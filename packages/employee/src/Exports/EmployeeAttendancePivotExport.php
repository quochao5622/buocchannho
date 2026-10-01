<?php

namespace Quochao56\Employee\Exports;

use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class EmployeeAttendancePivotExport implements FromArray, WithColumnWidths, WithHeadings, WithStyles
{
    protected string $fromDate;

    protected string $toDate;

    protected $records;

    protected array $styleMap = [];

    public function __construct(string $fromDate, string $toDate, $records)
    {
        $this->fromDate = $fromDate;
        $this->toDate = $toDate;
        $this->records = $records;
    }

    public function headings(): array
    {
        $headings = ['Giáo viên', 'Chức vụ'];

        $period = CarbonPeriod::create($this->fromDate, $this->toDate);
        foreach ($period as $date) {
            $headings[] = $date->format('d/m');
        }

        $headings[] = 'Tổng cộng';

        return $headings;
    }

    public function array(): array
    {
        // Nhóm theo employee_id để truy xuất thông tin nhân viên đầy đủ
        $grouped = $this->records->groupBy('employee_id');

        $rows = [];
        $period = CarbonPeriod::create($this->fromDate, $this->toDate);
        $datesInfo = [];
        foreach ($period as $date) {
            $datesInfo[] = [
                'db' => $date->format('Y-m-d'),
                'is_weekend' => $date->isWeekend(),
            ];
        }

        $rowIndex = 2; // Data rows start at row 2
        foreach ($grouped as $employeeId => $attendances) {
            $firstRecord = $attendances->first();
            $employee = $firstRecord ? $firstRecord->employee : null;
            $employeeName = $employee ? $employee->name : 'N/A';
            $position = $employee ? $employee->position : '-';

            $rowValues = [
                $employeeName,
                $position,
            ];

            $totalHours = 0;
            $colIndex = 3; // Date columns start at C (index 3)

            foreach ($datesInfo as $dateVal) {
                $dbDate = $dateVal['db'];
                $isWeekend = $dateVal['is_weekend'];

                // Lọc dữ liệu chấm công của ngày hôm đó
                $dayRecords = $attendances->filter(function ($item) use ($dbDate) {
                    $itemDate = $item->date;
                    if ($itemDate instanceof Carbon) {
                        return $itemDate->toDateString() === $dbDate;
                    }

                    return Carbon::parse($itemDate)->toDateString() === $dbDate;
                });

                $hours = 0;
                $statuses = [];
                foreach ($dayRecords as $record) {
                    $hours += floatval($record->total_hours);
                    $statuses[] = $record->status;
                }

                // Cộng dồn tổng giờ công
                $totalHours += $hours;

                // Xác định trạng thái chính của ngày hôm đó
                $primaryStatus = 'none';
                if ($dayRecords->isNotEmpty()) {
                    if (in_array('absent', $statuses)) {
                        $primaryStatus = 'absent';
                    } elseif (in_array('on_leave', $statuses)) {
                        $primaryStatus = 'on_leave';
                    } elseif (in_array('late', $statuses)) {
                        $primaryStatus = 'late';
                    } else {
                        $primaryStatus = 'present';
                    }
                }

                // Thiết lập hiển thị giá trị ô và màu sắc
                $cellValue = '-';
                $cellBg = null;
                $cellFont = null;
                $isBold = false;

                if ($primaryStatus === 'on_leave') {
                    $cellValue = 'P';
                    $cellBg = 'F3E8FF'; // tím nhạt
                    $cellFont = '7E22CE'; // tím đậm
                    $isBold = true;
                } elseif ($primaryStatus === 'absent') {
                    $cellValue = 'V';
                    $cellBg = 'FFE4E6'; // hồng nhạt
                    $cellFont = 'BE123C'; // hồng đỏ
                    $isBold = true;
                } elseif ($hours > 0) {
                    if ($hours < 4.0) {
                        $cellValue = number_format($hours, 1);
                        $cellBg = 'FFE4E6'; // hồng nhạt
                        $cellFont = 'BE123C'; // hồng đỏ
                        $isBold = true;
                    } elseif ($hours < 8.0) {
                        $cellValue = number_format($hours, 1);
                        $cellBg = 'FEF3C7'; // vàng nhạt
                        $cellFont = '92400E'; // cam nâu
                        $isBold = true;
                    } elseif ($hours == 8.0) {
                        $cellValue = number_format($hours, 1);
                        $cellBg = 'D1FAE5'; // lục nhạt
                        $cellFont = '065F46'; // lục đậm
                        $isBold = true;
                    } else {
                        $cellValue = '+'.number_format($hours, 1);
                        $cellBg = 'E0F2FE'; // xanh trời nhạt
                        $cellFont = '075985'; // xanh trời đậm
                        $isBold = true;
                    }
                } else {
                    if ($isWeekend) {
                        $cellBg = 'FFEDD5'; // cam nhạt cho ngày cuối tuần không đi làm
                        $cellFont = 'C2410C'; // cam đậm
                    }
                }

                $rowValues[] = $cellValue;

                // Lưu lại cấu hình màu sắc để áp dụng trong styles()
                if ($cellBg || $cellFont) {
                    $this->styleMap[$rowIndex][$colIndex] = [
                        'bg' => $cellBg,
                        'font' => $cellFont,
                        'bold' => $isBold,
                    ];
                }

                $colIndex++;
            }

            // Ghi nhận cột Tổng cộng
            $rowValues[] = $totalHours;

            // Định dạng màu sắc cột Tổng cộng dựa trên KPI
            $kpiBg = 'FFE4E6'; // Đỏ (thiếu giờ)
            $kpiFont = 'BE123C';
            if ($totalHours >= 160.0) {
                $kpiBg = 'D1FAE5'; // Xanh lá (đủ giờ)
                $kpiFont = '065F46';
            } elseif ($totalHours >= 140.0) {
                $kpiBg = 'FEF3C7'; // Vàng/Cam (gần đủ)
                $kpiFont = '92400E';
            }

            $this->styleMap[$rowIndex][$colIndex] = [
                'bg' => $kpiBg,
                'font' => $kpiFont,
                'bold' => true,
            ];

            $rows[] = $rowValues;
            $rowIndex++;
        }

        return $rows;
    }

    public function columnWidths(): array
    {
        $widths = [
            'A' => 30, // Giáo viên
            'B' => 20, // Chức vụ
        ];

        $period = CarbonPeriod::create($this->fromDate, $this->toDate);
        $colIndex = 3;
        foreach ($period as $date) {
            $colLetter = Coordinate::stringFromColumnIndex($colIndex);
            $widths[$colLetter] = 9; // Độ rộng ô ngày vừa đủ hiển thị (ví dụ: '+10.0')
            $colIndex++;
        }

        $totalColLetter = Coordinate::stringFromColumnIndex($colIndex);
        $widths[$totalColLetter] = 15; // Độ rộng ô tổng cộng

        return $widths;
    }

    public function styles(Worksheet $sheet)
    {
        $grouped = $this->records->groupBy('employee_id');
        $rowCount = 1 + count($grouped);

        $period = CarbonPeriod::create($this->fromDate, $this->toDate);
        $daysCount = iterator_count($period);
        $totalColumns = 3 + $daysCount;
        $lastColLetter = Coordinate::stringFromColumnIndex($totalColumns);

        // 1. Áp dụng font chữ và đường viền lưới (gridlines)
        $sheet->getStyle("A1:{$lastColLetter}{$rowCount}")->applyFromArray([
            'font' => [
                'name' => 'Arial',
                'size' => 10,
            ],
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                    'color' => ['rgb' => 'E5E7EB'],
                ],
            ],
        ]);

        // 2. Căn lề
        // Tên giáo viên và chức vụ căn trái
        $sheet->getStyle("A1:B{$rowCount}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);
        $sheet->getStyle("A1:B{$rowCount}")->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);

        // Các ô dữ liệu ngày và cột tổng cộng căn giữa
        $sheet->getStyle("C1:{$lastColLetter}{$rowCount}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle("C1:{$lastColLetter}{$rowCount}")->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);

        // 3. Định dạng Dòng tiêu đề (Header)
        $sheet->getStyle("A1:{$lastColLetter}1")->applyFromArray([
            'font' => [
                'bold' => true,
                'color' => ['rgb' => '111827'],
            ],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['rgb' => 'F3F4F6'],
            ],
        ]);

        // 4. Áp dụng các màu sắc và định dạng in đậm đã lưu trong quá trình sinh dữ liệu
        foreach ($this->styleMap as $rowIndex => $cols) {
            foreach ($cols as $colIndex => $style) {
                $colLetter = Coordinate::stringFromColumnIndex($colIndex);
                $cellCoordinate = "{$colLetter}{$rowIndex}";

                $cellStyle = [];
                if (! empty($style['bg'])) {
                    $cellStyle['fill'] = [
                        'fillType' => Fill::FILL_SOLID,
                        'startColor' => ['rgb' => $style['bg']],
                    ];
                }
                if (! empty($style['font'])) {
                    $cellStyle['font']['color'] = ['rgb' => $style['font']];
                }
                if (! empty($style['bold'])) {
                    $cellStyle['font']['bold'] = true;
                }

                if (! empty($cellStyle)) {
                    $sheet->getStyle($cellCoordinate)->applyFromArray($cellStyle);
                }
            }
        }

        // Thiết lập chiều cao hàng cho đẹp mắt và thoáng hơn
        $sheet->getRowDimension(1)->setRowHeight(28); // Dòng tiêu đề cao hơn
        for ($i = 2; $i <= $rowCount; $i++) {
            $sheet->getRowDimension($i)->setRowHeight(22); // Dòng dữ liệu
        }
    }
}
