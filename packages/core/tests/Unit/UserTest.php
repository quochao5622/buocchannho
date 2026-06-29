<?php

use Filament\Panel;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Quochao56\Core\Models\User;
use Quochao56\Core\Tests\TestCase;

uses(TestCase::class, RefreshDatabase::class);

it('determines if user is super admin', function () {
    $user = clone User::factory()->make(['is_super_admin' => true]);
    expect($user->isSuperAdmin())->toBeTrue();

    $regularUser = clone User::factory()->make(['is_super_admin' => false]);
    expect($regularUser->isSuperAdmin())->toBeFalse();
});

it('determines if user can access filament panel based on is_active', function () {
    $activeUser = clone User::factory()->make(['is_active' => true]);
    $panelMock = Mockery::mock(Panel::class);

    expect($activeUser->canAccessPanel($panelMock))->toBeTrue();

    $inactiveUser = clone User::factory()->make(['is_active' => false]);
    expect($inactiveUser->canAccessPanel($panelMock))->toBeFalse();
});
