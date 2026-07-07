<?php

namespace App\Filament\Pages;

use DamodarBhattarai\FilamentSettings\Filament\Pages\ManageSettings as BaseManageSettings;
use DamodarBhattarai\Settings\Models\Setting;
use Filament\Actions\Action as FormAction;
use Filament\Actions\Action as PageAction;
use Filament\Forms\Components\Checkbox;
use Filament\Forms\Components\ColorPicker;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\TimePicker;
use Filament\Forms\Components\Toggle;
use Filament\Notifications\Notification;
use Illuminate\Support\Str;
use Quochao56\Core\Traits\HasNotifications;

class CustomManageSettings extends BaseManageSettings
{
    use HasNotifications;

    /**
     * Save all settings from the form to the database.
     * Overridden to translate notification text and dispatch notificationsSent.
     */
    public function save(): void
    {
        $formData = $this->form->getState();
        $settings = $formData['settings'] ?? [];

        foreach ($settings as $key => $value) {
            $settingModel = Setting::where('key', $key)->first();

            if (! $settingModel) {
                continue;
            }

            if (in_array($settingModel->type, ['image', 'file'])) {
                $storedValue = is_array($value) ? (reset($value) ?: '') : ($value ?? '');
            } elseif (in_array($settingModel->type, ['switch', 'checkbox'])) {
                $storedValue = $value ? true : false;
            } else {
                $storedValue = $value ?? '';
            }

            $settingModel->update([
                'value' => json_encode($storedValue),
            ]);
        }

        $this->clearSettingsCache();

        Notification::make()
            ->title('Đã lưu cấu hình')
            ->body('Toàn bộ cấu hình hệ thống đã được lưu lại thành công.')
            ->success()
            ->send();

        $this->dispatch('notificationsSent');
    }

    protected function getHeaderActions(): array
    {
        return [
            // Toggle Modify Mode button
            PageAction::make('toggleModifyMode')
                ->label(fn () => $this->modifyMode ? 'Exit Modify Mode' : 'Modify Fields')
                ->icon(fn () => $this->modifyMode ? 'heroicon-o-lock-closed' : 'heroicon-o-pencil-square')
                ->color(fn () => $this->modifyMode ? 'warning' : 'gray')
                ->action(function () {
                    $this->modifyMode = ! $this->modifyMode;
                    session()->put('manage_settings_modify_mode', $this->modifyMode);
                })
                ->visible(fn () => $this->canModifyFields()),

            // Add New Setting (only in modify mode)
            PageAction::make('addSetting')
                ->label('Add Setting')
                ->icon('heroicon-o-plus-circle')
                ->color('success')
                ->form([
                    TextInput::make('key')
                        ->label('Setting Key')
                        ->required()
                        ->unique('settings', 'key')
                        ->alphaDash()
                        ->helperText('Unique key used to retrieve this setting (e.g., site_name)')
                        ->maxLength(255),

                    TextInput::make('label')
                        ->label('Display Label')
                        ->required()
                        ->maxLength(255),

                    Select::make('type')
                        ->label('Field Type')
                        ->required()
                        ->options([
                            'text' => 'Text Input',
                            'textarea' => 'Textarea',
                            'image' => 'Image Upload',
                            'file' => 'File Upload',
                            'color' => 'Color Picker',
                            'switch' => 'Switch (Toggle)',
                            'checkbox' => 'Checkbox',
                            'date' => 'Date Picker',
                            'time' => 'Time Picker',
                            'datetime' => 'Datetime Picker',
                        ])
                        ->default('text'),

                    Select::make('group')
                        ->label('Tab / Group')
                        ->required()
                        ->options(fn () => $this->getGroupOptions())
                        ->default('general'),
                ])
                ->action(function (array $data): void {
                    $maxOrder = Setting::where('group', $data['group'])->max('tab_order') ?? 0;

                    Setting::create([
                        'key' => $data['key'],
                        'label' => $data['label'],
                        'type' => $data['type'],
                        'group' => $data['group'],
                        'value' => json_encode($this->getDefaultValueForType($data['type'])),
                        'tab_order' => $maxOrder + 1,
                    ]);

                    $this->clearSettingsCache();
                    $this->fillFormFromDatabase();

                    Notification::make()
                        ->title('Setting created')
                        ->body("'{$data['label']}' has been created successfully.")
                        ->success()
                        ->send();

                    $this->dispatch('notificationsSent');

                    $this->refreshSchema();
                })
                ->visible(fn (): bool => $this->modifyMode && $this->canModifyFields()),
        ];
    }

    protected function buildFieldForSetting(Setting $setting)
    {
        $fieldName = "settings.{$setting->key}";
        $label = $setting->label ?: Str::title(str_replace('_', ' ', $setting->key));

        // Use custom picker fields for datetime, time, and date
        if (in_array($setting->type, ['datetime', 'time', 'date'])) {
            $field = match ($setting->type) {
                'datetime' => DateTimePicker::make($fieldName)
                    ->label($label)
                    ->native(false)
                    ->displayFormat('d/m/Y H:i:s'),

                'time' => TimePicker::make($fieldName)
                    ->label($label)
                    ->native(false)
                    ->displayFormat('H:i'),

                'date' => DatePicker::make($fieldName)
                    ->label($label)
                    ->native(false)
                    ->displayFormat('d/m/Y'),
            };
        } else {
            // Otherwise fallback to parent mapping
            $field = match ($setting->type) {
                'textarea' => Textarea::make($fieldName)
                    ->label($label)
                    ->rows(4)
                    ->autosize()
                    ->columnSpanFull(),

                'image' => FileUpload::make($fieldName)
                    ->label($label)
                    ->image()
                    ->imagePreviewHeight('100')
                    ->disk(config('filament-settings.disk', 'public'))
                    ->directory('settings')
                    ->visibility('public')
                    ->columnSpanFull(),

                'file' => FileUpload::make($fieldName)
                    ->label($label)
                    ->disk(config('filament-settings.disk', 'public'))
                    ->directory('settings')
                    ->visibility('public')
                    ->columnSpanFull(),

                'color' => ColorPicker::make($fieldName)
                    ->label($label),

                'switch' => Toggle::make($fieldName)
                    ->label($label)
                    ->inline(false),

                'checkbox' => Checkbox::make($fieldName)
                    ->label($label),

                default => TextInput::make($fieldName)
                    ->label($label),
            };
        }

        // Add hint actions chain identical to parent
        // ── Modify Mode: hint action to Move to another tab ──
        $field = $field->hintAction(
            FormAction::make("move_{$setting->key}")
                ->icon('heroicon-m-arrows-right-left')
                ->tooltip('Move to another tab')
                ->visible(fn (): bool => $this->modifyMode && $this->canModifyFields())
                ->form([
                    Select::make('target_group')
                        ->label('Move to Tab')
                        ->options(fn () => $this->getGroupOptions())
                        ->default($setting->group)
                        ->required(),
                ])
                ->action(function (array $data) use ($setting): void {
                    if ($data['target_group'] === $setting->group) {
                        return;
                    }

                    $maxOrder = Setting::where('group', $data['target_group'])->max('tab_order') ?? 0;

                    $setting->update([
                        'group' => $data['target_group'],
                        'tab_order' => $maxOrder + 1,
                    ]);

                    $this->clearSettingsCache();
                    $this->fillFormFromDatabase();

                    Notification::make()
                        ->title('Setting moved')
                        ->body("'{$setting->label}' moved to '{$this->getGroupLabel($data['target_group'])}'.")
                        ->success()
                        ->send();

                    $this->dispatch('notificationsSent');

                    $this->refreshSchema();
                }),
        );

        // ── Modify Mode: hint action to Edit Label ──
        $field = $field->hintAction(
            FormAction::make("relabel_{$setting->key}")
                ->icon('heroicon-m-pencil')
                ->tooltip('Edit label')
                ->visible(fn (): bool => $this->modifyMode && $this->canModifyFields())
                ->form([
                    TextInput::make('new_label')
                        ->label('New Label')
                        ->default($setting->label ?: Str::title(str_replace('_', ' ', $setting->key)))
                        ->required()
                        ->maxLength(255),
                ])
                ->action(function (array $data) use ($setting): void {
                    $setting->update(['label' => $data['new_label']]);

                    $this->clearSettingsCache();
                    $this->fillFormFromDatabase();

                    Notification::make()
                        ->title('Label updated')
                        ->success()
                        ->send();

                    $this->dispatch('notificationsSent');

                    $this->refreshSchema();
                }),
        );

        // ── Modify Mode: hint action to Delete ──
        $field = $field->hintAction(
            FormAction::make("delete_{$setting->key}")
                ->icon('heroicon-m-trash')
                ->tooltip('Delete setting')
                ->color('danger')
                ->visible(fn (): bool => $this->modifyMode && $this->canModifyFields())
                ->requiresConfirmation()
                ->modalHeading("Delete '{$label}'")
                ->modalDescription('Are you sure you want to permanently delete this setting? This action cannot be undone.')
                ->action(function () use ($setting): void {
                    $setting->delete();

                    $this->clearSettingsCache();
                    $this->fillFormFromDatabase();

                    Notification::make()
                        ->title('Setting deleted')
                        ->body("'{$setting->label}' has been deleted.")
                        ->warning()
                        ->send();

                    $this->dispatch('notificationsSent');

                    $this->refreshSchema();
                }),
        );

        // ── Modify Mode: hint action to Reorder (move up/down) ──
        $field = $field->hintAction(
            FormAction::make("order_up_{$setting->key}")
                ->icon('heroicon-m-arrow-up')
                ->tooltip('Move up')
                ->visible(fn (): bool => $this->modifyMode && $this->canModifyFields())
                ->action(function () use ($setting): void {
                    $this->reorderSetting($setting, 'up');
                }),
        );

        $field = $field->hintAction(
            FormAction::make("order_down_{$setting->key}")
                ->icon('heroicon-m-arrow-down')
                ->tooltip('Move down')
                ->visible(fn (): bool => $this->modifyMode && $this->canModifyFields())
                ->action(function () use ($setting): void {
                    $this->reorderSetting($setting, 'down');
                }),
        );

        return $field;
    }
}
