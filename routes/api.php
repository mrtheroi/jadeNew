<?php

use App\Http\Controllers\Api\LlamaWebhookController;
use Illuminate\Support\Facades\Route;

Route::post('/webhook/llama', LlamaWebhookController::class)
    ->middleware('idempotent:body_event_id,1440')
    ->name('api.webhook.llama');
