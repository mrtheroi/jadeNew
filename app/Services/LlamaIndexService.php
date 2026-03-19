<?php

declare(strict_types=1);

namespace App\Services;

use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class LlamaIndexService
{
    public function __construct(
        private readonly string $baseUrl,
        private readonly string $apiKey,
        private readonly string $extractionAgentId,
    ) {}

    /**
     * Upload a file to LlamaIndex Cloud.
     */
    public function uploadFile(string $fileContents, string $fileName): Response
    {
        Log::info('LlamaIndex: Uploading file.', [
            'file_name' => $fileName,
        ]);

        $response = Http::withToken($this->apiKey)
            ->attach('file', $fileContents, $fileName)
            ->post("{$this->baseUrl}/beta/files", [
                'purpose' => 'extract',
            ]);

        Log::info('LlamaIndex: File upload response.', [
            'status' => $response->status(),
            'successful' => $response->successful(),
            'file_id' => $response->successful() ? $response->json('id') : null,
            'body' => $response->failed() ? $response->body() : null,
        ]);

        return $response;
    }

    /**
     * Create an extraction job using a file_id and the configured extraction agent.
     *
     * @param  array<int, array<string, mixed>>|null  $webhookConfigurations
     */
    public function createExtractJob(string $fileId, ?array $webhookConfigurations = null): Response
    {
        Log::info('LlamaIndex: Creating extraction job.', [
            'file_id' => $fileId,
            'extraction_agent_id' => $this->extractionAgentId,
            'has_webhook' => $webhookConfigurations !== null,
        ]);

        $body = [
            'extraction_agent_id' => $this->extractionAgentId,
            'file_id' => $fileId,
        ];

        if ($webhookConfigurations !== null) {
            $body['webhook_configurations'] = $webhookConfigurations;
        }

        $response = Http::withToken($this->apiKey)
            ->post("{$this->baseUrl}/extraction/jobs", $body);

        Log::info('LlamaIndex: Extraction job response.', [
            'status' => $response->status(),
            'successful' => $response->successful(),
            'job_id' => $response->successful() ? $response->json('id') : null,
            'body' => $response->failed() ? $response->body() : null,
        ]);

        return $response;
    }

    /**
     * Get an extraction job with its results.
     */
    /**
     * Get the extraction result for a completed job.
     */
    public function getExtractJobResult(string $jobId): Response
    {
        Log::info('LlamaIndex: Fetching extraction job result.', ['job_id' => $jobId]);

        $response = Http::withToken($this->apiKey)
            ->get("{$this->baseUrl}/extraction/jobs/{$jobId}/result");

        Log::info('LlamaIndex: Extraction job result.', [
            'job_id' => $jobId,
            'status' => $response->status(),
            'successful' => $response->successful(),
        ]);

        return $response;
    }
}
