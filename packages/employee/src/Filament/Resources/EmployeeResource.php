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
        return trans('employee::employee.navigation_label');
    }

    public static function getModelLabel(): string
    {
        return trans('employee::employee.model_label');
    }

    public static function getPluralModelLabel(): string
    {
        return trans('employee::employee.plural_model_label');
    }

    public static function getNavigationGroup(): ?string
    {
        return trans('employee::employee.navigation_group');
    }

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            TextInput::make('employee_code')
                ->label(trans('employee::employee.fields.employee_code'))
                ->maxLength(50)
                ->unique(ignoreRecord: true),

            TextInput::make('name')
                ->label(trans('employee::employee.fields.name'))
                ->required()
                ->maxLength(255),

            TextInput::make('email')
                ->label(trans('employee::employee.fields.email'))
                ->email()
                ->unique(ignoreRecord: true)
                ->maxLength(255),
            Select::make('gender')
                ->label(trans('employee::employee.fields.gender'))
                ->options([
                    'male' => trans('employee::employee.gender.male'),
                    'female' => trans('employee::employee.gender.female'),
                    'other' => trans('employee::employee.gender.other'),
                ]),

            DatePicker::make('dob')
                ->label(trans('employee::employee.fields.dob'))
                ->native(false),

            TextInput::make('phone')
                ->label(trans('employee::employee.fields.phone'))
                ->tel()
                ->maxLength(20),

            TextInput::make('address')
                ->label(trans('employee::employee.fields.address'))
                ->maxLength(255),

            TextInput::make('position')
                ->label(trans('employee::employee.fields.position'))
                ->maxLength(255),

            Select::make('employment_type')
                ->label(trans('employee::employee.fields.employment_type'))
                ->options([
                    'full-time' => trans('employee::employee.employment_type.full_time'),
                    'part-time' => trans('employee::employee.employment_type.part_time'),
                    'intern' => trans('employee::employee.employment_type.intern'),
                    'contract' => trans('employee::employee.employment_type.contract'),
                ]),

            DatePicker::make('hired_at')
                ->label(trans('employee::employee.fields.hired_at'))
                ->native(false),

            DatePicker::make('probation_end_at')
                ->label(trans('employee::employee.fields.probation_end_at'))
                ->native(false),

            FileUpload::make('avatar')
                ->label(trans('employee::employee.fields.avatar'))
                ->image()
                ->directory('employees')
                ->imageEditor(),


            Select::make('status')
                ->label(trans('employee::employee.fields.status'))
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
                    ->label(trans('employee::employee.fields.avatar'))
                    ->circular(),
                TextColumn::make('employee_code')
                    ->label(trans('employee::employee.fields.employee_code'))
                    ->searchable()
                    ->sortable(),

                TextColumn::make('name')
                    ->label(trans('employee::employee.fields.name'))
                    ->searchable()
                    ->sortable(),

                TextColumn::make('email')
                    ->label(trans('employee::employee.fields.email'))
                    ->searchable(),

                TextColumn::make('phone')
                    ->label(trans('employee::employee.fields.phone')),

                TextColumn::make('address')
                    ->label(trans('employee::employee.fields.address')),


                TextColumn::make('position')
                    ->label(trans('employee::employee.fields.position'))
                    ->searchable(),

                TextColumn::make('employment_type')
                    ->label(trans('employee::employee.fields.employment_type'))
                    ->badge()
                    ->formatStateUsing(fn (?string $state): string => match ($state) {
                        'full-time' => trans('employee::employee.employment_type.full_time'),
                        'part-time' => trans('employee::employee.employment_type.part_time'),
                        'intern' => trans('employee::employee.employment_type.intern'),
                        'contract' => trans('employee::employee.employment_type.contract'),
                        default => $state ?? '-',
                    }),

                TextColumn::make('hired_at')
                    ->label(trans('employee::employee.fields.hired_at'))
                    ->date('d/m/Y')
                    ->sortable(),

                TextColumn::make('probation_end_at')
                    ->label(trans('employee::employee.fields.probation_end_at'))
                    ->date('d/m/Y')
                    ->sortable(),

                TextColumn::make('gender')
                    ->label(trans('employee::employee.fields.gender'))
                    ->formatStateUsing(fn (?string $state): string => match ($state) {
                        'male' => trans('employee::employee.gender.male'),
                        'female' => trans('employee::employee.gender.female'),
                        'other' => trans('employee::employee.gender.other'),
                        default => $state ?? '-',
                    }),

                TextColumn::make('status')
                    ->label(trans('employee::employee.fields.status'))
                    ->badge()
            ])
            ->filters([
                SelectFilter::make('status')
                    ->label(trans('employee::employee.fields.status'))
                    ->options([
                        BaseStatusEnum::Active->value   => BaseStatusEnum::Active->getLabel(),
                        BaseStatusEnum::Inactive->value => BaseStatusEnum::Inactive->getLabel(),
                    ]),

                SelectFilter::make('employment_type')
                    ->label(trans('employee::employee.fields.employment_type'))
                    ->options([
                        'full-time' => trans('employee::employee.employment_type.full_time'),
                        'part-time' => trans('employee::employee.employment_type.part_time'),
                        'intern' => trans('employee::employee.employment_type.intern'),
                        'contract' => trans('employee::employee.employment_type.contract'),
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

    public static function getPages(): array
    {
        return [
            'index'  => ListEmployees::route('/'),
            'create' => CreateEmployee::route('/create'),
            'edit'   => EditEmployee::route('/{record}/edit')
        ];
    }
}
