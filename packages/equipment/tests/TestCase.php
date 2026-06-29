<?php

namespace Quochao56\Equipment\Tests;

use Orchestra\Testbench\TestCase as Orchestra;
use Quochao56\Core\CoreServiceProvider;
use Quochao56\Equipment\EquipmentServiceProvider;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionServiceProvider;

class TestCase extends Orchestra
{
    protected function getPackageProviders($app)
    {
        return [
            PermissionServiceProvider::class,
            CoreServiceProvider::class,
            EquipmentServiceProvider::class,
        ];
    }

    protected function getEnvironmentSetUp($app)
    {
        $app['config']->set('database.default', 'testing');
        $app['config']->set('database.connections.testing', [
            'driver' => 'sqlite',
            'database' => ':memory:',
            'prefix' => '',
        ]);

        $app['config']->set('permission.models.permission', Permission::class);
        $app['config']->set('permission.models.role', Role::class);
        $app['config']->set('permission.table_names', [
            'roles' => 'roles',
            'permissions' => 'permissions',
            'model_has_permissions' => 'model_has_permissions',
            'model_has_roles' => 'model_has_roles',
            'role_has_permissions' => 'role_has_permissions',
        ]);
        $app['config']->set('permission.column_names', [
            'role_pivot_key' => null,
            'permission_pivot_key' => null,
            'model_morph_key' => 'model_id',
            'team_foreign_key' => 'team_id',
        ]);
        $app['config']->set('permission.teams', false);
    }

    protected function defineDatabaseMigrations()
    {
        $this->loadMigrationsFrom(__DIR__.'/../../../database/migrations');
    }
}
