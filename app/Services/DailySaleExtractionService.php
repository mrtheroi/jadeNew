<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\DailySale;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Log;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;

class DailySaleExtractionService
{
    public function __construct(
        private readonly LlamaIndexService $llamaIndexService,
    ) {}

    /**
     * Upload a file to LlamaIndex and create an extraction job for the given DailySale.
     */
    public function process(DailySale $dailySale, UploadedFile $file): void
    {
        $fileName = $file->getClientOriginalName();

        // Read file contents — Livewire temp files need to be read via Storage
        if ($file instanceof TemporaryUploadedFile) {
            $fileContents = $file->get();
        } else {
            $fileContents = $file->getContent();
        }

        // Step 1: Upload file to LlamaIndex
        $uploadResponse = $this->llamaIndexService->uploadFile(
            $fileContents,
            $fileName,
        );

        if ($uploadResponse->failed()) {
            $this->fail($dailySale, 'Error al subir archivo a LlamaIndex (HTTP '.$uploadResponse->status().'): '.$uploadResponse->body());

            return;
        }

        $fileId = $uploadResponse->json('id');

        Log::info('DailySaleExtraction: File uploaded.', [
            'daily_sale_id' => $dailySale->id,
            'llama_file_id' => $fileId,
        ]);

        // Step 2: Create extraction job
        $webhookUrl = config('app.url').'/api/webhook/llama';
        $extractionResponse = $this->llamaIndexService->createExtractJob($fileId, [
            [
                'webhook_url' => $webhookUrl,
                'webhook_events' => ['extract.success', 'extract.error'],
                'webhook_output_format' => 'json',
            ],
        ]);

        if ($extractionResponse->failed()) {
            $this->fail($dailySale, 'Error al crear job de extracción (HTTP '.$extractionResponse->status().'): '.$extractionResponse->body());

            return;
        }

        $jobId = $extractionResponse->json('id');

        $dailySale->update(['llama_job_id' => $jobId]);

        Log::info('DailySaleExtraction: Job created, awaiting webhook.', [
            'daily_sale_id' => $dailySale->id,
            'llama_job_id' => $jobId,
        ]);
    }

    private function fail(DailySale $dailySale, string $message): void
    {
        $dailySale->update([
            'status' => 'failed',
            'error_message' => $message,
        ]);

        Log::error('DailySaleExtraction: Failed.', [
            'daily_sale_id' => $dailySale->id,
            'error' => $message,
        ]);
    }
}
