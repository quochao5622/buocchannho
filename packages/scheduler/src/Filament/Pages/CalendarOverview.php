<?php

namespace Quochao56\Scheduler\Filament\Pages;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Pages\Page;
use Filament\Schemas\Schema;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Support\Facades\Auth;
use Quochao56\Employee\Models\Employee;
use Quochao56\Scheduler\Models\Classroom;
use Quochao56\Student\Models\Student;

class CalendarOverview extends Page implements HasForms
{
    use InteractsWithForms;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-calendar';

    public static function getNavigationLabel(): string
    {
        return 'Lịch biểu tổng quan';
    }

    public function getTitle(): string|Htmlable
    {
        return 'Lịch biểu tổng quan';
    }

    public static function getNavigationGroup(): ?string
    {
        return trans('packages.scheduler::scheduler.navigation_group');
    }

    protected static ?int $navigationSort = 3;

    public static function canAccess(): bool
    {
        $user = Auth::user();

        return $user && ($user->isSuperAdmin() || $user->hasPermissionTo('schedules.index'));
    }

    protected string $view = 'scheduler::calendar-overview';

    public ?int $employeeId = null;

    public ?int $studentId = null;

    public ?int $classroomId = null;

    public ?string $guardianKeyword = null;

    public function mount(): void
    {
        $this->form->fill([
            'employeeId' => $this->employeeId,
            'studentId' => $this->studentId,
            'classroomId' => $this->classroomId,
            'guardianKeyword' => $this->guardianKeyword,
        ]);
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('employeeId')
                    ->label('Giáo viên')
                    ->placeholder('Tất cả giáo viên')
                    ->options(function () {
                        $teachers = Employee::active()->get();
                        $grouped = [];
                        foreach ($teachers as $teacher) {
                            $posLabel = $teacher->position ?: 'Chưa phân chức vụ';
                            $grouped[$posLabel][$teacher->id] = $teacher->name;
                        }

                        return $grouped;
                    })
                    ->searchable()
                    ->live()
                    ->afterStateUpdated(fn ($state) => $this->employeeId = $state),

                Select::make('studentId')
                    ->label('Học sinh')
                    ->placeholder('Tất cả học sinh')
                    ->options(Student::active()->pluck('name', 'id'))
                    ->searchable()
                    ->live()
                    ->afterStateUpdated(fn ($state) => $this->studentId = $state),

                Select::make('classroomId')
                    ->label('Phòng học')
                    ->placeholder('Tất cả phòng học')
                    ->options(Classroom::active()->pluck('name', 'id'))
                    ->searchable()
                    ->live()
                    ->afterStateUpdated(fn ($state) => $this->classroomId = $state),

                TextInput::make('guardianKeyword')
                    ->label('Tìm theo phụ huynh')
                    ->placeholder('Tên cha/mẹ hoặc số điện thoại')
                    ->live(debounce: 600)
                    ->afterStateUpdated(fn ($state) => $this->guardianKeyword = $state),
            ]);
    }
}
