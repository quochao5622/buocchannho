<?php

namespace Quochao56\Acl\Filament\Resources\RoleResource\Pages;

use Filament\Resources\Pages\CreateRecord;
use Quochao56\Acl\Filament\Resources\RoleResource;

class CreateRole extends CreateRecord
{
    protected static string $resource = RoleResource::class;

    protected function afterCreate(): void
    {
        $allPermissions = [];
        $formState = $this->data;
        foreach ($formState as $key => $values) {
            if (str_starts_with($key, 'permissions_group_') && is_array($values)) {
                $groupName = substr($key, strlen('permissions_group_'));
                foreach ($values as $action) {
                    $allPermissions[] = "{$groupName}.{$action}";
                }
            }
        }

        $this->record->syncPermissions($allPermissions);
    }
}
