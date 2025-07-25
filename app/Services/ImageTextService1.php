<?php

namespace App\Services;

use Intervention\Image\ImageManager;
use Intervention\Image\Typography\FontFactory;

class ImageTextService
{
    // first bot
    public function addTextToImage(string $imagePath, string $text): void
    {
        $imageManager = ImageManager::gd();
        $image = $imageManager->read($imagePath);

        $imageWidth = $image->width();
        $imageHeight = $image->height();

        // Вычисляем процент размера шрифта по высоте изображения
        if ($imageHeight > 2000) {
            $fontSize = intval($imageHeight * 0.018); // очень большое изображение
        } elseif ($imageHeight > 1000) {
            $fontSize = intval($imageHeight * 0.025);
        } elseif ($imageHeight > 500) {
            $fontSize = intval($imageHeight * 0.035);
        } else {
            $fontSize = intval($imageHeight * 0.04); // по умолчанию
        }
        $fontSize = max(10, $fontSize);
        // Координаты правого нижнего угла с отступом
        $x = $imageWidth - 30;
        $y = $imageHeight - 20;
        $wrapWidth = intval($imageWidth * 0.9);
        $image->text($text, $x, $y, function (FontFactory $font) use ($fontSize,$wrapWidth) {
            $font->filename(public_path('fonts/Jost-VariableFont_wght.ttf'));
            $font->size($fontSize);
            $font->color('#ffffff');
            $font->stroke('62655e', 1);
            $font->align('right');   // Выравнивание по правому краю
            $font->valign('bottom'); // И по нижнему краю
            $font->wrap($wrapWidth);
        });
        $image->save($imagePath);
    }

    // two Bot
    public function addTwoImage(string $imagePath): void
    {
        $imageManager = ImageManager::gd();
        $image = $imageManager->read($imagePath);
        $position=[
            'position'=>'bottom-right',
            'offset-x'=>10,
            'offset-y'=>10,
            'transparency'=>100,
            'scale'=>0.35
        ];
        $this->addWatermark($image,$position,$imagePath);;
    }


    public function addTwoImageAndText(string $imagePath,string $caption): void
    {
        $imageManager = ImageManager::gd();
        $image = $imageManager->read($imagePath);

        $imageWidth = $image->width();
        $imageHeight = $image->height();

        if($imageWidth>$imageHeight){

            $position=[
                'position'=>'bottom-left',
                'offset-x'=>10,
                'offset-y'=>10,
                'transparency'=>100,
                'scale'=>0.35
            ];
            $this->addWatermark($image,$position,$imagePath);

            $position_text=[
                'position'=>'bottom-left',
                'offset-x'=>10,
                'offset-y'=>10,
                'transparency'=>100,
                'scale'=>0.35
            ];
            $this->addText($image,$position,$imagePath,$caption);
        }else{

            $position=[
                'position'=>'top-right',
                'offset-x'=>10,
                'offset-y'=>10,
                'transparency'=>100,
                'scale'=>0.35
            ];
            $this->addWatermark($image,$position,$imagePath);;
        }
    }

    private function addWatermark($image,$position,$imagePath)
    {
        $imageWidth = $image->width();
        $imageHeight = $image->height();

        $waterPatch = public_path() . '/watermark/ru_converted.png';
        $manager = ImageManager::gd();
        $watermark = $manager->read($waterPatch);
        $targetWidth = (int) ($imageWidth * $position['scale']);
        $watermark->scale($targetWidth);

        $image->place(
            $watermark,
            $position['position'],               // позиция
            $position['offset-x'],               // смещение по X
            $position['offset-y'],               // смещение по Y
            $position['transparency']            // прозрачность (0–100)
        );
        $image->save($imagePath);

    }

    private function addText($image,$position,$imagePath,$text)
    {

        $imageWidth = $image->width();
        $imageHeight = $image->height();
        // Вычисляем процент размера шрифта по высоте изображения
        if ($imageHeight > 2000) {
            $fontSize = intval($imageHeight * 0.018); // очень большое изображение
        } elseif ($imageHeight > 1000) {
            $fontSize = intval($imageHeight * 0.025);
        } elseif ($imageHeight > 500) {
            $fontSize = intval($imageHeight * 0.035);
        } else {
            $fontSize = intval($imageHeight * 0.04); // по умолчанию
        }
        $fontSize = max(10, $fontSize);

        // Координаты правого нижнего угла с отступом
        $x = $imageWidth - 10;
        $y = $imageHeight - 10;
        $wrapWidth = intval($imageWidth * 0.9);
        $image->text($text, $x, $y, function (FontFactory $font) use ($fontSize,$wrapWidth) {
            $font->filename(public_path('fonts/Jost-VariableFont_wght.ttf'));
            $font->size($fontSize);
            $font->color('#ffffff');
            $font->stroke('62655e', 1);
            $font->align('right');   // Выравнивание по правому краю
            $font->valign('bottom'); // И по нижнему краю
            $font->wrap($wrapWidth);
        });
        $image->save($imagePath);
    }

}
