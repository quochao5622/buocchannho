<?php

namespace Quochao56\Acl\Filament\Resources\RoleResource\Schemas;

use Filament\Forms\Components\CheckboxList;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
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

        // Mapping keys to friendly group tabs
        $groupMapping = [
            'students' => 'Học sinh & Giáo viên',
            'employees' => 'Học sinh & Giáo viên',

            'daily_logs' => 'Nhật ký & Trị liệu',
            'behavior_incidents' => 'Nhật ký & Trị liệu',

            'plannings' => 'Học tập',
            'evaluations' => 'Học tập',

            'equipments' => 'Học cụ',
            'equipment_categories' => 'Học cụ',
            'equipment_inventories' => 'Học cụ',

            'users' => 'Hệ thống & Cấu hình',
            'roles' => 'Hệ thống & Cấu hình',
            'logs' => 'Hệ thống & Cấu hình',
            'audits' => 'Hệ thống & Cấu hình',
            'activities' => 'Hệ thống & Cấu hình',
        ];

        // Define groups and order
        $tabGroups = [
            'Học sinh & Giáo viên' => [
                'icon' => 'heroicon-o-academic-cap',
                'sections' => [],
            ],
            'Nhật ký & Trị liệu' => [
                'icon' => 'heroicon-o-book-open',
                'sections' => [],
            ],
            'Học tập' => [
                'icon' => 'heroicon-o-document-text',
                'sections' => [],
            ],
            'Học cụ' => [
                'icon' => 'heroicon-o-briefcase',
                'sections' => [],
            ],
            'Hệ thống & Cấu hình' => [
                'icon' => 'heroicon-o-cog-6-tooth',
                'sections' => [],
            ],
            'Khác' => [
                'icon' => 'heroicon-o-ellipsis-horizontal',
                'sections' => [],
            ],
        ];

        // Generate sections
        foreach ($groups as $groupKey => $groupConfig) {
            if (! isset($groupConfig['permissions'])) {
                continue;
            }

            $options = $groupConfig['permissions'];
            $statePathName = 'permissions_group_'.$groupKey;
            $groupPermNames = array_map(fn ($action) => "{$groupKey}.{$action}", array_keys($options));

            $section = Section::make($groupConfig['label'])
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

            $tabName = $groupMapping[$groupKey] ?? 'Khác';
            $tabGroups[$tabName]['sections'][] = $section;
        }

        // Build tabs schema
        $tabs = [];
        foreach ($tabGroups as $tabLabel => $tabData) {
            if (empty($tabData['sections'])) {
                continue;
            }
            $tabs[] = Tab::make($tabLabel)
                ->icon($tabData['icon'])
                ->schema([
                    Grid::make(2)
                        ->schema($tabData['sections']),
                ]);
        }

        return $schema
            ->components([
                TextInput::make('name')
                    ->label(trans('acl::rbac.fields.name'))
                    ->required()
                    ->unique(ignoreRecord: true)
                    ->placeholder(trans('acl::rbac.fields.name_placeholder'))
                    ->columnSpanFull(),

                Tabs::make('Permissions')
                    ->tabs($tabs)
                    ->columnSpanFull(),
            ]);
    }
}
