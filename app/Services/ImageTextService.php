<?php

namespace App\Services;

use Intervention\Image\ImageManager;
use Intervention\Image\Typography\FontFactory;
use Intervention\Image\Drivers\Gd\Driver as GdDriver;
use Illuminate\Support\Facades\Log;

class ImageTextService
{
    protected $imageManager;

    public function __construct()
    {
        $this->imageManager = new ImageManager(new GdDriver());
    }

    // Только подпись (если вдруг нужно)
    public function addTextToImage(string $imagePath, string $caption): void
    {
        try {
//            Log::info('addTextToImage: start', compact('imagePath', 'caption'));
            $image = $this->imageManager->read($imagePath);

            $imageWidth = $image->width();
            $imageHeight = $image->height();

            $offsetX = intval($imageWidth * 0.03);
            $offsetY = intval($imageHeight * 0.03);
            $this->addPhotoCredit($image, $caption, $imagePath, $offsetX, $offsetY, $imageHeight);
//            Log::info('addTextToImage: done', compact('imagePath'));
        } catch (\Throwable $e) {
            /*
            Log::error('addTextToImage: exception', [
                'message' => $e->getMessage(),
                'trace'   => $e->getTraceAsString()
            ]);
            */
        }
    }

    // Добавляет только ватермарку (png)
    public function addTwoImage(string $imagePath): void
    {
        try {
//            Log::info('addTwoImage: start', compact('imagePath'));
            $image = $this->imageManager->read($imagePath);
            $imageWidth = $image->width();

            // Ширина ватермарки: не более 21.5% ширины изображения, но не больше исходного файла
            $wmScale = min(0.215, 1238 / $imageWidth); // 1238 — исходная ширина png ватермарки
            $position = [
                'position' => 'bottom-right',
                'offset-x' => intval($imageWidth * 0.03), // 3% ширины
                'offset-y' => intval($image->height() * 0.03), // 3% высоты
                'transparency' => 100,
                'scale' => $wmScale
            ];
            $this->addWatermark($image, $position, $imagePath);
//            Log::info('addTwoImage: done', compact('imagePath'));
        } catch (\Throwable $e) {
            /*
            Log::error('addTwoImage: exception', [
                'message' => $e->getMessage(),
                'trace'   => $e->getTraceAsString()
            ]);
            */
        }
    }

    // Добавляет ватермарку и подпись (caption) в левый нижний угол
    public function addTwoImageAndText(string $imagePath, string $caption): void
    {
        try {
//            Log::info('addTwoImageAndText: start', compact('imagePath', 'caption'));
            $image = $this->imageManager->read($imagePath);

            $imageWidth = $image->width();
            $imageHeight = $image->height();

            // Ватермарка всегда в правом нижнем
            $wmScale = min(0.215, 1238 / $imageWidth); // исходная ширина png ватермарки
            $wmOffsetX = intval($imageWidth * 0.03);
            $wmOffsetY = intval($imageHeight * 0.03);

            $wmPosition = [
                'position' => 'bottom-right',
                'offset-x' => $wmOffsetX,
                'offset-y' => $wmOffsetY,
                'transparency' => 100,
                'scale' => $wmScale
            ];
            $this->addWatermark($image, $wmPosition, $imagePath);
            // Подпись всегда левый нижний, с такими же отступами, размер шрифта — 3% высоты, min 10, max 25
            $this->addPhotoCredit($image, $caption, $imagePath, $wmOffsetX, $wmOffsetY, $imageHeight);
//            Log::info('addTwoImageAndText: done', compact('imagePath'));
        } catch (\Throwable $e) {
            /*
            Log::error('addTwoImageAndText: exception', [
                'message' => $e->getMessage(),
                'trace'   => $e->getTraceAsString()
            ]);
            */
        }
    }


    // Приватная функция для наложения png-ватермарки
    private function addWatermark($image, $position, $imagePath)
    {
        try {
//            Log::info('addWatermark: start', compact('imagePath'));
            $imageWidth = $image->width();

            $waterPatch = public_path('watermark/white_ru.png');
            $manager = new ImageManager(new GdDriver());
            $watermark = $manager->read($waterPatch);

            // Масштабируем ватермарку по ширине
            $targetWidth = (int)($imageWidth * $position['scale']);
            $watermark->scale($targetWidth);
            $image->place(
                $watermark,
                $position['position'],
                $position['offset-x'],
                $position['offset-y'],
                $position['transparency']
            );
            $image->save($imagePath);
//            Log::info('addWatermark: done', compact('imagePath'));
        } catch (\Throwable $e) {
            /*
            Log::error('addWatermark: exception', [
                'message' => $e->getMessage(),
                'trace'   => $e->getTraceAsString()
            ]);
            */
        }
    }

    // Приватная функция для наложения подписи/авторства (Roboto, #8C909B, левый нижний)
    private function addPhotoCredit($image, $caption, $imagePath, $offsetX, $offsetY, $imageHeight)
    {
        try {
//            Log::info('addPhotoCredit: start', compact('imagePath'));
            $fontSize = max(10, min(intval($imageHeight * 0.03), 25));
            $x = $offsetX;
            $y = $imageHeight - $offsetY;
            $image->text($caption, $x, $y, function (FontFactory $font) use ($fontSize) {
                $font->filename(public_path('fonts/Roboto-Regular.ttf'));
                $font->size($fontSize);
                $font->color('#ffffff');
                $font->stroke('8C909B', 1);
                $font->align('left');
                $font->valign('bottom');
            });
            $image->save($imagePath);
//            Log::info('addPhotoCredit: done', compact('imagePath'));
        } catch (\Throwable $e) {

        }
    }
}
