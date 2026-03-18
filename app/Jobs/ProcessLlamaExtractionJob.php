<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Models\DailySale;
use App\Services\LlamaIndexService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class ProcessLlamaExtractionJob implements ShouldQueue
{
    use Queueable;

    public int $timeout = 120;

    public int $tries = 3;

    public function __construct(
        public DailySale $dailySale,
        public string $filePath,
        public string $fileName,
    ) {}

    public function handle(LlamaIndexService $llamaIndexService): void
    {
        try {
            // Move file to public storage so LlamaIndex can access it via URL
            $publicPath = 'extractions/'.$this->dailySale->id.'_'.$this->fileName;
            Storage::disk('public')->put($publicPath, file_get_contents($this->filePath));

            $fileUrl = url(Storage::disk('public')->url($publicPath));

            Log::info('ProcessLlamaExtractionJob: File stored publicly.', [
                'daily_sale_id' => $this->dailySale->id,
                'file_url' => $fileUrl,
            ]);

            $webhookUrl = config('app.url').'/api/webhook/llama';
            $extractionResponse = $llamaIndexService->createExtractJob($fileUrl, [
                [
                    'webhook_url' => $webhookUrl,
                    'webhook_events' => ['extract.success', 'extract.error'],
                    'webhook_output_format' => 'json',
                ],
            ]);

            if ($extractionResponse->failed()) {
                throw new \RuntimeException('Failed to create LlamaIndex extraction job (HTTP '.$extractionResponse->status().').');
            }

            $jobId = $extractionResponse->json('id');

            $this->dailySale->update(['llama_job_id' => $jobId]);

            Log::info('ProcessLlamaExtractionJob: Extract job created, awaiting webhook.', [
                'daily_sale_id' => $this->dailySale->id,
                'llama_job_id' => $jobId,
            ]);
        } catch (\Throwable $e) {
            $this->dailySale->update([
                'status' => 'failed',
                'error_message' => $e->getMessage(),
            ]);

            Log::error('ProcessLlamaExtractionJob: Failed.', [
                'daily_sale_id' => $this->dailySale->id,
                'error' => $e->getMessage(),
            ]);
        } finally {
            // Clean up temp file
            if (file_exists($this->filePath)) {
                unlink($this->filePath);
            }
        }
    }

    public function failed(?\Throwable $exception): void
    {
        $this->dailySale->update([
            'status' => 'failed',
            'error_message' => $exception?->getMessage() ?? 'Job failed after max attempts.',
        ]);

        Log::error('ProcessLlamaExtractionJob: Job failed permanently.', [
            'daily_sale_id' => $this->dailySale->id,
            'error' => $exception?->getMessage(),
        ]);

        if (file_exists($this->filePath)) {
            unlink($this->filePath);
        }
    }
}
