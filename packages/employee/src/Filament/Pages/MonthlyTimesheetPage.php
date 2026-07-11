<?php

namespace Quochao56\Employee\Filament\Pages;

use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Filament\Actions\Action;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\TimePicker;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Schema;
use Illuminate\Contracts\Support\Htmlable;
use Maatwebsite\Excel\Facades\Excel as ExcelFacade;
use Quochao56\Core\Traits\HasNotifications;
use Quochao56\Employee\Exports\EmployeeAttendancePivotExport;
use Quochao56\Employee\Models\Employee;
use Quochao56\Employee\Models\EmployeeAttendance;

class MonthlyTimesheetPage extends Page implements HasForms
{
    use HasNotifications;
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
        $this->fromDate = now()->startOfMonth()->toDateString();
        $this->toDate = now()->endOfMonth()->toDateString();

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
                ->action(fn() => $this->export()),
        ];
    }

    public function export()
    {
        $fromDate = $this->fromDate ?: now()->startOfMonth()->toDateString();
        $toDate = $this->toDate ?: now()->endOfMonth()->toDateString();

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
                ->afterStateUpdated(fn($state) => $this->fromDate = $state),

            DatePicker::make('toDate')
                ->label('Đến ngày')
                ->native(false)
                ->displayFormat('d/m/Y')
                ->required()
                ->live()
                ->afterStateUpdated(fn($state) => $this->toDate = $state),

            Select::make('employeeId')
                ->label('Giáo viên/Nhân viên')
                ->placeholder('Tất cả nhân viên')
                ->options(Employee::active()->pluck('name', 'id'))
                ->searchable()
                ->live()
                ->afterStateUpdated(fn($state) => $this->employeeId = $state),
        ])->columns([
            'sm' => 1,
            'md' => 3,
        ]);
    }

    protected function getViewData(): array
    {
        $fromDate = $this->fromDate ?: now()->startOfMonth()->toDateString();
        $toDate = $this->toDate ?: now()->endOfMonth()->toDateString();
        // $fromDate = $this->fromDate ?: now()->subMonth()->startOfMonth()->toDateString();
        // $toDate = $this->toDate ?: now()->subMonth()->endOfMonth()->toDateString();

        // 1. Tạo danh sách các ngày
        $period = CarbonPeriod::create($fromDate, $toDate);
        $dates = [];
        $dayNames = [
            1 => 'T2',
            2 => 'T3',
            3 => 'T4',
            4 => 'T5',
            5 => 'T6',
            6 => 'T7',
            7 => 'CN',
        ];
        foreach ($period as $date) {
            $dates[] = [
                'formatted' => $date->format('d/m'),
                'db' => $date->format('Y-m-d'),
                'is_weekend' => $date->isWeekend(),
                'is_today' => $date->isToday(),
                'day_name' => $dayNames[$date->dayOfWeekIso],
            ];
        }

        // 2. Query nhân viên
        $employeesQuery = Employee::active();
        if ($this->employeeId) {
            $employeesQuery->where('id', $this->employeeId);
        }
        $employees = $employeesQuery->orderBy('name')->get();

        // 3. Query dữ liệu chấm công
        $attendanceQuery = EmployeeAttendance::with('correctedBy')->whereDate('date', '>=', $fromDate)
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
                'employee_id' => $employee->id,
                'employee_name' => $employee->name,
                'position' => $employee->position ?? '-',
                'days' => [],
                'total_hours' => 0,
            ];

            foreach ($dates as $dateInfo) {
                $dbDate = $dateInfo['db'];

                // Lấy tất cả ca làm việc trong ngày của nhân viên này
                $dayRecords = $empAttendances->filter(fn($item) => $item->date->toDateString() === $dbDate);

                $hours = 0;
                $statuses = [];
                $details = [];

                foreach ($dayRecords as $record) {
                    $hours += floatval($record->total_hours);
                    $statuses[] = $record->status;
                    $details[] = [
                        'session' => $record->session,
                        'check_in_at' => $record->check_in_at?->format('H:i'),
                        'check_out_at' => $record->check_out_at?->format('H:i'),
                        'hours' => floatval($record->total_hours),
                        'status' => $record->status,
                        'notes' => $record->notes,
                        'corrected_by' => $record->correctedBy?->name,
                        'corrected_at' => $record->corrected_at?->format('d/m/Y H:i'),
                    ];
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
                    'details' => $details,
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

    public function editAttendanceAction(): Action
    {
        return Action::make('editAttendanceAction')
            ->modalHeading(function (array $arguments) {
                $employeeId = $arguments['employeeId'] ?? null;
                $date = $arguments['date'] ?? null;
                if ($employeeId && $date) {
                    $employee = Employee::find($employeeId);
                    $formattedDate = Carbon::parse($date)->format('d/m/Y');

                    return "Chấm công - {$employee?->name} ({$formattedDate})";
                }

                return 'Chấm công';
            })
            ->modalSubmitActionLabel('Lưu')
            ->modalCancelActionLabel('Hủy')
            ->fillForm(function (array $arguments) {
                $employeeId = $arguments['employeeId'] ?? null;
                $date = $arguments['date'] ?? null;
                if (! $employeeId || ! $date) {
                    return [];
                }

                $sessionOrder = ['morning' => 1, 'afternoon' => 2, 'evening' => 3];

                $records = EmployeeAttendance::where('employee_id', $employeeId)
                    ->whereDate('date', $date)
                    ->get()
                    ->sortBy(fn($record) => $sessionOrder[$record->session] ?? 4)
                    ->values();

                $attendances = $records->map(fn($record) => [
                    'id' => $record->id,
                    'session' => $record->session,
                    'check_in_at' => $record->check_in_at?->format('H:i:s'),
                    'check_out_at' => $record->check_out_at?->format('H:i:s'),
                    'total_hours' => $record->total_hours,
                    'status' => $record->status,
                    'notes' => $record->notes,
                ])->toArray();

                return [
                    'attendances' => $attendances,
                ];
            })
            ->form([
                Repeater::make('attendances')
                    ->label('Danh sách ca làm việc')
                    ->schema([
                        Hidden::make('id'),
                        Grid::make(3)
                            ->schema([
                                Select::make('session')
                                    ->label('Ca làm việc')
                                    ->options([
                                        'morning' => 'Ca sáng',
                                        'afternoon' => 'Ca chiều',
                                        'evening' => 'Ca tối',
                                    ])
                                    ->required()
                                    ->default('morning')
                                    ->live()
                                    ->afterStateUpdated(function (string $state, $set) {
                                        if ($state === 'morning') {
                                            $set('check_in_at', settings('office_morning_start', '08:00'));
                                            $set('check_out_at', settings('office_morning_end', '12:00'));
                                        } elseif ($state === 'afternoon') {
                                            $set('check_in_at', settings('office_afternoon_start', '13:30'));
                                            $set('check_out_at', settings('office_afternoon_end', '17:30'));
                                        } elseif ($state === 'evening') {
                                            $set('check_in_at', settings('office_evening_start', '18:00'));
                                            $set('check_out_at', settings('office_evening_end', '21:00'));
                                        }
                                    }),
                                Select::make('status')
                                    ->label('Trạng thái')
                                    ->options([
                                        'present' => 'Có mặt',
                                        'late' => 'Đi muộn',
                                        'early_leave' => 'Về sớm',
                                        'absent' => 'Vắng mặt',
                                        'on_leave' => 'Nghỉ phép',
                                    ])
                                    ->default('present')
                                    ->required(),
                                TextInput::make('total_hours')
                                    ->label('Số giờ công')
                                    ->numeric()
                                    ->step(0.1)
                                    ->nullable()
                                    ->placeholder('Tự động tính nếu trống'),
                            ]),
                        Grid::make(2)
                            ->schema([
                                TimePicker::make('check_in_at')
                                    ->label('Giờ Check-in')
                                    ->native(false)
                                    ->displayFormat('H:i:s')
                                    ->nullable()
                                    ->default(settings('office_morning_start', '08:00')),
                                TimePicker::make('check_out_at')
                                    ->label('Giờ Check-out')
                                    ->native(false)
                                    ->displayFormat('H:i:s')
                                    ->nullable()
                                    ->default(settings('office_morning_end', '12:00')),
                            ]),
                        Textarea::make('notes')
                            ->label('Ghi chú')
                            ->rows(1)
                            ->columnSpanFull()
                            ->nullable(),
                    ])
                    ->rules([
                        fn() => function (string $attribute, $value, \Closure $fail) {
                            if (! is_array($value)) {
                                return;
                            }
                            $sessions = collect($value)->pluck('session')->filter()->toArray();
                            if (count($sessions) !== count(array_unique($sessions))) {
                                $fail('Mỗi ca làm việc (Sáng, Chiều, Tối) chỉ được chọn một lần duy nhất trong ngày.');
                            }
                        },
                    ])
                    ->createItemButtonLabel('Thêm ca làm việc'),
            ])
            ->action(function (array $data, array $arguments) {
                $employeeId = $arguments['employeeId'] ?? null;
                $date = $arguments['date'] ?? null;
                if (! $employeeId || ! $date) {
                    return;
                }

                $submittedIds = collect($data['attendances'])->pluck('id')->filter()->toArray();

                // Xóa các ca làm việc bị xóa khỏi repeater
                EmployeeAttendance::where('employee_id', $employeeId)
                    ->whereDate('date', $date)
                    ->whereNotIn('id', $submittedIds)
                    ->delete();

                foreach ($data['attendances'] as $item) {
                    $checkInTime = $item['check_in_at'] ?? null;
                    $checkOutTime = $item['check_out_at'] ?? null;

                    $checkInDateTime = $checkInTime ? Carbon::parse($date . ' ' . $checkInTime) : null;
                    $checkOutDateTime = $checkOutTime ? Carbon::parse($date . ' ' . $checkOutTime) : null;

                    $totalHours = $item['total_hours'];
                    if (is_null($totalHours) && $checkInDateTime && $checkOutDateTime) {
                        $totalHours = EmployeeAttendance::calculateTotalHours(
                            $employeeId,
                            Carbon::parse($date),
                            $checkInDateTime,
                            $checkOutDateTime,
                            $item['session']
                        );
                    }

                    $attendanceData = [
                        'employee_id' => $employeeId,
                        'date' => $date,
                        'session' => $item['session'],
                        'check_in_at' => $checkInDateTime,
                        'check_out_at' => $checkOutDateTime,
                        'total_hours' => $totalHours,
                        'status' => $item['status'],
                        'notes' => $item['notes'],
                        'verification_status' => 'approved',
                        'corrected_by' => auth()->id(),
                        'corrected_at' => now(),
                    ];

                    if (! empty($item['id'])) {
                        EmployeeAttendance::where('id', $item['id'])->update($attendanceData);
                    } else {
                        EmployeeAttendance::create($attendanceData);
                    }
                }

                $this->notify(
                    Notification::make()
                        ->title('Cập nhật chấm công thành công')
                        ->success()
                );
            });
    }
}
