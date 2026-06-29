<?php

namespace Quochao56\Equipment\Filament\Exports;

use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Concerns\WithDrawings;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Drawing;
use pxlrbt\FilamentExcel\Exports\ExcelExport;

class EquipmentExcelExport extends ExcelExport implements WithDrawings
{
    private array $tempFiles = [];

    public function __destruct()
    {
        foreach ($this->tempFiles as $file) {
            if (is_file($file)) {
                @unlink($file);
            }
        }
    }

    public function drawings(): array
    {
        $drawings = [];

        $records = $this->getQuery()->get(['id', 'image']);

        foreach ($records as $index => $record) {
            $imagePath = $this->resolveImagePath($record->image);

            if ($imagePath === null) {
                continue;
            }

            $resizedPath = $this->resizeImageToTemp($imagePath);
            if ($resizedPath !== $imagePath) {
                $this->tempFiles[] = $resizedPath;
            }

            $row = $index + 2;

            $drawing = new Drawing;
            $drawing->setPath($resizedPath);
            $drawing->setCoordinates("A{$row}");
            $drawing->setHeight(130);
            $drawing->setOffsetX(6);
            $drawing->setOffsetY(6);

            $drawings[] = $drawing;
        }

        return $drawings;
    }

    public function registerEvents(): array
    {
        return array_merge(parent::registerEvents(), [
            AfterSheet::class => function (AfterSheet $event): void {
                $sheet = $event->sheet->getDelegate();
                $highestRow = $sheet->getHighestDataRow();

                $sheet->getColumnDimension('A')->setWidth(34);

                for ($row = 2; $row <= $highestRow; $row++) {
                    $sheet->getRowDimension($row)->setRowHeight(104);
                }
            },
        ]);
    }

    private function resolveImagePath(?string $state): ?string
    {
        if (blank($state)) {
            return null;
        }

        $publicDiskPath = Storage::disk('public')->path($state);

        if (is_file($publicDiskPath)) {
            return $publicDiskPath;
        }

        $defaultDiskPath = Storage::path($state);

        if (is_file($defaultDiskPath)) {
            return $defaultDiskPath;
        }

        return null;
    }

    private function resizeImageToTemp(string $originalPath): string
    {
        $info = @getimagesize($originalPath);
        if (! $info) {
            return $originalPath;
        }

        $mime = $info['mime'];
        $width = $info[0];
        $height = $info[1];

        $targetHeight = 130;
        if ($height <= $targetHeight) {
            return $originalPath;
        }

        $targetWidth = (int) (($width / $height) * $targetHeight);

        switch ($mime) {
            case 'image/jpeg':
                $srcImg = @imagecreatefromjpeg($originalPath);
                break;
            case 'image/png':
                $srcImg = @imagecreatefrompng($originalPath);
                break;
            case 'image/gif':
                $srcImg = @imagecreatefromgif($originalPath);
                break;
            case 'image/webp':
                if (function_exists('imagecreatefromwebp')) {
                    $srcImg = @imagecreatefromwebp($originalPath);
                } else {
                    $srcImg = false;
                }
                break;
            default:
                $srcImg = false;
        }

        if (! $srcImg) {
            return $originalPath;
        }

        $dstImg = @imagecreatetruecolor($targetWidth, $targetHeight);
        if (! $dstImg) {
            @imagedestroy($srcImg);

            return $originalPath;
        }

        if ($mime == 'image/png' || $mime == 'image/gif') {
            @imagealphablending($dstImg, false);
            @imagesavealpha($dstImg, true);
            $transparent = @imagecolorallocatealpha($dstImg, 255, 255, 255, 127);
            @imagefilledrectangle($dstImg, 0, 0, $targetWidth, $targetHeight, $transparent);
        }

        if (! @imagecopyresampled($dstImg, $srcImg, 0, 0, 0, 0, $targetWidth, $targetHeight, $width, $height)) {
            @imagedestroy($srcImg);
            @imagedestroy($dstImg);

            return $originalPath;
        }

        $tempPath = tempnam(sys_get_temp_dir(), 'eq_img_');
        if ($tempPath) {
            if (@imagejpeg($dstImg, $tempPath, 75)) {
                @imagedestroy($srcImg);
                @imagedestroy($dstImg);

                return $tempPath;
            }
        }

        @imagedestroy($srcImg);
        @imagedestroy($dstImg);

        return $originalPath;
    }
}
