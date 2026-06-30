<?php

namespace Quochao56\Acl\Filament\Resources;

use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Schema as DbSchema;
use Quochao56\Acl\Filament\Resources\RoleResource\Pages;
use Quochao56\Acl\Filament\Resources\RoleResource\Schemas\RoleForm;
use Quochao56\Acl\Filament\Resources\RoleResource\Tables\RoleTable;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

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
        if (! DbSchema::hasTable('permissions')) {
            return;
        }

        $groups = config('permissions', []);
        $configHash = md5(serialize($groups));

        // Use cache to prevent running database queries on every boot request
        if (cache()->get('permissions_sync_hash') === $configHash) {
            return;
        }

        // Clear cached permissions to avoid state issues
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        $activePermissions = [];

        foreach ($groups as $groupKey => $groupConfig) {
            if (! isset($groupConfig['permissions'])) {
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

        cache()->forever('permissions_sync_hash', $configHash);
        app()[PermissionRegistrar::class]->forgetCachedPermissions();
    }

    public static function form(Schema $schema): Schema
    {
        return RoleForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return RoleTable::configure($table);
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
