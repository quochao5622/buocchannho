<?php

namespace Quochao56\Acl\Filament\Resources\RoleResource\Schemas;

use Filament\Forms\Components\CheckboxList;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Database\Eloquent\Model;
use Quochao56\Acl\Filament\Resources\RoleResource;

class RoleForm
{
    public static function configure(Schema $schema): Schema
    {
        // Sync config permissions to DB before rendering the form
        RoleResource::syncPermissionsToDatabase();

        $groups = config('permissions', []);
        $sections = [];

        foreach ($groups as $groupKey => $groupConfig) {
            if (! isset($groupConfig['permissions'])) {
                continue;
            }

            $options = $groupConfig['permissions'];
            $statePathName = 'permissions_group_'.$groupKey;
            $groupPermNames = array_map(fn ($action) => "{$groupKey}.{$action}", array_keys($options));

            $sections[] = Section::make($groupConfig['label'])
                ->schema([
                    CheckboxList::make($statePathName)
                        ->options($options)
                        ->hiddenLabel()
                        ->columns(2)
                        ->dehydrated(false)
                        ->bulkToggleable()
                        ->afterStateHydrated(function ($component, $state, ?Model $record) use ($groupKey, $groupPermNames) {
                            if (! $record) {
                                $component->state([]);

                                return;
                            }
                            $selected = $record->permissions->pluck('name')->toArray();
                            $groupSelected = array_intersect($selected, $groupPermNames);
                            $actions = array_map(fn ($perm) => substr($perm, strlen($groupKey) + 1), $groupSelected);
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
}
