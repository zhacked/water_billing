<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class SemaphoreSmsService
{
    protected string $apiKey;
    protected string $senderName;

    public function __construct()
    {
        $this->apiKey = config('semaphore.api_key');
        $this->senderName = config('semaphore.sender_name');
    }

    public function sendSms(string $number, string $message): bool
    {
        $response = Http::post('https://api.semaphore.co/api/v4/messages', [
            'apikey' => $this->apiKey,
            'number' => $number,
            'message' => $message,
            'sendername' => $this->senderName,
        ]);

        return $response->successful();
    }
}
