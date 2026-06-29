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
    protected static ?int $navigationSort = 2;


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

        $groups = config('permissions', []);
        $activePermissions = [];

        foreach ($groups as $groupKey => $groupConfig) {
            if (!isset($groupConfig['permissions'])) {
                continue;
            }
            foreach (array_keys($groupConfig['permissions']) as $action) {
                $permName = "{$groupKey}.{$action}";
                Permission::findOrCreate($permName, 'web');
                $activePermissions[] = $permName;
            }
        }

        // Clean up any stale permissions not in the config
        Permission::where('guard_name', 'web')
            ->whereNotIn('name', $activePermissions)
            ->delete();

        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();
    }

    public static function form(Schema $schema): Schema
    {
        // Sync config permissions to DB before rendering the form
        self::syncPermissionsToDatabase();

        $groups = config('permissions', []);
        $sections = [];

        foreach ($groups as $groupKey => $groupConfig) {
            if (!isset($groupConfig['permissions'])) {
                continue;
            }

            $options = $groupConfig['permissions'];
            $statePathName = 'permissions_group_' . $groupKey;
            $groupPermNames = array_map(fn($action) => "{$groupKey}.{$action}", array_keys($options));

            $sections[] = Section::make($groupConfig['label'])
                ->schema([
                    CheckboxList::make($statePathName)
                        ->options($options)
                        ->hiddenLabel()
                        ->columns(2)
                        ->dehydrated(false)
                        ->bulkToggleable()
                        ->afterStateHydrated(function ($component, $state, ?\Illuminate\Database\Eloquent\Model $record) use ($groupKey, $groupPermNames) {
                            if (! $record) {
                                $component->state([]);
                                return;
                            }
                            $selected = $record->permissions->pluck('name')->toArray();
                            $groupSelected = array_intersect($selected, $groupPermNames);
                            $actions = array_map(fn($perm) => substr($perm, strlen($groupKey) + 1), $groupSelected);
                            $component->state(array_values($actions));
                        }),
                ])
                ->collapsible();
        }

        return $schema
            ->components(array_merge([
                TextInput::make('name')
                    ->label(trans('acl::rbac.fields.name'))
                    ->required()
                    ->unique(ignoreRecord: true)
                    ->placeholder(trans('acl::rbac.fields.name_placeholder'))
                    ->columnSpanFull(),
            ], $sections))
            ->columns(2);
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
