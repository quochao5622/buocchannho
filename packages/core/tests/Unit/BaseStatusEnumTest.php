<?php

use Quochao56\Core\Enum\BaseStatusEnum;
use Quochao56\Core\Tests\TestCase;

uses(TestCase::class);

it('returns correct label for enum values', function () {
    expect(BaseStatusEnum::Active->getLabel())->toBe('status.active');
    expect(BaseStatusEnum::Inactive->getLabel())->toBe('status.inactive');
    expect(BaseStatusEnum::Draft->getLabel())->toBe('status.draft');
    expect(BaseStatusEnum::Published->getLabel())->toBe('status.published');
    expect(BaseStatusEnum::Archived->getLabel())->toBe('status.archived');
});

it('returns correct color for enum values', function () {
    expect(BaseStatusEnum::Active->getColor())->toBe('success');
    expect(BaseStatusEnum::Inactive->getColor())->toBe('danger');
    expect(BaseStatusEnum::Draft->getColor())->toBe('gray');
    expect(BaseStatusEnum::Published->getColor())->toBe('success');
    expect(BaseStatusEnum::Archived->getColor())->toBe('gray');
});
