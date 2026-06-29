<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Gate;
use OwenIt\Auditing\Models\Audit;
use Quochao56\Acl\Tests\TestCase;
use Quochao56\Core\Models\User;
use Spatie\Permission\Models\Permission;

uses(TestCase::class, RefreshDatabase::class);

beforeEach(function () {
    // Create necessary permissions for audits
    Permission::findOrCreate('audits.index', 'web');
    Permission::findOrCreate('audits.restore', 'web');
});

it('allows superadmin to view and restore audits', function () {
    $superadmin = clone User::factory()->create(['is_super_admin' => true]);

    expect(Gate::forUser($superadmin)->allows('viewAny', Audit::class))->toBeTrue();
    expect(Gate::forUser($superadmin)->allows('view', new Audit))->toBeTrue();
    expect(Gate::forUser($superadmin)->allows('restore', new Audit))->toBeTrue();
});

it('allows user with permissions to view and restore audits', function () {
    $user = clone User::factory()->create(['is_super_admin' => false]);
    $user->givePermissionTo('audits.index');
    $user->givePermissionTo('audits.restore');

    expect(Gate::forUser($user)->allows('viewAny', Audit::class))->toBeTrue();
    expect(Gate::forUser($user)->allows('view', new Audit))->toBeTrue();
    expect(Gate::forUser($user)->allows('restore', new Audit))->toBeTrue();
});

it('denies user without permissions to view and restore audits', function () {
    $user = clone User::factory()->create(['is_super_admin' => false]);

    expect(Gate::forUser($user)->allows('viewAny', Audit::class))->toBeFalse();
    expect(Gate::forUser($user)->allows('view', new Audit))->toBeFalse();
    expect(Gate::forUser($user)->allows('restore', new Audit))->toBeFalse();
});
