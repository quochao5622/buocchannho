<?php

namespace Quochao56\PlanningEvaluation\Filament\Resources\Plannings\Tables;

use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Notifications\Notification;
use Filament\Actions\ActionGroup;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Enums\FiltersLayout;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table; // Thêm class Filter
use Illuminate\Database\Eloquent\Builder; // Thêm class DatePicker
use Illuminate\Support\Facades\Auth;
use Quochao56\Core\Enum\BaseStatusEnum;
use Quochao56\Employee\Models\Employee;
use Quochao56\PlanningEvaluation\Filament\Actions\ApproveAction;
use Quochao56\PlanningEvaluation\Filament\Resources\Evaluations\EvaluationResource;
use Quochao56\PlanningEvaluation\Models\Evaluation;
use Quochao56\PlanningEvaluation\Models\Planning;
use Quochao56\Student\Models\Student;

class PlanningsTable
{
    public static function configure(Table $table): Table
    {
        $currentEmployeeId = null;
        if (Auth::check()) {
            $currentEmployeeId = Employee::where('email', Auth::user()->email)->first()?->id;
        }

        return $table
            ->columns([
                TextColumn::make('name')->label(trans('packages.planning_evaluation::planning.fields.name'))->searchable(),
                TextColumn::make('employee.name')
                    ->label(trans('packages.planning_evaluation::planning.fields.employee'))
                    ->searchable()
                    ->formatStateUsing(fn($state) => $state ?: '-'),
                TextColumn::make('student.name')
                    ->label(trans('packages.planning_evaluation::planning.fields.student'))
                    ->searchable()
                    ->formatStateUsing(fn($state) => $state ?: '-'),
                TextColumn::make('start_date')->label(trans('packages.planning_evaluation::planning.fields.start_date'))->date('d/m/Y'),
                TextColumn::make('end_date')->label(trans('packages.planning_evaluation::planning.fields.end_date'))->date('d/m/Y'),
                TextColumn::make('status')->label(trans('packages.planning_evaluation::planning.fields.status'))
                    ->badge(),
                TextColumn::make('created_at')->label(trans('packages.planning_evaluation::planning.fields.created_at'))->dateTime(),
                TextColumn::make('updated_at')->label(trans('packages.planning_evaluation::planning.fields.updated_at'))->dateTime(),
            ])
            ->filters([
                SelectFilter::make('managing_teacher')
                    ->label(trans('packages.planning_evaluation::planning.tracker.managing_teacher'))
                    ->options(Employee::query()->where('status', BaseStatusEnum::Active->value ?? BaseStatusEnum::Active)->pluck('name', 'id'))
                    ->searchable()
                    ->preload()
                    ->query(function ($query, array $data) use ($currentEmployeeId) {
                        // Nếu là giáo viên đang đăng nhập, ép điều kiện lọc theo chính họ luôn
                        if ($currentEmployeeId) {
                            return $query->whereHas('student.currentAssignment', function ($q) use ($currentEmployeeId) {
                                $q->where('employee_id', $currentEmployeeId);
                            });
                        }

                        // Nếu là Admin, xử lý giá trị chọn từ bộ lọc như bình thường
                        if (empty($data['value'])) {
                            return;
                        }
                        $query->whereHas('student.currentAssignment', function ($q) use ($data) {
                            $q->where('employee_id', $data['value']);
                        });
                    })
                    // KHẮC PHỤC TẠI ĐÂY: Chỉ hiển thị bộ lọc này ngoài giao diện nếu KHÔNG PHẢI là giáo viên
                    ->visible(blank($currentEmployeeId)),
                // 2. Bộ lọc Từ ngày (Đã sửa: Bọc DatePicker vào trong Filter)
                Filter::make('created_from')
                    ->form([
                        DatePicker::make('created_from')
                            ->label('Từ ngày tạo')
                            ->native(false)
                            ->displayFormat('d/m/Y'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query->when(
                            $data['created_from'],
                            fn(Builder $query, $date): Builder => $query->whereDate('created_at', '>=', $date),
                        );
                    }),

                // 3. Bộ lọc Đến ngày (Đã sửa: Bọc DatePicker vào trong Filter)
                Filter::make('created_until')
                    ->form([
                        DatePicker::make('created_until')
                            ->label('Đến ngày tạo')
                            ->native(false)
                            ->displayFormat('d/m/Y'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query->when(
                            $data['created_until'],
                            fn(Builder $query, $date): Builder => $query->whereDate('created_at', '<=', $date),
                        );
                    }),
            ])
            ->filtersLayout(FiltersLayout::AboveContent)
            ->actions([
                Action::make('evaluate')
                    ->label(trans('packages.planning_evaluation::planning.tracker.evaluation'))
                    ->color('warning')
                    ->visible(fn(Planning $record): bool => (($record->status?->value ?? $record->status) === BaseStatusEnum::Published->value))
                    ->action(function (Planning $record) {
                        $evaluation = Evaluation::upsertFromPlanning($record);

                        return redirect(EvaluationResource::getUrl('edit', [
                            'planning' => $record,
                            'record' => $evaluation,
                        ]));
                    }),
                ApproveAction::make(),
                EditAction::make(),
                ActionGroup::make([
                    ViewAction::make()
                        ->modalWidth('90%'),
                    Action::make('clone')
                        ->label(trans('packages.planning_evaluation::planning.clone.label'))
                        ->icon('heroicon-o-document-duplicate')
                        ->color('info')
                        ->form([
                            Select::make('student_id')
                                ->label(trans('packages.planning_evaluation::planning.clone.student'))
                                ->options(Student::query()->pluck('name', 'id'))
                                ->required()
                                ->searchable(),
                            DatePicker::make('start_date')
                                ->label(trans('packages.planning_evaluation::planning.clone.start_date'))
                                ->native(false)
                                ->default(now())
                                ->displayFormat('d/m/Y')
                                ->required(),
                            DatePicker::make('end_date')
                                ->label(trans('packages.planning_evaluation::planning.clone.end_date'))
                                ->native(false)
                                ->default(now()->addMonths(3))
                                ->displayFormat('d/m/Y')
                                ->required(),
                        ])
                        ->action(function (Planning $record, array $data): void {
                            $cloned = $record->replicate();
                            $cloned->student_id = $data['student_id'];
                            $cloned->start_date = $data['start_date'];
                            $cloned->end_date = $data['end_date'];
                            $cloned->name = $record->name . trans('packages.planning_evaluation::planning.clone.suffix');

                            $newStudent = Student::find($data['student_id']);
                            $employeeId = null;
                            if (Auth::check()) {
                                $employeeId = Employee::where('email', Auth::user()->email)->first()?->id;
                            }
                            $cloned->employee_id = $newStudent?->currentAssignment?->employee_id
                                ?? $employeeId
                                ?? $record->employee_id;

                            $cloned->status = BaseStatusEnum::Draft;
                            $cloned->save();

                            Notification::make()
                                ->success()
                                ->title(trans('packages.planning_evaluation::planning.clone.success'))
                                ->send();
                        }),
                    DeleteAction::make(),
                ])
                    ->label('Thao tác')
                    ->icon('heroicon-m-chevron-down')
                    ->color('gray')
                    ->button(),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
