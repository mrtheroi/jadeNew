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
        private readonly string $configurationId,
    ) {}

    /**
     * Create an extraction job using a URL directly (v2 API).
     *
     * @param  array<int, array<string, mixed>>|null  $webhookConfigurations
     */
    public function createExtractJob(string $fileUrl, ?array $webhookConfigurations = null): Response
    {
        Log::info('Creating LlamaIndex v2 extract job.', [
            'file_url' => $fileUrl,
            'configuration_id' => $this->configurationId,
            'has_webhook' => $webhookConfigurations !== null,
        ]);

        $body = [
            'type' => 'url',
            'value' => $fileUrl,
            'configuration_id' => $this->configurationId,
        ];

        if ($webhookConfigurations !== null) {
            $body['webhook_configurations'] = $webhookConfigurations;
        }

        $response = Http::withToken($this->apiKey)
            ->post("{$this->baseUrl}/extract", $body);

        Log::info('LlamaIndex v2 extract job response.', [
            'status' => $response->status(),
            'successful' => $response->successful(),
            'body' => $response->failed() ? $response->body() : null,
        ]);

        return $response;
    }

    /**
     * Get an extraction job with its results (v2 API).
     */
    public function getExtractJob(string $jobId): Response
    {
        Log::info('Fetching extraction job.', ['job_id' => $jobId]);

        $response = Http::withToken($this->apiKey)
            ->get("{$this->baseUrl}/extract/{$jobId}");

        Log::info('LlamaIndex v2 extract job result response.', [
            'job_id' => $jobId,
            'status' => $response->status(),
            'successful' => $response->successful(),
        ]);

        return $response;
    }
}