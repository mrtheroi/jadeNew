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
            // filePath is now an S3/R2 relative path (e.g. "extractions/1_file.pdf")
            $fileUrl = Storage::disk('s3')->url($this->filePath);

            Log::info('ProcessLlamaExtractionJob: Using public file.', [
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
            // Clean up the S3/R2 file
            Storage::disk('s3')->delete($this->filePath);
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

        Storage::disk('s3')->delete($this->filePath);
    }
}
