<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\DailySale;
use App\Services\DailySaleExtractionMapper;
use App\Services\LlamaIndexService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class LlamaWebhookController extends Controller
{
    public function __invoke(
        Request $request,
        LlamaIndexService $llamaIndexService,
        DailySaleExtractionMapper $mapper,
    ): JsonResponse {
        $eventType = $request->input('event_type');
        $jobId = $request->input('data.job_id');

        if (! $jobId) {
            Log::warning('LlamaWebhook: Missing job_id in payload.', [
                'event_type' => $eventType,
                'event_id' => $request->input('event_id'),
            ]);

            return response()->json(['error' => 'Missing job id.'], 400);
        }

        $dailySale = DailySale::where('llama_job_id', $jobId)->first();

        if (! $dailySale) {
            Log::warning('LlamaWebhook: No matching daily sale found.', [
                'llama_job_id' => $jobId,
                'event_type' => $eventType,
            ]);

            return response()->json(['error' => 'Daily sale not found.'], 404);
        }

        Log::info('LlamaWebhook: Received event.', [
            'daily_sale_id' => $dailySale->id,
            'llama_job_id' => $jobId,
            'event_type' => $eventType,
            'event_id' => $request->input('event_id'),
        ]);

        if ($eventType === 'extract.success') {
            $resultResponse = $llamaIndexService->getExtractJob($jobId);

            if ($resultResponse->successful()) {
                $fullResponse = $resultResponse->json();
                $resultData = $fullResponse['extract_result'] ?? $fullResponse;
                $mappedData = $mapper->map($resultData);

                $dailySale->update(array_merge($mappedData, [
                    'extraction_raw_json' => $resultData,
                    'status' => 'completed',
                    'error_message' => null,
                ]));

                Log::info('LlamaWebhook: Daily sale completed.', [
                    'daily_sale_id' => $dailySale->id,
                ]);
            } else {
                $dailySale->update([
                    'status' => 'failed',
                    'error_message' => 'Failed to fetch extraction result (HTTP '.$resultResponse->status().').',
                ]);

                Log::error('LlamaWebhook: Failed to fetch result.', [
                    'daily_sale_id' => $dailySale->id,
                    'http_status' => $resultResponse->status(),
                ]);
            }
        } elseif ($eventType === 'extract.error') {
            $dailySale->update([
                'status' => 'failed',
                'error_message' => 'LlamaIndex extraction failed.',
            ]);

            Log::error('LlamaWebhook: Extraction failed.', [
                'daily_sale_id' => $dailySale->id,
            ]);
        } else {
            return response()->json(['status' => 'acknowledged', 'event_type' => $eventType]);
        }

        return response()->json(['status' => 'processed']);
    }
}
