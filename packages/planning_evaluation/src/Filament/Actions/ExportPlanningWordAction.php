<?php

namespace Quochao56\PlanningEvaluation\Filament\Actions;

use Filament\Actions\Action;
use Filament\Support\Icons\Heroicon;
use Illuminate\Contracts\Filesystem\FileNotFoundException;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
use PhpOffice\PhpWord\Element\TextRun;
use PhpOffice\PhpWord\TemplateProcessor;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class ExportPlanningWordAction extends Action
{
    public static function getDefaultName(): ?string
    {
        return 'export_planning_word';
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->label('Xuất Word');
        $this->icon(Heroicon::OutlinedDocumentArrowDown);
        $this->color('info');

        $this->hidden(static function (Model $record): bool {
            if (! method_exists($record, 'trashed')) {
                return false;
            }

            return $record->trashed();
        });
        
        $this->action(function (Model $record): BinaryFileResponse {
            $startMonth = data_get($record, 'start_date') ? $record->start_date->format('n') : 'unknown';
            $endMonth = data_get($record, 'end_date') ? $record->end_date->format('n') : 'unknown';
            $timeRange = "T{$startMonth}-T{$endMonth}";
            $outputFile = "KHGDCN_" . ($record->student?->name ?? 'unknown') . "_" . $timeRange . "_" . time() . ".docx";
            $path = $this->generateWordFile($record, $outputFile);

            return response()->download(
                $path,
                $outputFile,
            )->deleteFileAfterSend(true);
        });
    }

    protected function generateWordFile(Model $record, string $outputFile): string
    {
        $templatePath = realpath(
            __DIR__ . '/../../../resources/templates/template_KHGDCN.docx'
        );

        if (! $templatePath || ! file_exists($templatePath)) {
            throw new FileNotFoundException('Template not found at: ' . $templatePath);
        }

        $record->loadMissing(['employee', 'student']);

        $student  = $record->student;
        $employee = $record->employee;

        $genderMap = [
            'male'   => 'Nam',
            'female' => 'Nữ',
            'other'  => 'Khác',
        ];

        // Determine time range string example T9-T10 then time is 2 months, if start or end date is missing, use 'unknown' and leave time empty.
        $time = '';
        if ($record->start_date && $record->end_date) {
            $startMonth = $record->start_date->format('n');
            $endMonth = $record->end_date->format('n');
            $time = ($endMonth - $startMonth + 1) . ' tháng';
        }


        $templateProcessor = new TemplateProcessor($templatePath);

        $templateProcessor->setValue('name',          (string) ($record->name ?? ''));
        $templateProcessor->setValue('student_name',  (string) ($student?->name ?? ''));
        $templateProcessor->setValue('student_dob',   $student?->dob ? $student->dob->format('d/m/Y') : '');
        $templateProcessor->setValue('gender',        $genderMap[$student?->gender ?? ''] ?? '');
        $templateProcessor->setValue('employee_name', (string) ($employee?->name ?? ''));
        $templateProcessor->setValue('time', (string) ($time ?? ''));

        // Fill table rows from planning_details using complex values to preserve inline styling.
        $details = $record->planning_details ?? [];

        if (! empty($details)) {
            $templateProcessor->cloneRow('linh_vuc', count($details));

            foreach ($details as $i => $row) {
                $n = $i + 1;
                $templateProcessor->setComplexValue("linh_vuc#{$n}", $this->buildCellTextRun($row['linh_vuc'] ?? []));
                $templateProcessor->setComplexValue("muc_tieu#{$n}", $this->buildCellTextRun($row['muc_tieu'] ?? []));
                $templateProcessor->setComplexValue("hoat_dong#{$n}", $this->buildCellTextRun($row['hoat_dong'] ?? []));
                $templateProcessor->setComplexValue("phuong_tien#{$n}", $this->buildCellTextRun($row['phuong_tien'] ?? []));
                $templateProcessor->setComplexValue("muc_tieu_du_phong#{$n}", $this->buildCellTextRun($row['muc_tieu_du_phong'] ?? []));
            }
        } else {
            $templateProcessor->setValue('linh_vuc', '');
            $templateProcessor->setValue('muc_tieu', '');
            $templateProcessor->setValue('hoat_dong', '');
            $templateProcessor->setValue('phuong_tien', '');
            $templateProcessor->setValue('muc_tieu_du_phong', '');
        }

        $disk      = Storage::disk('local');
        $directory = 'exports/plannings';

        if (! $disk->exists($directory)) {
            $disk->makeDirectory($directory);
        }

        $relativePath = $directory . '/' . $outputFile;

        $templateProcessor->saveAs($disk->path($relativePath));

        if (! $disk->exists($relativePath)) {
            throw new FileNotFoundException($relativePath);
        }

        return $disk->path($relativePath);
    }

    /**
     * Build rich text content for one Word table cell from markdown editor items.
     *
     * @param  array<array{content?: string}>  $items
     */
    protected function buildCellTextRun(array $items): TextRun
    {
        $textRun = new TextRun();
        $hasContent = false;

        foreach ($items as $item) {
            $raw = (string) ($item['content'] ?? '');

            foreach (preg_split('/\r\n|\r|\n/u', $raw) ?: [] as $line) {
                $line = trim($line);

                if ($line === '') {
                    continue;
                }

                if ($hasContent) {
                    $textRun->addTextBreak();
                }

                if (str_starts_with($line, '- ')) {
                    $textRun->addText('- ', $this->defaultTextStyle());
                    $line = substr($line, 2);
                }

                $this->appendMarkdownInlineToTextRun($textRun, $line);
                $hasContent = true;
            }
        }

        if (! $hasContent) {
            $textRun->addText('', $this->defaultTextStyle());
        }

        return $textRun;
    }

    protected function appendMarkdownInlineToTextRun(TextRun $textRun, string $text): void
    {
        $pattern = '/(\*\*[^*]+\*\*|\*[^*]+\*)/u';

        preg_match_all($pattern, $text, $matches, PREG_OFFSET_CAPTURE);

        $offset = 0;

        foreach ($matches[0] as [$token, $position]) {
            if ($position > $offset) {
                $textRun->addText(substr($text, $offset, $position - $offset), $this->defaultTextStyle());
            }

            $style = $this->defaultTextStyle();
            $content = $token;

            if (str_starts_with($token, '**') && str_ends_with($token, '**')) {
                $style['bold'] = true;
                $content = substr($token, 2, -2);
            } elseif (str_starts_with($token, '*') && str_ends_with($token, '*')) {
                $style['italic'] = true;
                $content = substr($token, 1, -1);
            }

            if ($content !== '') {
                $textRun->addText($content, $style);
            }

            $offset = $position + strlen($token);
        }

        if ($offset < strlen($text)) {
            $textRun->addText(substr($text, $offset), $this->defaultTextStyle());
        }
    }

    protected function defaultTextStyle(): array
    {
        return [
            'name' => 'Times New Roman',
            'size' => 11,
        ];
    }
}
