<?php

namespace Quochao56\Student\Filament\Resources;

use Filament\Actions\BulkActionGroup as ActionsBulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Filament\Actions\BulkAction;
use App\Enum\BaseStatusEnum;
use Quochao56\Student\Filament\Resources\StudentResource\Pages\CreateStudent;
use Quochao56\Student\Filament\Resources\StudentResource\Pages\EditStudent;
use Quochao56\Student\Filament\Resources\StudentResource\Pages\ListStudents;
use Quochao56\Student\Models\Student;

class StudentResource extends Resource
{
    protected static ?string $model = Student::class;

    protected static ?int $navigationSort = 1;

    public static function getNavigationIcon(): string | \BackedEnum  | \Illuminate\Contracts\Support\Htmlable  | null
    {
        return 'heroicon-o-academic-cap';
    }

    public static function getNavigationLabel(): string
    {
        return trans('packages.student::student.navigation_label');
    }

    public static function getModelLabel(): string
    {
        return trans('packages.student::student.model_label');
    }

    public static function getPluralModelLabel(): string
    {
        return trans('packages.student::student.plural_model_label');
    }

    public static function getNavigationGroup(): ?string
    {
        return trans('packages.student::student.navigation_group');
    }

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            TextInput::make('student_code')
                ->label(trans('packages.student::student.fields.student_code'))
                ->maxLength(50)
                ->unique(table: 'students', column: 'student_code', ignoreRecord: true),
            TextInput::make('name')
                ->label(trans('packages.student::student.fields.name'))
                ->required()
                ->maxLength(255),

            TextInput::make('nickname')
                ->label(trans('packages.student::student.fields.nickname'))
                ->maxLength(255),
            Select::make('gender')
                ->label(trans('packages.student::student.fields.gender'))
                ->options([
                    'male'   => trans('packages.student::student.gender.male'),
                    'female' => trans('packages.student::student.gender.female'),
                    'other'  => trans('packages.student::student.gender.other')
                ]),
            DatePicker::make('dob')
                ->label(trans('packages.student::student.fields.dob'))
                ->native(false),
            TextInput::make('father_name')
                ->label(trans('packages.student::student.fields.father_name'))
                ->maxLength(255),

            TextInput::make('father_phone')
                ->label(trans('packages.student::student.fields.father_phone'))
                ->tel()
                ->maxLength(20),

            TextInput::make('mother_name')
                ->label(trans('packages.student::student.fields.mother_name'))
                ->maxLength(255),

            TextInput::make('mother_phone')
                ->label(trans('packages.student::student.fields.mother_phone'))
                ->tel()
                ->maxLength(20),

            Select::make('status')
                ->label(trans('packages.student::student.fields.status'))
                ->options([
                    'active'   => trans('packages.student::student.status.active'),
                    'inactive' => trans('packages.student::student.status.inactive')
                ])
                ->default('active')
                ->required(),
            FileUpload::make('avatar')
                ->label(trans('packages.student::student.fields.avatar'))
                ->image()
                ->directory('students')
                ->imageEditor()

        ]);
    }

    public static function getEloquentQuery(): \Illuminate\Database\Eloquent\Builder
    {
        $query = parent::getEloquentQuery();
        
        if (auth()->check() && auth()->user()->isSuperAdmin()) {
            return $query;
        }
        
        if (auth()->check()) {
            $employee = \Quochao56\Employee\Models\Employee::where('email', auth()->user()->email)->first();
            if ($employee) {
                return $query->whereHas('currentAssignment', function ($q) use ($employee) {
                    $q->where('employee_id', $employee->id);
                });
            }
        }
        
        return $query->whereRaw('1=0');
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                ImageColumn::make('avatar')
                    ->label(trans('packages.student::student.fields.avatar'))
                    ->circular(),

                TextColumn::make('name')
                    ->label(trans('packages.student::student.fields.name'))
                    ->searchable()
                    ->sortable(),
                TextColumn::make('gender')
                    ->label(trans('packages.student::student.fields.gender'))
                    ->formatStateUsing(fn(?string $state): string => trans('packages.core::core.gender.' . $state ?? 'male')),

                TextColumn::make('dob')
                    ->label(trans('packages.student::student.fields.dob'))
                    ->date('d/m/Y')
                    ->sortable(),
                TextColumn::make('nickname')
                    ->label(trans('packages.student::student.fields.nickname'))
                    ->searchable(),

                TextColumn::make('father_name')
                    ->label(trans('packages.student::student.fields.father_name'))
                    ->searchable(),

                TextColumn::make('father_phone')
                    ->label(trans('packages.student::student.fields.father_phone')),

                TextColumn::make('mother_name')
                    ->label(trans('packages.student::student.fields.mother_name'))
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('mother_phone')
                    ->label(trans('packages.student::student.fields.mother_phone'))
                    ->toggleable(isToggledHiddenByDefault: true),



                TextColumn::make('status')
                    ->label(trans('packages.student::student.fields.status'))
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        'active'   => 'success',
                        'inactive' => 'danger',
                        default    => 'gray',
                    })
                    ->formatStateUsing(fn(string $state): string => match ($state) {
                        'active'   => trans('packages.student::student.status.active'),
                        'inactive' => trans('packages.student::student.status.inactive'),
                        default    => $state,
                    })
            ])
            ->filters([
                SelectFilter::make('status')
                    ->label(trans('packages.student::student.fields.status'))
                    ->options([
                        'active'   => trans('packages.student::student.status.active'),
                        'inactive' => trans('packages.student::student.status.inactive')
                    ])
            ])
            ->actions([
                \Filament\Actions\Action::make('create_planning')
                    ->label(trans('packages.planning_evaluation::planning.actions.create_plan'))
                    ->icon('heroicon-o-document-plus')
                    ->color('success')
                    ->url(fn (Student $record) => \Quochao56\PlanningEvaluation\Filament\Resources\Plannings\PlanningResource::getUrl('create', [
                        'student_id' => $record->id
                    ])),
                EditAction::make(),
                DeleteAction::make()
            ])
            ->bulkActions([
                ActionsBulkActionGroup::make([
                    BulkAction::make('assign_teacher')
                        ->label(trans('packages.planning_evaluation::planning.assignment.assign_teacher_bulk'))
                        ->icon('heroicon-o-user-plus')
                        ->visible(fn () => auth()->check() && auth()->user()->can('assign_students'))
                        ->form([
                            Select::make('employee_id')
                                ->label(trans('packages.planning_evaluation::planning.assignment.teacher'))
                                ->options(\Quochao56\Employee\Models\Employee::query()->where('status', BaseStatusEnum::Active->value ?? BaseStatusEnum::Active)->pluck('name', 'id'))
                                ->required()
                                ->searchable(),
                            DatePicker::make('assigned_at')
                                ->label(trans('packages.planning_evaluation::planning.assignment.assign_date'))
                                ->default(now())
                                ->native(false)
                                ->displayFormat('d/m/Y')
                                ->required(),
                        ])
                        ->action(function (\Illuminate\Database\Eloquent\Collection $records, array $data): void {
                            foreach ($records as $student) {
                                // 1. Close current active assignment
                                $student->assignments()
                                    ->whereNull('unassigned_at')
                                    ->update(['unassigned_at' => $data['assigned_at']]);
                                    
                                // 2. Create new assignment
                                $student->assignments()->create([
                                    'employee_id' => $data['employee_id'],
                                    'assigned_at' => $data['assigned_at'],
                                ]);
                            }
                        })
                        ->deselectRecordsAfterCompletion(),
                    DeleteBulkAction::make()
                ])
            ]);
    }

    public static function getRelations(): array
    {
        $relations = [];
        
        if (auth()->check() && auth()->user()->can('assign_students')) {
            $relations[] = \Quochao56\PlanningEvaluation\Filament\Resources\StudentAssignmentRelationManager::class;
        }
        
        return $relations;
    }

    public static function getPages(): array
    {
        return [
            'index'  => ListStudents::route('/'),
            'create' => CreateStudent::route('/create'),
            'edit'   => EditStudent::route('/{record}/edit')
        ];
    }
}
