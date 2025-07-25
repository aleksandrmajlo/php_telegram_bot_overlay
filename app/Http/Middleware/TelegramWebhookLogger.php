<?php

namespace App\Http\Middleware;


use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class TelegramWebhookLogger
{
    public function handle(Request $request, Closure $next)
    {
//        Log::channel('telegram')->info('Incoming Telegram Webhook', [
//            'body' => $request->all(),
//        ]);
        return $next($request);
    }
}
