<?php

namespace Quochao56\Acl\Filament\Resources\RoleResource\Pages;

use Quochao56\Acl\Filament\Resources\RoleResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Quochao56\Core\Traits\HasAutoSave;

class EditRole extends EditRecord
{
    use HasAutoSave;

    protected static string $resource = RoleResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }

    protected function afterSave(): void
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

        $currentPermissions = $this->record->permissions->pluck('name')->toArray();
        sort($allPermissions);
        sort($currentPermissions);

        if ($allPermissions !== $currentPermissions) {
            $this->record->syncPermissions($allPermissions);
        }
    }
}
