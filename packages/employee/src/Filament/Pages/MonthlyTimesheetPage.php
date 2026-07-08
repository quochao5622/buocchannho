<?php

namespace Quochao56\Employee\Filament\Pages;

use Carbon\CarbonPeriod;
use Filament\Actions\Action;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Pages\Page;
use Filament\Schemas\Schema;
use Illuminate\Contracts\Support\Htmlable;
use Maatwebsite\Excel\Facades\Excel as ExcelFacade;
use Quochao56\Employee\Exports\EmployeeAttendancePivotExport;
use Quochao56\Employee\Models\Employee;
use Quochao56\Employee\Models\EmployeeAttendance;

class MonthlyTimesheetPage extends Page implements HasForms
{
    use InteractsWithForms;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-table-cells';

    protected static ?int $navigationSort = 5;

    protected string $view = 'employee::filament.pages.monthly-timesheet';

    public ?string $fromDate = null;

    public ?string $toDate = null;

    public ?int $employeeId = null;

    public static function canAccess(): bool
    {
        return auth()->user()?->can('viewAny', EmployeeAttendance::class) ?? false;
    }

    public static function getNavigationGroup(): ?string
    {
        return trans('packages.employee::employee.navigation_group');
    }

    public static function getNavigationLabel(): string
    {
        return 'Bảng công';
    }

    public function getTitle(): string|Htmlable
    {
        return 'Bảng Chấm Công';
    }

    public function mount(): void
    {
        $this->fromDate = now()->subMonth()->startOfMonth()->toDateString();
        $this->toDate = now()->subMonth()->endOfMonth()->toDateString();

        $this->form->fill([
            'fromDate' => $this->fromDate,
            'toDate' => $this->toDate,
            'employeeId' => $this->employeeId,
        ]);
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('export_pivot')
                ->label('Xuất bảng công (Excel)')
                ->icon('heroicon-o-arrow-down-tray')
                ->color('success')
                ->action(fn () => $this->export()),
        ];
    }

    public function export()
    {
        $fromDate = $this->fromDate ?: now()->subMonth()->startOfMonth()->toDateString();
        $toDate = $this->toDate ?: now()->subMonth()->endOfMonth()->toDateString();

        $query = EmployeeAttendance::whereDate('date', '>=', $fromDate)
            ->whereDate('date', '<=', $toDate);

        if ($this->employeeId) {
            $query->where('employee_id', $this->employeeId);
        }

        $records = $query->with('employee')->get();

        return ExcelFacade::download(
            new EmployeeAttendancePivotExport($fromDate, $toDate, $records),
            "bang-cham-cong-{$fromDate}-to-{$toDate}.xlsx"
        );
    }

    public function form(Schema $schema): Schema
    {
        return $schema->components([
            DatePicker::make('fromDate')
                ->label('Từ ngày')
                ->native(false)
                ->displayFormat('d/m/Y')
                ->required()
                ->live()
                ->afterStateUpdated(fn ($state) => $this->fromDate = $state),

            DatePicker::make('toDate')
                ->label('Đến ngày')
                ->native(false)
                ->displayFormat('d/m/Y')
                ->required()
                ->live()
                ->afterStateUpdated(fn ($state) => $this->toDate = $state),

            Select::make('employeeId')
                ->label('Giáo viên/Nhân viên')
                ->placeholder('Tất cả nhân viên')
                ->options(Employee::active()->pluck('name', 'id'))
                ->searchable()
                ->live()
                ->afterStateUpdated(fn ($state) => $this->employeeId = $state),
        ])->columns([
            'sm' => 1,
            'md' => 3,
        ]);
    }

    protected function getViewData(): array
    {
        $fromDate = $this->fromDate ?: now()->subMonth()->startOfMonth()->toDateString();
        $toDate = $this->toDate ?: now()->subMonth()->endOfMonth()->toDateString();

        // 1. Tạo danh sách các ngày
        $period = CarbonPeriod::create($fromDate, $toDate);
        $dates = [];
        foreach ($period as $date) {
            $dates[] = [
                'formatted' => $date->format('d/m'),
                'db' => $date->format('Y-m-d'),
                'is_weekend' => $date->isWeekend(),
            ];
        }

        // 2. Query nhân viên
        $employeesQuery = Employee::active();
        if ($this->employeeId) {
            $employeesQuery->where('id', $this->employeeId);
        }
        $employees = $employeesQuery->orderBy('name')->get();

        // 3. Query dữ liệu chấm công
        $attendanceQuery = EmployeeAttendance::whereDate('date', '>=', $fromDate)
            ->whereDate('date', '<=', $toDate);

        if ($this->employeeId) {
            $attendanceQuery->where('employee_id', $this->employeeId);
        }

        $attendances = $attendanceQuery->get()->groupBy('employee_id');

        // 4. Xây dựng ma trận dữ liệu
        $matrix = [];
        foreach ($employees as $employee) {
            $empAttendances = $attendances->get($employee->id) ?? collect();
            $row = [
                'employee_name' => $employee->name,
                'position' => $employee->position ?? '-',
                'days' => [],
                'total_hours' => 0,
            ];

            foreach ($dates as $dateInfo) {
                $dbDate = $dateInfo['db'];

                // Lấy tất cả ca làm việc trong ngày của nhân viên này
                $dayRecords = $empAttendances->filter(fn ($item) => $item->date->toDateString() === $dbDate);

                $hours = 0;
                $statuses = [];

                foreach ($dayRecords as $record) {
                    $hours += floatval($record->total_hours);
                    $statuses[] = $record->status;
                }

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

                $row['days'][$dbDate] = [
                    'hours' => $hours > 0 ? $hours : null,
                    'status' => $primaryStatus,
                ];

                $row['total_hours'] += $hours;
            }

            $matrix[] = $row;
        }

        return [
            'dates' => $dates,
            'matrix' => $matrix,
        ];
    }
}
