<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;

/**
 * TelegramBot
 */
class TelegramBot 
{
    protected $token;
    protected $apiEndpoint;
    protected $baseUrl;
    protected $headers;
    
    /**
     * __construct
     *
     * @return void
     */
    public function __construct()
    {
        $this->token = env('TELEGRAM_BOT_TOKEN');
        $this->apiEndpoint = env('TELEGRAM_API_ENDPOINT');
        $this->baseUrl = "{$this->apiEndpoint}/bot{$this->token}";
        $this->setHeaders();
    }
    
    /**
     * setHeaders
     *
     * @return void
     */
    protected function setHeaders(){
        
        $this->headers = [
            "Content-Type"  => "application/json",
            "Accept"        => "application/json",
        ];

    }

    /**
     * sendMessage
     *
     * @param  string $text
     * @param  string $chatId
     * @param  string $replyToMessageId
     * @return array
     */
    public function sendMessage(string $text = '', string $chatId, ?string $replyToMessageId): array
    {
        Log::info('TelegramBot->sendMessage', [
            'text' => $text,
            'chatId' => $chatId,
            'replyToMessageId' => $replyToMessageId,
        ]);

        // Default result array
        $result = ['success' => false, 'body' => []];

        // Create params array
        $params = [
            'chat_id' => $chatId,
            'reply_to_message_id' => $replyToMessageId,
            'text' => $text,
        ];

        $url = "{$this->baseUrl}/sendMessage";

        // Send the request
        try {
            
            $response = Http::withHeaders($this->headers)->post($url,$params);
            $result = ['success'=>$response->ok(),'body'=>$response->json()];

        } catch (\Throwable $th) {

            $result['error'] = $th->getMessage();
        }

        Log::info('TelegramBot->sendMessage->result',['result'=>$result]);

        return $result;
    }

}