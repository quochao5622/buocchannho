<?php

namespace Quochao56\Employee\Exports;

use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class EmployeeAttendancePivotExport implements FromArray, WithColumnWidths, WithHeadings, WithStyles
{
    protected string $fromDate;

    protected string $toDate;

    protected $records;

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
        $dateStrings = [];
        foreach ($period as $date) {
            $dateStrings[] = $date->format('Y-m-d');
        }

        foreach ($grouped as $employeeId => $attendances) {
            $firstRecord = $attendances->first();
            $employee = $firstRecord ? $firstRecord->employee : null;
            $employeeName = $employee ? $employee->name : 'N/A';
            $position = $employee ? $employee->position : '-';

            $row = [
                'employee_name' => $employeeName,
                'position' => $position,
            ];

            foreach ($dateStrings as $ds) {
                $row[$ds] = 0;
            }

            $totalHours = 0;
            foreach ($attendances as $attendance) {
                if ($attendance->date) {
                    $dateStr = Carbon::parse($attendance->date)->format('Y-m-d');
                    if (isset($row[$dateStr])) {
                        $hours = floatval($attendance->total_hours);
                        $row[$dateStr] += $hours;
                        $totalHours += $hours;
                    }
                }
            }

            $row['total_hours'] = $totalHours;

            $rows[] = array_values($row);
        }

        return $rows;
    }

    public function columnWidths(): array
    {
        return [
            'A' => 30, // Giáo viên
            'B' => 20, // Chức vụ
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true]],
        ];
    }
}
