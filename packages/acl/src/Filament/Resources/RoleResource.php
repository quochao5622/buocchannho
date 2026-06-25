<?php

namespace Quochao56\Acl\Filament\Resources;

use Quochao56\Acl\Filament\Resources\RoleResource\Pages;
use Filament\Schemas\Components\Section;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\CheckboxList;
use Filament\Schemas\Schema;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Actions\EditAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\BulkActionGroup;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Illuminate\Database\Eloquent\Builder;

class RoleResource extends Resource
{
    protected static ?string $model = Role::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-shield-check';

    public static function getNavigationLabel(): string
    {
        return trans('acl::rbac.navigation_label');
    }

    public static function getModelLabel(): string
    {
        return trans('acl::rbac.model_label');
    }

    public static function getPluralModelLabel(): string
    {
        return trans('acl::rbac.plural_model_label');
    }

    public static function getNavigationGroup(): ?string
    {
        return trans('filament-users::user.group');
    }

    public static function getEloquentQuery(): Builder
    {
        self::syncPermissionsToDatabase();
        return parent::getEloquentQuery();
    }

    public static function syncPermissionsToDatabase(): void
    {
        // Clear cached permissions to avoid state issues
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        $categories = config('permissions.categories', []);
        foreach ($categories as $group => $permissions) {
            foreach ($permissions as $name => $labelKey) {
                Permission::findOrCreate($name, 'web');
            }
        }
        
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();
    }

    public static function form(Schema $schema): Schema
    {
        // Sync config permissions to DB before rendering the form
        self::syncPermissionsToDatabase();

        $categories = config('permissions.categories', []);
        $sections = [];

        foreach ($categories as $group => $permissions) {
            $translatedOptions = [];
            foreach ($permissions as $name => $labelKey) {
                $translatedOptions[$name] = trans($labelKey);
            }

            $sections[] = Section::make(trans($group))
                ->schema([
                    CheckboxList::make('permissions')
                        ->relationship('permissions', 'name')
                        ->options($translatedOptions)
                        ->label(trans('acl::rbac.fields.permissions'))
                        ->columns(2),
                ])
                ->collapsible();
        }

        return $schema
            ->components(array_merge([
                TextInput::make('name')
                    ->label(trans('acl::rbac.fields.name'))
                    ->required()
                    ->unique(ignoreRecord: true)
                    ->placeholder(trans('acl::rbac.fields.name_placeholder')),
            ], $sections));
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label(trans('acl::rbac.fields.name'))
                    ->searchable()
                    ->sortable(),
                TextColumn::make('permissions_count')
                    ->label(trans('acl::rbac.fields.permissions_count'))
                    ->counts('permissions'),
            ])
            ->filters([
                //
            ])
            ->actions([
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListRoles::route('/'),
            'create' => Pages\CreateRole::route('/create'),
            'edit' => Pages\EditRole::route('/{record}/edit'),
        ];
    }
}
