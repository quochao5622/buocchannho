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
        return trans('student::student.navigation_label');
    }

    public static function getModelLabel(): string
    {
        return trans('student::student.model_label');
    }

    public static function getPluralModelLabel(): string
    {
        return trans('student::student.plural_model_label');
    }

    public static function getNavigationGroup(): ?string
    {
        return trans('student::student.navigation_group');
    }

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            TextInput::make('student_code')
                ->label(trans('student::student.fields.student_code'))
                ->maxLength(50)
                ->unique(ignoreRecord: true),
            TextInput::make('name')
                ->label(trans('student::student.fields.name'))
                ->required()
                ->maxLength(255),

            TextInput::make('nickname')
                ->label(trans('student::student.fields.nickname'))
                ->maxLength(255),
            Select::make('gender')
                ->label(trans('student::student.fields.gender'))
                ->options([
                    'male'   => trans('student::student.gender.male'),
                    'female' => trans('student::student.gender.female'),
                    'other'  => trans('student::student.gender.other')
                ]),
            DatePicker::make('dob')
                ->label(trans('student::student.fields.dob'))
                ->native(false),
            TextInput::make('father_name')
                ->label(trans('student::student.fields.father_name'))
                ->maxLength(255),

            TextInput::make('father_phone')
                ->label(trans('student::student.fields.father_phone'))
                ->tel()
                ->maxLength(20),

            TextInput::make('mother_name')
                ->label(trans('student::student.fields.mother_name'))
                ->maxLength(255),

            TextInput::make('mother_phone')
                ->label(trans('student::student.fields.mother_phone'))
                ->tel()
                ->maxLength(20),

            Select::make('status')
                ->label(trans('student::student.fields.status'))
                ->options([
                    'active'   => trans('student::student.status.active'),
                    'inactive' => trans('student::student.status.inactive')
                ])
                ->default('active')
                ->required(),
            FileUpload::make('avatar')
                ->label(trans('student::student.fields.avatar'))
                ->image()
                ->directory('students')
                ->imageEditor()

        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                ImageColumn::make('avatar')
                    ->label(trans('student::student.fields.avatar'))
                    ->circular(),

                TextColumn::make('name')
                    ->label(trans('student::student.fields.name'))
                    ->searchable()
                    ->sortable(),

                TextColumn::make('nickname')
                    ->label(trans('student::student.fields.nickname'))
                    ->searchable(),

                TextColumn::make('father_name')
                    ->label(trans('student::student.fields.father_name'))
                    ->searchable(),

                TextColumn::make('father_phone')
                    ->label(trans('student::student.fields.father_phone')),

                TextColumn::make('mother_name')
                    ->label(trans('student::student.fields.mother_name'))
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('mother_phone')
                    ->label(trans('student::student.fields.mother_phone'))
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('dob')
                    ->label(trans('student::student.fields.dob'))
                    ->date('d/m/Y')
                    ->sortable(),

                TextColumn::make('status')
                    ->label(trans('student::student.fields.status'))
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        'active'   => 'success',
                        'inactive' => 'danger',
                        default    => 'gray',
                    })
                    ->formatStateUsing(fn(string $state): string => match ($state) {
                        'active'   => trans('student::student.status.active'),
                        'inactive' => trans('student::student.status.inactive'),
                        default    => $state,
                    })
            ])
            ->filters([
                SelectFilter::make('status')
                    ->label(trans('student::student.fields.status'))
                    ->options([
                        'active'   => trans('student::student.status.active'),
                        'inactive' => trans('student::student.status.inactive')
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
            'index'  => ListStudents::route('/'),
            'create' => CreateStudent::route('/create'),
            'edit'   => EditStudent::route('/{record}/edit')
        ];
    }

}
