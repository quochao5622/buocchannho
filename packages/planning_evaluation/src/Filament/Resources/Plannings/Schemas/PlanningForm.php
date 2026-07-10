<?php

namespace Quochao56\PlanningEvaluation\Filament\Resources\Plannings\Schemas;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\MarkdownEditor;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Schema;
use Illuminate\Database\Eloquent\Model;
use Quochao56\Core\Enum\BaseStatusEnum;
use Quochao56\Employee\Models\Employee;
use Quochao56\Student\Models\Student;

class PlanningForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->label(trans('packages.planning_evaluation::planning.fields.name'))
                    ->required(),
                Textarea::make('description')
                    ->label(trans('packages.planning_evaluation::planning.fields.description')),
                Select::make('employee_id')
                    ->label(trans('packages.planning_evaluation::planning.fields.employee'))
                    ->options(function () {
                        $query = Employee::query();
                        if (auth()->check() && ! auth()->user()->isSuperAdmin()) {
                            $canManageAll = auth()->user()->hasPermissionTo('employees.index')
                                || auth()->user()->hasPermissionTo('employees.edit');
                            if (! $canManageAll) {
                                $query->where('email', auth()->user()->email);
                            }
                        }

                        return $query->pluck('name', 'id')->toArray();
                    })
                    ->default(function () {
                        $studentId = request()->query('student_id');
                        if ($studentId) {
                            $student = Student::find($studentId);

                            return $student?->currentAssignment?->employee_id ?? 4;
                        }
                        $email = auth()->user()->email;
                        if ($email) {
                            return Employee::where('email', $email)->first()?->id;
                        }

                        return 4;
                    })
                    ->disabled(fn (?Model $record) => $record !== null)
                    ->searchable(),
                Select::make('student_id')
                    ->label(trans('packages.planning_evaluation::planning.fields.student'))
                    ->options(function () {
                        $query = Student::query()->where('status', 'active');
                        if (auth()->check() && ! auth()->user()->isSuperAdmin()) {
                            $canManageAll = auth()->user()->hasPermissionTo('employees.index')
                                || auth()->user()->hasPermissionTo('employees.edit');
                            if (! $canManageAll) {
                                $employee = Employee::where('email', auth()->user()->email)->first();
                                if ($employee) {
                                    $query->whereHas('currentAssignment', function ($q) use ($employee) {
                                        $q->where('employee_id', $employee->id);
                                    });
                                } else {
                                    $query->whereRaw('1 = 0');
                                }
                            }
                        }

                        return $query->pluck('name', 'id')->toArray();
                    })
                    ->default(fn () => request()->query('student_id'))
                    ->disabled(fn (?Model $record) => $record !== null)
                    ->searchable(),
                DatePicker::make('start_date')
                    ->label(trans('packages.planning_evaluation::planning.fields.start_date'))
                    ->native(false)
                    ->displayFormat('d/m/Y'),
                DatePicker::make('end_date')
                    ->label(trans('packages.planning_evaluation::planning.fields.end_date'))
                    ->native(false)
                    ->displayFormat('d/m/Y'),
                Select::make('status')
                    ->label(trans('packages.planning_evaluation::planning.fields.status'))
                    ->options(
                        fn () => auth()->user()->can('plannings.approve')
                            ? [
                                BaseStatusEnum::Published->value => BaseStatusEnum::Published->getLabel(),
                                BaseStatusEnum::Pending->value => BaseStatusEnum::Pending->getLabel(),
                                BaseStatusEnum::Draft->value => BaseStatusEnum::Draft->getLabel(),
                            ]
                            : [
                                BaseStatusEnum::Pending->value => BaseStatusEnum::Pending->getLabel(),
                                BaseStatusEnum::Draft->value => BaseStatusEnum::Draft->getLabel(),
                            ]
                    )
                    ->default(fn () => auth()->user()->can('plannings.approve') ? BaseStatusEnum::Published->value : BaseStatusEnum::Draft->value)
                    ->disabled(fn ($record) => ($record?->status?->value ?? $record?->status) === BaseStatusEnum::Published->value)
                    ->required(),
                Repeater::make('planning_details')
                    ->label(trans('packages.planning_evaluation::planning.fields.details'))
                    ->default([
                        [
                            'linh_vuc' => [
                                ['content' => '**Kỹ năng tiền đề**'],
                                ['content' => '- Chú ý'],
                            ],
                            'muc_tieu' => [],
                            'hoat_dong' => [],
                            'phuong_tien' => [],
                            'muc_tieu_du_phong' => [],
                        ],
                        [
                            'linh_vuc' => [
                                ['content' => '**Kỹ năng tiền đề**'],
                                ['content' => '- Bắt chước'],
                            ],
                            'muc_tieu' => [],
                            'hoat_dong' => [],
                            'phuong_tien' => [],
                            'muc_tieu_du_phong' => [],
                        ],
                        [
                            'linh_vuc' => [
                                ['content' => '**Kỹ năng tiền đề**'],
                                ['content' => '- Kỹ năng chơi'],
                            ],
                            'muc_tieu' => [],
                            'hoat_dong' => [],
                            'phuong_tien' => [],
                            'muc_tieu_du_phong' => [],
                        ],
                        [
                            'linh_vuc' => [
                                ['content' => '**Kỹ năng tiền đề**'],
                                ['content' => '- Luân phiên - chờ'],
                            ],
                            'muc_tieu' => [],
                            'hoat_dong' => [],
                            'phuong_tien' => [],
                            'muc_tieu_du_phong' => [],
                        ],
                        [
                            'linh_vuc' => [
                                ['content' => '**Vận động**'],
                                ['content' => '- Vận động tinh'],
                            ],
                            'muc_tieu' => [],
                            'hoat_dong' => [],
                            'phuong_tien' => [],
                            'muc_tieu_du_phong' => [],
                        ],
                        [
                            'linh_vuc' => [
                                ['content' => '**Vận động**'],
                                ['content' => '- Vận động thô'],
                            ],
                            'muc_tieu' => [],
                            'hoat_dong' => [],
                            'phuong_tien' => [],
                            'muc_tieu_du_phong' => [],
                        ],
                        [
                            'linh_vuc' => [
                                ['content' => '**Nhận thức**'],
                            ],
                            'muc_tieu' => [],
                            'hoat_dong' => [],
                            'phuong_tien' => [],
                            'muc_tieu_du_phong' => [],
                        ],
                        [
                            'linh_vuc' => [
                                ['content' => '**Kỹ năng đọc – viết**'],
                            ],
                            'muc_tieu' => [],
                            'hoat_dong' => [],
                            'phuong_tien' => [],
                            'muc_tieu_du_phong' => [],
                        ],
                        [
                            'linh_vuc' => [
                                ['content' => '**Biểu tượng toán**'],
                            ],
                            'muc_tieu' => [],
                            'hoat_dong' => [],
                            'phuong_tien' => [],
                            'muc_tieu_du_phong' => [],
                        ],
                        [
                            'linh_vuc' => [
                                ['content' => '**Ngôn ngữ và giao tiếp**'],
                                ['content' => '- Ngôn ngữ tiếp nhận'],
                            ],
                            'muc_tieu' => [],
                            'hoat_dong' => [],
                            'phuong_tien' => [],
                            'muc_tieu_du_phong' => [],
                        ],
                        [
                            'linh_vuc' => [
                                ['content' => '**Ngôn ngữ và giao tiếp**'],
                                ['content' => '- Ngôn ngữ nói'],
                            ],
                            'muc_tieu' => [],
                            'hoat_dong' => [],
                            'phuong_tien' => [],
                            'muc_tieu_du_phong' => [],
                        ],
                        [
                            'linh_vuc' => [
                                ['content' => '**Cá nhân và xã hội**'],
                            ],
                            'muc_tieu' => [],
                            'hoat_dong' => [],
                            'phuong_tien' => [],
                            'muc_tieu_du_phong' => [],
                        ],
                        [
                            'linh_vuc' => [
                                ['content' => '**Kỹ năng tự phục vụ**'],
                            ],
                            'muc_tieu' => [],
                            'hoat_dong' => [],
                            'phuong_tien' => [],
                            'muc_tieu_du_phong' => [],
                        ],
                        [
                            'linh_vuc' => [
                                ['content' => '**Cảm xúc**'],
                            ],
                            'muc_tieu' => [],
                            'hoat_dong' => [],
                            'phuong_tien' => [],
                            'muc_tieu_du_phong' => [],
                        ],
                    ])
                    ->schema([
                        Grid::make(5)
                            ->schema([
                                Repeater::make('linh_vuc')
                                    ->label(trans('packages.planning_evaluation::planning.fields.linh_vuc'))
                                    ->schema([
                                        MarkdownEditor::make('content')
                                            ->label(trans('packages.planning_evaluation::planning.fields.noi_dung'))
                                            ->toolbarButtons([
                                                'bold',
                                                'italic',
                                                'bulletList',
                                            ]),
                                    ])
                                    ->createItemButtonLabel(trans('packages.planning_evaluation::planning.actions.add_linh_vuc_item'))
                                    ->collapsible(),

                                Repeater::make('muc_tieu')
                                    ->label(trans('packages.planning_evaluation::planning.fields.muc_tieu'))
                                    ->schema([
                                        MarkdownEditor::make('content')
                                            ->label(trans('packages.planning_evaluation::planning.fields.noi_dung'))
                                            ->toolbarButtons([
                                                'bold',
                                                'italic',
                                                'bulletList',
                                            ]),
                                    ])
                                    ->createItemButtonLabel(trans('packages.planning_evaluation::planning.actions.add_muc_tieu'))
                                    ->collapsible(),

                                Repeater::make('hoat_dong')
                                    ->label(trans('packages.planning_evaluation::planning.fields.hoat_dong'))
                                    ->schema([
                                        MarkdownEditor::make('content')
                                            ->label(trans('packages.planning_evaluation::planning.fields.noi_dung'))
                                            ->toolbarButtons([
                                                'bold',
                                                'italic',
                                                'bulletList',
                                            ]),
                                    ])
                                    ->createItemButtonLabel(trans('packages.planning_evaluation::planning.actions.add_hoat_dong'))
                                    ->collapsible(),

                                Repeater::make('phuong_tien')
                                    ->label(trans('packages.planning_evaluation::planning.fields.phuong_tien'))
                                    ->schema([
                                        MarkdownEditor::make('content')
                                            ->label(trans('packages.planning_evaluation::planning.fields.noi_dung'))
                                            ->toolbarButtons([
                                                'bold',
                                                'italic',
                                                'bulletList',
                                            ]),
                                    ])
                                    ->createItemButtonLabel(trans('packages.planning_evaluation::planning.actions.add_phuong_tien'))
                                    ->collapsible(),

                                Repeater::make('muc_tieu_du_phong')
                                    ->label(trans('packages.planning_evaluation::planning.fields.muc_tieu_du_phong'))
                                    ->schema([
                                        MarkdownEditor::make('content')
                                            ->label(trans('packages.planning_evaluation::planning.fields.noi_dung'))
                                            ->toolbarButtons([
                                                'bold',
                                                'italic',
                                                'bulletList',
                                            ]),
                                    ])
                                    ->createItemButtonLabel(trans('packages.planning_evaluation::planning.actions.add_muc_tieu_du_phong'))
                                    ->collapsible(),
                            ]),
                    ])
                    ->createItemButtonLabel(trans('packages.planning_evaluation::planning.actions.add_row'))
                    ->collapsible()
                    ->columnSpanFull(),
            ]);
    }
}
