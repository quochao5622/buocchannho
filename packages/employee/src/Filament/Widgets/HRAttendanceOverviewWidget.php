<?php

namespace Quochao56\Employee\Filament\Widgets;

use Filament\Widgets\Widget;
use Quochao56\Employee\Filament\Resources\AttendanceCorrectionRequestResource;
use Quochao56\Employee\Filament\Resources\EmployeeAttendanceResource;
use Quochao56\Employee\Filament\Resources\LeaveRequestResource;
use Quochao56\Employee\Models\AttendanceCorrectionRequest;
use Quochao56\Employee\Models\Employee;
use Quochao56\Employee\Models\EmployeeAttendance;
use Quochao56\Employee\Models\LeaveRequest;

class HRAttendanceOverviewWidget extends Widget
{
    protected string $view = 'employee::widgets.hr-attendance-overview-widget';

    protected int|string|array $columnSpan = 'full';

    protected static ?int $sort = -5;

    public static function canView(): bool
    {
        return auth()->user()?->hasPermissionTo('employee_attendances.view_overview') ?? false;
    }

    protected function getViewData(): array
    {
        $today = now()->toDateString();

        $totalEmployees = Employee::active()->count();
        $todayCheckedIn = EmployeeAttendance::whereDate('date', $today)
            ->whereNotNull('check_in_at')
            ->distinct('employee_id')
            ->count('employee_id');

        $todayLate = EmployeeAttendance::whereDate('date', $today)
            ->where('status', 'late')
            ->count();

        $todayAbsent = EmployeeAttendance::whereDate('date', $today)
            ->where('status', 'absent')
            ->count();

        $pendingLeaves = LeaveRequest::where('status', 'pending')->count();
        $pendingCorrections = AttendanceCorrectionRequest::where('status', 'pending')->count();

        $checkInRate = $totalEmployees > 0
            ? round(($todayCheckedIn / $totalEmployees) * 100, 1)
            : 0;

        return [
            'totalEmployees' => $totalEmployees,
            'todayCheckedIn' => $todayCheckedIn,
            'todayLate' => $todayLate,
            'todayAbsent' => $todayAbsent,
            'checkInRate' => $checkInRate,
            'pendingLeaves' => $pendingLeaves,
            'pendingCorrections' => $pendingCorrections,
            'leaveUrl' => LeaveRequestResource::getUrl('index'),
            'correctionUrl' => AttendanceCorrectionRequestResource::getUrl('index'),
            'attendanceUrl' => EmployeeAttendanceResource::getUrl('index'),
        ];
    }
}
