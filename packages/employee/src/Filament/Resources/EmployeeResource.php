<?php

namespace Quochao56\Employee\Filament\Resources;

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
use Quochao56\Employee\Filament\Resources\EmployeeResource\Pages\CreateEmployee;
use Quochao56\Employee\Filament\Resources\EmployeeResource\Pages\EditEmployee;
use Quochao56\Employee\Filament\Resources\EmployeeResource\Pages\ListEmployees;
use Quochao56\Employee\Models\Employee;
use App\Enum\BaseStatusEnum;

class EmployeeResource extends Resource
{
    protected static ?string $model = Employee::class;

    protected static ?int $navigationSort = 1;

    public static function getNavigationIcon(): string | \BackedEnum  | \Illuminate\Contracts\Support\Htmlable  | null
    {
        return 'heroicon-o-user-group';
    }

    public static function getNavigationLabel(): string
    {
        return trans('packages.employee::employee.navigation_label');
    }

    public static function getModelLabel(): string
    {
        return trans('packages.employee::employee.model_label');
    }

    public static function getPluralModelLabel(): string
    {
        return trans('packages.employee::employee.plural_model_label');
    }

    public static function getNavigationGroup(): ?string
    {
        return trans('packages.employee::employee.navigation_group');
    }

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            TextInput::make('employee_code')
                ->label(trans('packages.employee::employee.fields.employee_code'))
                ->maxLength(50)
                ->unique(table: 'employees', column: 'employee_code', ignoreRecord: true),

            TextInput::make('name')
                ->label(trans('packages.employee::employee.fields.name'))
                ->required()
                ->maxLength(255),

            TextInput::make('email')
                ->label(trans('packages.employee::employee.fields.email'))
                ->email()
                ->unique(table: 'employees', column: 'email', ignoreRecord: true)
                ->maxLength(255),

            Select::make('gender')
                ->label(trans('packages.employee::employee.fields.gender'))
                ->options([
                    'male' => trans('packages.employee::employee.gender.male'),
                    'female' => trans('packages.employee::employee.gender.female'),
                    'other' => trans('packages.employee::employee.gender.other'),
                ]),

            DatePicker::make('dob')
                ->label(trans('packages.employee::employee.fields.dob'))
                ->native(false),

            TextInput::make('phone')
                ->label(trans('packages.employee::employee.fields.phone'))
                ->tel()
                ->maxLength(20),

            TextInput::make('address')
                ->label(trans('packages.employee::employee.fields.address'))
                ->maxLength(255),

            TextInput::make('position')
                ->label(trans('packages.employee::employee.fields.position'))
                ->maxLength(255),

            Select::make('employment_type')
                ->label(trans('packages.employee::employee.fields.employment_type'))
                ->options([
                    'full-time' => trans('packages.employee::employee.employment_type.full_time'),
                    'part-time' => trans('packages.employee::employee.employment_type.part_time'),
                    'intern' => trans('packages.employee::employee.employment_type.intern'),
                    'contract' => trans('packages.employee::employee.employment_type.contract'),
                ]),

            DatePicker::make('hired_at')
                ->label(trans('packages.employee::employee.fields.hired_at'))
                ->native(false),

            DatePicker::make('probation_end_at')
                ->label(trans('packages.employee::employee.fields.probation_end_at'))
                ->native(false),

            FileUpload::make('avatar')
                ->label(trans('packages.employee::employee.fields.avatar'))
                ->image()
                ->directory('employees')
                ->imageEditor(),


            Select::make('status')
                ->label(trans('packages.employee::employee.fields.status'))
                ->options([
                    BaseStatusEnum::Active->value   => BaseStatusEnum::Active->getLabel(),
                    BaseStatusEnum::Inactive->value => BaseStatusEnum::Inactive->getLabel(),
                ])
                ->default(BaseStatusEnum::Active->value)
                ->required()
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                ImageColumn::make('avatar')
                    ->label(trans('packages.employee::employee.fields.avatar'))
                    ->circular(),
                TextColumn::make('employee_code')
                    ->label(trans('packages.employee::employee.fields.employee_code'))
                    ->searchable()
                    ->sortable(),

                TextColumn::make('name')
                    ->label(trans('packages.employee::employee.fields.name'))
                    ->searchable()
                    ->sortable(),

                TextColumn::make('email')
                    ->label(trans('packages.employee::employee.fields.email'))
                    ->searchable(),

                TextColumn::make('gender')
                    ->label(trans('packages.employee::employee.fields.gender'))
                    ->formatStateUsing(fn(?string $state): string => trans('packages.core::core.gender.' . $state ?? 'male')),

                TextColumn::make('phone')
                    ->label(trans('packages.employee::employee.fields.phone')),


                TextColumn::make('position')
                    ->label(trans('packages.employee::employee.fields.position'))
                    ->searchable(),

                TextColumn::make('employment_type')
                    ->label(trans('packages.employee::employee.fields.employment_type'))
                    ->badge()
                    ->color(fn(?string $state): string => match ($state) {
                        'full-time' => 'success',
                        'part-time' => 'warning',
                        'intern' => 'info',
                        'contract' => 'gray',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn(?string $state): string => match ($state) {
                        'full-time' => trans('packages.employee::employee.employment_type.full_time'),
                        'part-time' => trans('packages.employee::employee.employment_type.part_time'),
                        'intern' => trans('packages.employee::employee.employment_type.intern'),
                        'contract' => trans('packages.employee::employee.employment_type.contract'),
                        default => $state ?? '-',
                    }),

                TextColumn::make('students_count')
                    ->counts('students')
                    ->label(trans('packages.employee::employee.fields.students_count'))
                    ->badge()
                    ->color('info')
                    ->sortable(),

                TextColumn::make('hired_at')
                    ->label(trans('packages.employee::employee.fields.hired_at'))
                    ->date('d/m/Y')
                    ->sortable(),


                TextColumn::make('status')
                    ->label(trans('packages.employee::employee.fields.status'))
                    ->badge()
                    ->color(fn ($state): string => $state instanceof BaseStatusEnum ? $state->getColor() : ($state === 'active' ? 'success' : 'danger')),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->label(trans('packages.employee::employee.fields.status'))
                    ->options([
                        BaseStatusEnum::Active->value   => BaseStatusEnum::Active->getLabel(),
                        BaseStatusEnum::Inactive->value => BaseStatusEnum::Inactive->getLabel(),
                    ]),

                SelectFilter::make('employment_type')
                    ->label(trans('packages.employee::employee.fields.employment_type'))
                    ->options([
                        'full-time' => trans('packages.employee::employee.employment_type.full_time'),
                        'part-time' => trans('packages.employee::employee.employment_type.part_time'),
                        'intern' => trans('packages.employee::employee.employment_type.intern'),
                        'contract' => trans('packages.employee::employee.employment_type.contract'),
                    ])
            ])
            ->actions([
                EditAction::make(),
                DeleteAction::make()
            ])
            ->bulkActions([
                ActionsBulkActionGroup::make([
                    DeleteBulkAction::make()
                ])
            ]);
    }

    public static function getRelations(): array
    {
        return [
            \Quochao56\PlanningEvaluation\Filament\Resources\StudentsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index'  => ListEmployees::route('/'),
            'create' => CreateEmployee::route('/create'),
            'edit'   => EditEmployee::route('/{record}/edit')
        ];
    }
}
