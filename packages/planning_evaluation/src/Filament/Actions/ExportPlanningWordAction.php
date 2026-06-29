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

        $this->label(trans('packages.planning_evaluation::planning.actions.export_word'));
        $this->icon(Heroicon::OutlinedDocumentArrowDown);
        $this->color('info');

        $this->hidden(static function (Model $record): bool {
            if (!method_exists($record, 'trashed')) {
                return false;
            }

            return $record->trashed();
        });

        $this->action(function (Model $record): BinaryFileResponse {
            $startMonth = data_get($record, 'start_date') ? $record->start_date->format('n') : 'unknown';
            $endMonth = data_get($record, 'end_date') ? $record->end_date->format('n') : 'unknown';
            $timeRange = "T{$startMonth}-T{$endMonth}";
            $studentName = mb_strtoupper($record->student?->name ?? 'unknown', 'UTF-8');
            $studentName = implode(' ', array_slice(explode(' ', $studentName), -2));
            $outputFile = 'KHCN_'.$studentName.'_'.$timeRange.'_'.time().'.docx';
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
            __DIR__.'/../../../resources/templates/template_KHCN.docx'
        );

        if (!$templatePath || !file_exists($templatePath)) {
            throw new FileNotFoundException('Template not found at: '.$templatePath);
        }

        $record->loadMissing(['employee', 'student']);

        $student = $record->student;
        $employee = $record->employee;

        $genderMap = [
            'male' => 'Nam',
            'female' => 'Nữ',
            'other' => 'Khác',
        ];

        // Determine time range string example T9-T10 then time is 2 months, if start or end date is missing, use 'unknown' and leave time empty.
        $time = '';
        if ($record->start_date && $record->end_date) {
            $startMonth = $record->start_date->format('n');
            $endMonth = $record->end_date->format('n');
            $time = ($endMonth - $startMonth + 1).' tháng';
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

        if (!empty($details)) {
            $tableRows = [];
            foreach ($details as $detail) {
                $linhVucItems = $detail['linh_vuc'] ?? [];
                $parentCategory = '';
                $subCategory = '';

                $contents = [];
                foreach ($linhVucItems as $item) {
                    $content = trim($item['content'] ?? '');
                    if ($content !== '') {
                        $contents[] = $content;
                    }
                }

                if (count($contents) >= 2) {
                    $parentCategory = $contents[0];
                    $subCategory = $contents[1];
                } elseif (count($contents) === 1) {
                    $lines = explode("\n", $contents[0]);
                    if (count($lines) >= 2) {
                        $parentCategory = trim($lines[0]);
                        $subCategory = trim($lines[1]);
                    } else {
                        $parentCategory = $contents[0];
                    }
                }

                $cleanSubCategory = $subCategory;
                if (str_starts_with($cleanSubCategory, '- ')) {
                    $cleanSubCategory = substr($cleanSubCategory, 2);
                }
                $cleanSubCategory = trim($cleanSubCategory, '*_');

                if ($cleanSubCategory !== '') {
                    // Subheader row
                    $tableRows[] = [
                        'type' => 'subheader',
                        'parent_category' => $parentCategory,
                        'sub_category' => $cleanSubCategory,
                        'linh_vuc' => [['content' => $parentCategory]],
                        'muc_tieu' => [['content' => '*'.$cleanSubCategory.'*']],
                        'hoat_dong' => [],
                        'phuong_tien' => [],
                        'muc_tieu_du_phong' => [],
                    ];

                    // Content row
                    $tableRows[] = [
                        'type' => 'content',
                        'parent_category' => $parentCategory,
                        'sub_category' => $cleanSubCategory,
                        'linh_vuc' => [['content' => $parentCategory]],
                        'muc_tieu' => $detail['muc_tieu'] ?? [],
                        'hoat_dong' => $detail['hoat_dong'] ?? [],
                        'phuong_tien' => $detail['phuong_tien'] ?? [],
                        'muc_tieu_du_phong' => $detail['muc_tieu_du_phong'] ?? [],
                    ];
                } else {
                    $tableRows[] = [
                        'type' => 'content',
                        'parent_category' => $parentCategory,
                        'sub_category' => '',
                        'linh_vuc' => [['content' => $parentCategory]],
                        'muc_tieu' => $detail['muc_tieu'] ?? [],
                        'hoat_dong' => $detail['hoat_dong'] ?? [],
                        'phuong_tien' => $detail['phuong_tien'] ?? [],
                        'muc_tieu_du_phong' => $detail['muc_tieu_du_phong'] ?? [],
                    ];
                }
            }

            $templateProcessor->cloneRow('linh_vuc', count($tableRows));

            $this->applyCellMerges($templateProcessor, $tableRows);

            foreach ($tableRows as $i => $row) {
                $n = $i + 1;
                if ($row['type'] === 'subheader') {
                    $templateProcessor->setComplexValue("linh_vuc#{$n}", $this->buildCellTextRun($row['linh_vuc'] ?? []));
                    $templateProcessor->setComplexValue("muc_tieu#{$n}", $this->buildCellTextRun($row['muc_tieu'] ?? []));
                    $templateProcessor->setValue("hoat_dong#{$n}", '');
                    $templateProcessor->setValue("phuong_tien#{$n}", '');
                    $templateProcessor->setValue("muc_tieu_du_phong#{$n}", '');
                } else {
                    $templateProcessor->setComplexValue("linh_vuc#{$n}", $this->buildCellTextRun($row['linh_vuc'] ?? []));
                    $templateProcessor->setComplexValue("muc_tieu#{$n}", $this->buildCellTextRun($row['muc_tieu'] ?? []));
                    $templateProcessor->setComplexValue("hoat_dong#{$n}", $this->buildCellTextRun($row['hoat_dong'] ?? []));
                    $templateProcessor->setComplexValue("phuong_tien#{$n}", $this->buildCellTextRun($row['phuong_tien'] ?? []));
                    $templateProcessor->setComplexValue("muc_tieu_du_phong#{$n}", $this->buildCellTextRun($row['muc_tieu_du_phong'] ?? []));
                }
            }
        } else {
            $templateProcessor->setValue('linh_vuc', '');
            $templateProcessor->setValue('muc_tieu', '');
            $templateProcessor->setValue('hoat_dong', '');
            $templateProcessor->setValue('phuong_tien', '');
            $templateProcessor->setValue('muc_tieu_du_phong', '');
        }

        $disk = Storage::disk('local');
        $directory = 'exports/plannings';

        if (!$disk->exists($directory)) {
            $disk->makeDirectory($directory);
        }

        $relativePath = $directory.'/'.$outputFile;

        $templateProcessor->saveAs($disk->path($relativePath));

        if (!$disk->exists($relativePath)) {
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
        $textRun = new TextRun;
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

                if (str_starts_with($line, '- ') || str_starts_with($line, '* ')) {
                    $textRun->addText('- ', $this->defaultTextStyle());
                    $line = substr($line, 2);
                }

                $this->appendMarkdownInlineToTextRun($textRun, $line);
                $hasContent = true;
            }
        }

        if (!$hasContent) {
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
                $textRun->addText(htmlspecialchars(substr($text, $offset, $position - $offset), ENT_QUOTES, 'UTF-8'), $this->defaultTextStyle());
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
                $textRun->addText(htmlspecialchars($content, ENT_QUOTES, 'UTF-8'), $style);
            }

            $offset = $position + strlen($token);
        }

        if ($offset < strlen($text)) {
            $textRun->addText(htmlspecialchars(substr($text, $offset), ENT_QUOTES, 'UTF-8'), $this->defaultTextStyle());
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
     * Apply horizontal gridSpan merges for subheaders and vertical vMerge merges for parent categories.
     */
    protected function applyCellMerges(TemplateProcessor $tp, array $tableRows): void
    {
        $reflProp = new \ReflectionProperty(TemplateProcessor::class, 'tempDocumentMainPart');
        $reflProp->setAccessible(true);
        $xml = $reflProp->getValue($tp);

        $dom = new \DOMDocument('1.0', 'UTF-8');
        libxml_use_internal_errors(true);
        $dom->loadXML($xml);
        libxml_clear_errors();

        $wNs = 'http://schemas.openxmlformats.org/wordprocessingml/2006/main';
        $xpath = new \DOMXPath($dom);
        $xpath->registerNamespace('w', $wNs);

        // Group rows by parent_category to apply vertical vMerge
        $groups = [];
        $currentParent = null;
        $currentStartIndex = null;

        foreach ($tableRows as $i => $row) {
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
                'end' => count($tableRows) - 1,
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
                $this->sortTableCellProperties($tcPr);
            }
        }

        // Apply gridSpan merges for subheader rows
        foreach ($tableRows as $i => $row) {
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

            $gridSpan = $dom->createElementNS($wNs, 'w:gridSpan');
            $gridSpan->setAttributeNS($wNs, 'w:val', '4');
            $tcPr->appendChild($gridSpan);

            $tcW = $xpath->query('w:tcW', $tcPr)->item(0);
            if ($tcW !== null) {
                $tcW->setAttributeNS($wNs, 'w:w', '12900');
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

            foreach (['hoat_dong', 'phuong_tien', 'muc_tieu_du_phong'] as $key) {
                $remCells = $xpath->query("//w:tc[.//w:t[contains(., '{$key}#{$n}')]]");
                if ($remCells->length > 0) {
                    $remCell = $remCells->item(0);
                    $remCell->parentNode->removeChild($remCell);
                }
            }

            // Remove trHeight to let the height fit the content automatically
            $tr = $mucTieuCell->parentNode;
            $trPr = $xpath->query('w:trPr', $tr)->item(0);
            if ($trPr !== null) {
                foreach (iterator_to_array($xpath->query('w:trHeight', $trPr)) as $heightNode) {
                    $trPr->removeChild($heightNode);
                }
            }

            // Shade only the merged cell in this row in light gray
            $cPr = $xpath->query('w:tcPr', $mucTieuCell)->item(0);
            if ($cPr === null) {
                $cPr = $dom->createElementNS($wNs, 'w:tcPr');
                $mucTieuCell->insertBefore($cPr, $mucTieuCell->firstChild);
            }
            foreach (iterator_to_array($xpath->query('w:shd', $cPr)) as $oldShd) {
                $cPr->removeChild($oldShd);
            }
            $shd = $dom->createElementNS($wNs, 'w:shd');
            $shd->setAttributeNS($wNs, 'w:val', 'clear');
            $shd->setAttributeNS($wNs, 'w:color', 'auto');
            $shd->setAttributeNS($wNs, 'w:fill', 'F2F2F2'); // Light gray background
            $cPr->appendChild($shd);

            $this->sortTableCellProperties($tcPr);
        }

        $reflProp->setValue($tp, $dom->saveXML());
    }

    /**
     * Sort children of w:tcPr element to match the sequence required by the OpenXML schema.
     */
    protected function sortTableCellProperties(\DOMElement $tcPr): void
    {
        $order = [
            'cnfStyle' => 1,
            'tcW' => 2,
            'gridSpan' => 3,
            'hMerge' => 4,
            'vMerge' => 5,
            'tcBorders' => 6,
            'shd' => 7,
            'noWrap' => 8,
            'tcMar' => 9,
            'textDirection' => 10,
            'tcFitText' => 11,
            'vAlign' => 12,
            'hideMark' => 13,
            'tcPrChange' => 14,
        ];

        $children = [];
        foreach ($tcPr->childNodes as $child) {
            if ($child instanceof \DOMElement) {
                $children[] = $child;
            }
        }

        usort($children, function ($a, $b) use ($order) {
            $aName = $a->localName;
            $bName = $b->localName;
            $aOrder = $order[$aName] ?? 999;
            $bOrder = $order[$bName] ?? 999;

            return $aOrder <=> $bOrder;
        });

        while ($tcPr->hasChildNodes()) {
            $tcPr->removeChild($tcPr->firstChild);
        }

        foreach ($children as $child) {
            $tcPr->appendChild($child);
        }
    }
}
