<?php

namespace App\Http\Controllers;

use App\Services\ImageTextJostService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use App\Models\TelegramPhoto;
use App\Models\TelegramWebhook;
use App\Services\ImageTextService;
class TelegramBotController extends Controller
{
    public function handle(Request $request)
    {
        $data = $request->all();
        if (isset($data['message']['photo'])) {
            $photos = $data['message']['photo'];
            $fileId = end($photos)['file_id'];
            $userId = $data['message']['from']['id'];
            $caption = $data['message']['caption'] ?? '';
            $botToken = config('services.telegram.bot_token');
            $fileResponse = Http::get("https://api.telegram.org/bot{$botToken}/getFile", [
                'file_id' => $fileId,
            ]);
            if (!$fileResponse->successful()) {
                return response('Ошибка получения файла от Telegram', 400);
            }
            $filePath = $fileResponse['result']['file_path'];
            $fileUrl = "https://api.telegram.org/file/bot{$botToken}/{$filePath}";

            $headers = get_headers($fileUrl, 1);
            $contentType = $headers['Content-Type'] ?? 'image/jpeg';
            $extensionMap = [
                'image/jpeg' => 'jpg',
                'image/png' => 'png',
                'image/webp' => 'webp',
            ];
            $extension = $extensionMap[$contentType] ?? 'jpg';
            $timestamp = now()->format('Ymd_His');
            $fileName = "telegram_{$userId}_{$timestamp}.{$extension}";
            $localPath = public_path("telegram_photos/{$fileName}");
            $localDir = public_path("telegram_photos");
            $localPath = "{$localDir}/{$fileName}";
            if (!file_exists($localDir)) {
                mkdir($localDir, 0755, true);
            }
            file_put_contents($localPath, file_get_contents($fileUrl));
            if (!empty($caption)) {
                $textService = new ImageTextJostService();
                $textService->addTextToImage($localPath, $caption);
                $chatId = $data['message']['chat']['id'];
                $botToken = config('services.telegram.bot_token');
                Http::asMultipart()->post("https://api.telegram.org/bot{$botToken}/sendPhoto", [
                    [
                        'name' => 'chat_id',
                        'contents' => $chatId,
                    ],
                    [
                        'name' => 'photo',
                        'contents' => fopen($localPath, 'r'),
                        'filename' => basename($localPath),
                    ],
                    [
                        'name' => 'caption',
                        'contents' => 'Ваше изображение с текстом готово 🎉',
                    ],
                ]);
                if (file_exists($localPath)) {
                    unlink($localPath);
                }
            }
            return response('Готово: текст наложен и сохранено', 200);
        }

        return response('Нет фото в сообщении', 200);
    }

    public function handle_two(Request $request)
    {
        $data = $request->all();
//        TelegramWebhook::create(['data' => $data]);
        if (isset($data['message']['photo'])) {
            $photos = $data['message']['photo'];
            $fileId = end($photos)['file_id'];
            $userId = $data['message']['from']['id'];

            $botToken = config('services.telegram.bot_token_two');
            $fileResponse = Http::get("https://api.telegram.org/bot{$botToken}/getFile", [
                'file_id' => $fileId,
            ]);
            if (!$fileResponse->successful()) {
                return response('Ошибка получения файла от Telegram', 400);
            }
            $filePath = $fileResponse['result']['file_path'];
            $fileUrl = "https://api.telegram.org/file/bot{$botToken}/{$filePath}";

            $headers = get_headers($fileUrl, 1);
            $contentType = $headers['Content-Type'] ?? 'image/jpeg';
            $extensionMap = [
                'image/jpeg' => 'jpg',
                'image/png' => 'png',
                'image/webp' => 'webp',
            ];
            $extension = $extensionMap[$contentType] ?? 'jpg';
            $timestamp = now()->format('Ymd_His');
            $fileName = "telegram_{$userId}_{$timestamp}.{$extension}";
            $localPath = public_path("telegram_photos/{$fileName}");
            $localDir = public_path("telegram_photos");
            $localPath = "{$localDir}/{$fileName}";
            if (!file_exists($localDir)) {
                mkdir($localDir, 0755, true);
            }
            file_put_contents($localPath, file_get_contents($fileUrl));
            $caption = $data['message']['caption'] ?? '';
            $service = new ImageTextService();
            if (!empty($caption)) {
                $service->addTwoImageAndText($localPath,$caption);
            } else {
                $service->addTwoImage($localPath);
            }
            $chatId = $data['message']['chat']['id'];
            Http::asMultipart()->post("https://api.telegram.org/bot{$botToken}/sendPhoto", [
                [
                    'name' => 'chat_id',
                    'contents' => $chatId,
                ],
                [
                    'name' => 'photo',
                    'contents' => fopen($localPath, 'r'),
                    'filename' => basename($localPath),
                ],
                [
                    'name' => 'caption',
                    'contents' => 'Ваше изображение  готово 🎉',
                ],
            ]);
            // delete file
            if (file_exists($localPath)) {
                unlink($localPath);
            }
            return response('Готово: текст наложен и сохранено', 200);
        }


        return response('Нет фото в сообщении', 200);
    }
}
