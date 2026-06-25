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

        $this->label(trans('packages.planning_evaluation::planning.actions.export_word'));
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
            $studentName = mb_strtoupper($student?->name ?? 'unknown', 'UTF-8');
            $studentName = implode(' ', array_slice(explode(' ', $studentName), -2));

            $outputFile = 'KQDG_' . $studentName . "_{$timeRange}_" . time() . '.docx';

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

            // Apply merges and formatting for the table.
            $this->applyCellMerges($templateProcessor, $flatRows);

            foreach ($flatRows as $i => $row) {
                $n       = $i + 1;
                $danhGia = $row['danh_gia'] ?? null;

                if ($row['type'] === 'subheader') {
                    $templateProcessor->setComplexValue("linh_vuc#{$n}", $this->buildTextRunFromMarkdown((string) $row['linh_vuc']));
                    $templateProcessor->setComplexValue("muc_tieu#{$n}", $this->buildTextRunFromMarkdown((string) $row['content']));
                    $templateProcessor->setValue("danh_gia_1#{$n}", '');
                    $templateProcessor->setValue("danh_gia_2#{$n}", '');
                    $templateProcessor->setValue("danh_gia_3#{$n}", '');
                    $templateProcessor->setValue("nhan_xet#{$n}", '');
                } else {
                    $templateProcessor->setComplexValue("linh_vuc#{$n}", $this->buildTextRunFromMarkdown((string) $row['linh_vuc']));
                    $templateProcessor->setComplexValue("muc_tieu#{$n}", $this->buildTextRunFromMarkdown((string) $row['content']));
                    $templateProcessor->setComplexValue("nhan_xet#{$n}", $this->buildTextRunFromMarkdown((string) ($row['nhan_xet'] ?? '')));
                    $templateProcessor->setValue("danh_gia_1#{$n}", $danhGia === '+'   ? '+' : '');
                    $templateProcessor->setValue("danh_gia_2#{$n}", $danhGia === '+/-' ? '+/-' : '');
                    $templateProcessor->setValue("danh_gia_3#{$n}", $danhGia === '-'   ? '-' : '');
                }
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
     * Flatten evaluation_details into one row per mục tiêu entry, inserting subheaders when needed.
     */
    protected function buildFlatRows(array $details): array
    {
        $rows = [];

        foreach ($details as $detail) {
            $linhVuc = (string) ($detail['linh_vuc'] ?? '');
            $mucList = (array) ($detail['muc_tieu'] ?? []);
            $count   = count($mucList);

            $lines = explode("\n", $linhVuc);
            $parentCategory = '';
            $subCategory = '';
            if (count($lines) >= 2) {
                $parentCategory = trim($lines[0]);
                $subCategory = trim($lines[1]);
            } else {
                $parentCategory = $linhVuc;
            }

            $cleanSubCategory = $subCategory;
            if (str_starts_with($cleanSubCategory, '- ')) {
                $cleanSubCategory = substr($cleanSubCategory, 2);
            }
            if (str_starts_with($cleanSubCategory, '* ')) {
                $cleanSubCategory = substr($cleanSubCategory, 2);
            }
            $cleanSubCategory = trim($cleanSubCategory, '*_');

            if ($cleanSubCategory !== '') {
                // Subheader row
                $rows[] = [
                    'type' => 'subheader',
                    'parent_category' => $parentCategory,
                    'sub_category' => $cleanSubCategory,
                    'linh_vuc' => $parentCategory,
                    'content' => '*' . $cleanSubCategory . '*',
                    'danh_gia' => null,
                    'nhan_xet' => '',
                ];

                // Content rows
                foreach ($mucList as $goal) {
                    $rows[] = [
                        'type' => 'content',
                        'parent_category' => $parentCategory,
                        'sub_category' => $cleanSubCategory,
                        'linh_vuc' => $parentCategory,
                        'content' => (string) ($goal['content'] ?? ''),
                        'danh_gia' => $goal['danh_gia'] ?? null,
                        'nhan_xet' => (string) ($goal['nhan_xet'] ?? ''),
                    ];
                }
            } else {
                if ($count === 0) {
                    $rows[] = [
                        'type' => 'content',
                        'parent_category' => $parentCategory,
                        'sub_category' => '',
                        'linh_vuc' => $parentCategory,
                        'content' => '',
                        'danh_gia' => null,
                        'nhan_xet' => '',
                    ];
                } else {
                    foreach ($mucList as $goal) {
                        $rows[] = [
                            'type' => 'content',
                            'parent_category' => $parentCategory,
                            'sub_category' => '',
                            'linh_vuc' => $parentCategory,
                            'content' => (string) ($goal['content'] ?? ''),
                            'danh_gia' => $goal['danh_gia'] ?? null,
                            'nhan_xet' => (string) ($goal['nhan_xet'] ?? ''),
                        ];
                    }
                }
            }
        }

        return $rows;
    }

    protected function buildTextRunFromMarkdown(string $text): TextRun
    {
        $textRun = new TextRun();
        $hasContent = false;

        foreach (preg_split('/\r\n|\r|\n/u', $text) ?: [] as $line) {
            $line = trim($line);

            if ($line === '') {
                continue;
            }

            if ($hasContent) {
                $textRun->addTextBreak();
            }

            if (str_starts_with($line, '- ') || str_starts_with($line, '* ')) {
                $textRun->addText('- ', $this->defaultTextStyle());
                $line = substr($line, 2);
            }

            $this->appendMarkdownInlineToTextRun($textRun, $line);
            $hasContent = true;
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

    /**
     * Apply horizontal gridSpan merges for subheaders and vertical vMerge merges for parent categories in Evaluation.
     */
    protected function applyCellMerges(TemplateProcessor $tp, array $flatRows): void
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

        // Group rows by parent_category to apply vertical vMerge
        $groups = [];
        $currentParent = null;
        $currentStartIndex = null;

        foreach ($flatRows as $i => $row) {
            $parent = $row['parent_category'];
            if ($parent !== $currentParent) {
                if ($currentParent !== null) {
                    $groups[] = [
                        'start' => $currentStartIndex,
                        'end' => $i - 1,
                    ];
                }
                $currentParent = $parent;
                $currentStartIndex = $i;
            }
        }
        if ($currentParent !== null) {
            $groups[] = [
                'start' => $currentStartIndex,
                'end' => count($flatRows) - 1,
            ];
        }

        foreach ($groups as $group) {
            $start = $group['start'];
            $end = $group['end'];
            if ($start === $end) {
                continue;
            }

            for ($i = $start; $i <= $end; $i++) {
                $n = $i + 1;
                $cells = $xpath->query("//w:tc[.//w:t[contains(., 'linh_vuc#{$n}')]]");
                if ($cells->length === 0) {
                    continue;
                }
                /** @var \DOMElement $cell */
                $cell = $cells->item(0);

                $tcPr = $xpath->query('w:tcPr', $cell)->item(0);
                if ($tcPr === null) {
                    $tcPr = $dom->createElementNS($wNs, 'w:tcPr');
                    $cell->insertBefore($tcPr, $cell->firstChild);
                }

                foreach (iterator_to_array($xpath->query('w:vMerge', $tcPr)) as $old) {
                    $tcPr->removeChild($old);
                }

                $vMerge = $dom->createElementNS($wNs, 'w:vMerge');
                if ($i === $start) {
                    $vMerge->setAttributeNS($wNs, 'w:val', 'restart');
                } else {
                    foreach (iterator_to_array($xpath->query('w:p', $cell)) as $p) {
                        $cell->removeChild($p);
                    }
                    $cell->appendChild($dom->createElementNS($wNs, 'w:p'));
                }
                $tcPr->appendChild($vMerge);
            }
        }

        // Apply gridSpan merges for subheader rows
        foreach ($flatRows as $i => $row) {
            if ($row['type'] !== 'subheader') {
                continue;
            }
            $n = $i + 1;

            $cells = $xpath->query("//w:tc[.//w:t[contains(., 'muc_tieu#{$n}')]]");
            if ($cells->length === 0) {
                continue;
            }
            /** @var \DOMElement $mucTieuCell */
            $mucTieuCell = $cells->item(0);

            $tcPr = $xpath->query('w:tcPr', $mucTieuCell)->item(0);
            if ($tcPr === null) {
                $tcPr = $dom->createElementNS($wNs, 'w:tcPr');
                $mucTieuCell->insertBefore($tcPr, $mucTieuCell->firstChild);
            }

            // gridSpan w:val="5" to cover muc_tieu, danh_gia_1, danh_gia_2, danh_gia_3, nhan_xet
            $gridSpan = $dom->createElementNS($wNs, 'w:gridSpan');
            $gridSpan->setAttributeNS($wNs, 'w:val', '5');
            $tcPr->appendChild($gridSpan);

            // Set width to 12049 dxa (sum of the 5 columns)
            $tcW = $xpath->query('w:tcW', $tcPr)->item(0);
            if ($tcW !== null) {
                $tcW->setAttributeNS($wNs, 'w:w', '12049');
            }

            /** @var \DOMElement|null $p */
            $p = $xpath->query('w:p', $mucTieuCell)->item(0);
            if ($p !== null) {
                $pPr = $xpath->query('w:pPr', $p)->item(0);
                if ($pPr === null) {
                    $pPr = $dom->createElementNS($wNs, 'w:pPr');
                    $p->insertBefore($pPr, $p->firstChild);
                }
                $jc = $xpath->query('w:jc', $pPr)->item(0);
                if ($jc !== null) {
                    $pPr->removeChild($jc);
                }
                $newJc = $dom->createElementNS($wNs, 'w:jc');
                $newJc->setAttributeNS($wNs, 'w:val', 'center');
                $pPr->appendChild($newJc);
            }

            // Remove sibling cells in subheader row
            foreach (['danh_gia_1', 'danh_gia_2', 'danh_gia_3', 'nhan_xet'] as $key) {
                $remCells = $xpath->query("//w:tc[.//w:t[contains(., '{$key}#{$n}')]]");
                if ($remCells->length > 0) {
                    $remCell = $remCells->item(0);
                    $remCell->parentNode->removeChild($remCell);
                }
            }

            // Remove trHeight to let height fit content automatically
            $tr = $mucTieuCell->parentNode;
            $trPr = $xpath->query('w:trPr', $tr)->item(0);
            if ($trPr !== null) {
                foreach (iterator_to_array($xpath->query('w:trHeight', $trPr)) as $heightNode) {
                    $trPr->removeChild($heightNode);
                }
            }

            // Shade only the merged cell in this row in light gray
            $shd = $dom->createElementNS($wNs, 'w:shd');
            $shd->setAttributeNS($wNs, 'w:val', 'clear');
            $shd->setAttributeNS($wNs, 'w:color', 'auto');
            $shd->setAttributeNS($wNs, 'w:fill', 'F2F2F2');
            $tcPr->appendChild($shd);
        }

        $reflProp->setValue($tp, $dom->saveXML($dom->documentElement));
    }
}
