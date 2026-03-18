<?php

namespace Quochao56\PlanningEvaluation\Filament\Actions;

use App\Enum\BaseStatusEnum;
use Filament\Actions\Action;
use Filament\Support\Icons\Heroicon;
use Illuminate\Contracts\Filesystem\FileNotFoundException;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
use PhpOffice\PhpWord\Element\TextRun;
use PhpOffice\PhpWord\TemplateProcessor;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class ExportEvaluationWordAction extends Action
{
    public static function getDefaultName(): ?string
    {
        return 'export_evaluation_word';
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->label('Xuất Word');
        $this->icon(Heroicon::OutlinedDocumentArrowDown);
        $this->color('info');

        $this->visible(static function (Model $record): bool {
            return $record->status === BaseStatusEnum::Published;
        });

        $this->action(function (Model $record): BinaryFileResponse {
            $record->loadMissing(['planning.student']);

            $planning   = $record->planning;
            $student    = $planning?->student;
            $startMonth = $planning?->start_date?->format('n') ?? 'unknown';
            $endMonth   = $planning?->end_date?->format('n') ?? 'unknown';
            $timeRange  = "T{$startMonth}-T{$endMonth}";
            $outputFile = 'KQDG_' . ($student?->name ?? 'unknown') . "_{$timeRange}_" . time() . '.docx';

            $path = $this->generateWordFile($record, $outputFile);

            return response()->download($path, $outputFile)->deleteFileAfterSend(true);
        });
    }

    protected function generateWordFile(Model $record, string $outputFile): string
    {
        $templatePath = realpath(__DIR__ . '/../../../resources/templates/template_KQDG.docx');

        if (! $templatePath || ! file_exists($templatePath)) {
            throw new FileNotFoundException('Template not found at: ' . $templatePath);
        }

        $record->loadMissing(['planning.student', 'planning.employee']);

        $planning = $record->planning;
        $student  = $planning?->student;
        $employee = $planning?->employee;

        $genderMap = ['male' => 'Nam', 'female' => 'Nữ', 'other' => 'Khác'];

        $time = '';
        if ($planning?->start_date && $planning?->end_date) {
            $months = (int) $planning->end_date->format('n') - (int) $planning->start_date->format('n') + 1;
            $time   = $months . ' tháng';
        }

        $templateProcessor = new TemplateProcessor($templatePath);

        $templateProcessor->setValue('name',          (string) ($record->name ?? ''));
        $templateProcessor->setValue('student_name',  (string) ($student?->name ?? ''));
        $templateProcessor->setValue('student_dob',   $student?->dob ? $student->dob->format('d/m/Y') : '');
        $templateProcessor->setValue('gender',        $genderMap[$student?->gender ?? ''] ?? '');
        $templateProcessor->setValue('employee_name', (string) ($employee?->name ?? ''));
        $templateProcessor->setValue('time',          $time);

        $details  = $record->evaluation_details ?? [];
        $flatRows = $this->buildFlatRows($details);
        $total    = count($flatRows);

        if ($total > 0) {
            $templateProcessor->cloneRow('linh_vuc', $total);

            // Apply vertical cell merging for the linh_vuc column.
            $this->applyVerticalMerges($templateProcessor, $flatRows);

            foreach ($flatRows as $i => $row) {
                $n       = $i + 1;
                $danhGia = $row['danh_gia'] ?? null;

                $templateProcessor->setComplexValue(
                    "linh_vuc#{$n}",
                    $this->buildTextRunFromMarkdown($row['is_first_in_group'] ? (string) $row['linh_vuc'] : ''),
                );
                $templateProcessor->setComplexValue("muc_tieu#{$n}", $this->buildTextRunFromMarkdown((string) $row['content']));
                $templateProcessor->setComplexValue("nhan_xet#{$n}", $this->buildTextRunFromMarkdown((string) ($row['nhan_xet'] ?? '')));
                $templateProcessor->setValue("danh_gia_1#{$n}", $danhGia === '+'   ? '+' : '');
                $templateProcessor->setValue("danh_gia_2#{$n}", $danhGia === '+/-' ? '+/-' : '');
                $templateProcessor->setValue("danh_gia_3#{$n}", $danhGia === '-'   ? '-' : '');
            }
        } else {
            foreach (['linh_vuc', 'muc_tieu', 'danh_gia_1', 'danh_gia_2', 'danh_gia_3', 'nhan_xet'] as $key) {
                $templateProcessor->setValue($key, '');
            }
        }

        $disk      = Storage::disk('local');
        $directory = 'exports/evaluations';

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
     * Flatten evaluation_details into one row per mục tiêu entry.
     */
    protected function buildFlatRows(array $details): array
    {
        $rows = [];

        foreach ($details as $detail) {
            $linhVuc = (string) ($detail['linh_vuc'] ?? '');
            $mucList = (array) ($detail['muc_tieu'] ?? []);
            $count   = count($mucList);

            if ($count === 0) {
                $rows[] = [
                    'linh_vuc'          => $linhVuc,
                    'is_first_in_group' => true,
                    'is_only_in_group'  => true,
                    'content'           => '',
                    'danh_gia'          => null,
                    'nhan_xet'          => '',
                ];
                continue;
            }

            foreach ($mucList as $idx => $goal) {
                $rows[] = [
                    'linh_vuc'          => $linhVuc,
                    'is_first_in_group' => $idx === 0,
                    'is_only_in_group'  => $count === 1,
                    'content'           => (string) ($goal['content'] ?? ''),
                    'danh_gia'          => $goal['danh_gia'] ?? null,
                    'nhan_xet'          => (string) ($goal['nhan_xet'] ?? ''),
                ];
            }
        }

        return $rows;
    }

    protected function buildTextRunFromMarkdown(string $text): TextRun
    {
        $textRun = new TextRun();

        foreach (preg_split('/\r\n|\r|\n/u', $text) ?: [''] as $lineIndex => $line) {
            if ($lineIndex > 0) {
                $textRun->addTextBreak();
            }

            $this->appendMarkdownInlineToTextRun($textRun, trim($line));
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

    /**
     * Add <w:vMerge> attributes to the linh_vuc column cells for grouped rows.
     *
     * First row in a group  → <w:vMerge w:val="restart"/>
     * Continuation rows     → <w:vMerge/> + paragraph content cleared
     * Single-row groups     → no change
     */
    protected function applyVerticalMerges(TemplateProcessor $tp, array $flatRows): void
    {
        $reflProp = new \ReflectionProperty(TemplateProcessor::class, 'tempDocumentMainPart');
        $reflProp->setAccessible(true);
        $xml = $reflProp->getValue($tp);

        $dom = new \DOMDocument('1.0', 'UTF-8');
        libxml_use_internal_errors(true);
        $dom->loadXML($xml);
        libxml_clear_errors();

        $wNs   = 'http://schemas.openxmlformats.org/wordprocessingml/2006/main';
        $xpath = new \DOMXPath($dom);
        $xpath->registerNamespace('w', $wNs);

        foreach ($flatRows as $i => $row) {
            if ($row['is_only_in_group']) {
                continue;
            }

            $n = $i + 1;

            // Locate the <w:tc> element that contains the linh_vuc#N placeholder.
            $cells = $xpath->query("//w:tc[.//w:t[contains(., 'linh_vuc#{$n}')]]");
            if ($cells->length === 0) {
                continue;
            }

            /** @var \DOMElement $cell */
            $cell = $cells->item(0);

            // Get or create <w:tcPr>.
            /** @var \DOMElement|null $tcPr */
            $tcPr = $xpath->query('w:tcPr', $cell)->item(0);
            if ($tcPr === null) {
                $tcPr = $dom->createElementNS($wNs, 'w:tcPr');
                $cell->insertBefore($tcPr, $cell->firstChild);
            }

            // Remove any previously existing <w:vMerge>.
            foreach (iterator_to_array($xpath->query('w:vMerge', $tcPr)) as $old) {
                $tcPr->removeChild($old);
            }

            $vMerge = $dom->createElementNS($wNs, 'w:vMerge');

            if ($row['is_first_in_group']) {
                // Mark this as the start of the merged region.
                $vMerge->setAttributeNS($wNs, 'w:val', 'restart');
            } else {
                // Continuation cell: clear all paragraphs and replace with one empty paragraph.
                foreach (iterator_to_array($xpath->query('w:p', $cell)) as $p) {
                    $cell->removeChild($p);
                }
                $cell->appendChild($dom->createElementNS($wNs, 'w:p'));
            }

            $tcPr->appendChild($vMerge);
        }

        $reflProp->setValue($tp, $dom->saveXML($dom->documentElement));
    }
}
