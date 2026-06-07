<?php

namespace Quochao56\Equipment\Filament\Exports;

use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Concerns\WithDrawings;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Drawing;
use pxlrbt\FilamentExcel\Exports\ExcelExport;

class EquipmentExcelExport extends ExcelExport implements WithDrawings
{
    public function drawings(): array
    {
        $drawings = [];

        $records = $this->getQuery()->get(['id', 'image']);

        foreach ($records as $index => $record) {
            $imagePath = $this->resolveImagePath($record->image);

            if ($imagePath === null) {
                continue;
            }

            $row = $index + 2;

            $drawing = new Drawing();
            $drawing->setPath($imagePath);
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
}
