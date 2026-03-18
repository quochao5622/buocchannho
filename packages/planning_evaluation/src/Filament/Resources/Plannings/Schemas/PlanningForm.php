<?php

namespace Quochao56\PlanningEvaluation\Filament\Resources\Plannings\Schemas;

use App\Enum\BaseStatusEnum;
use Filament\Forms\Components\Select;
use Filament\Schemas\Schema;

class PlanningForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                \Filament\Forms\Components\TextInput::make('name')
                    ->label(trans('planning-evaluation::planning.fields.name'))
                    ->required(),
                \Filament\Forms\Components\Textarea::make('description')
                    ->label(trans('planning-evaluation::planning.fields.description')),
                \Filament\Forms\Components\Select::make('employee_id')
                    ->label(trans('planning-evaluation::planning.fields.employee'))
                    ->relationship('employee', 'name')
                    ->searchable(),
                \Filament\Forms\Components\Select::make('student_id')
                    ->label(trans('planning-evaluation::planning.fields.student'))
                    ->relationship('student', 'name')
                    ->searchable(),
                \Filament\Forms\Components\DatePicker::make('start_date')
                    ->label(trans('planning-evaluation::planning.fields.start_date'))
                    ->native(false)
                    ->displayFormat('d/m/Y'),
                \Filament\Forms\Components\DatePicker::make('end_date')
                    ->label(trans('planning-evaluation::planning.fields.end_date'))
                    ->native(false)
                    ->displayFormat('d/m/Y'),
                Select::make('status')
                ->label(trans('planning-evaluation::planning.fields.status'))
                ->options([
                    BaseStatusEnum::Published->value   => BaseStatusEnum::Published->getLabel(),
                    BaseStatusEnum::Pending->value   => BaseStatusEnum::Pending->getLabel(),
                    BaseStatusEnum::Draft->value   => BaseStatusEnum::Draft->getLabel(),
                ])
                ->default(BaseStatusEnum::Published->value)
                ->required(),
                \Filament\Forms\Components\Repeater::make('planning_details')
                    ->label(trans('planning-evaluation::planning.fields.details'))
                    ->default([
                        [
                            'linh_vuc' => [['content' => '**Vận động**']],
                            'muc_tieu' => [],
                            'hoat_dong' => [],
                            'phuong_tien' => [],
                            'muc_tieu_du_phong' => [],
                        ],
                        [
                            'linh_vuc' => [['content' => '**Nhận thức**']],
                            'muc_tieu' => [],
                            'hoat_dong' => [],
                            'phuong_tien' => [],
                            'muc_tieu_du_phong' => [],
                        ],
                        [
                            'linh_vuc' => [['content' => '**Ngôn ngữ - giao tiếp**']],
                            'muc_tieu' => [],
                            'hoat_dong' => [],
                            'phuong_tien' => [],
                            'muc_tieu_du_phong' => [],
                        ],
                        [
                            'linh_vuc' => [['content' => '**Kỹ năng tình cảm – xã hội**']],
                            'muc_tieu' => [],
                            'hoat_dong' => [],
                            'phuong_tien' => [],
                            'muc_tieu_du_phong' => [],
                        ],
                    ])
                    ->schema([
                        \Filament\Schemas\Components\Grid::make(5)
                            ->schema([
                                \Filament\Forms\Components\Repeater::make('linh_vuc')
                                    ->label(trans('planning-evaluation::planning.fields.linh_vuc'))
                                    ->schema([
                                        \Filament\Forms\Components\MarkdownEditor::make('content')
                                            ->label(trans('planning-evaluation::planning.fields.noi_dung'))
                                            ->toolbarButtons([
                                                'bold',
                                                'italic',
                                                'bulletList',
                                            ]),
                                    ])
                                    ->createItemButtonLabel(trans('planning-evaluation::planning.actions.add_linh_vuc_item'))
                                    ->collapsible(),

                                \Filament\Forms\Components\Repeater::make('muc_tieu')
                                    ->label(trans('planning-evaluation::planning.fields.muc_tieu'))
                                    ->schema([
                                        \Filament\Forms\Components\MarkdownEditor::make('content')
                                            ->label(trans('planning-evaluation::planning.fields.noi_dung'))
                                            ->toolbarButtons([
                                                'bold',
                                                'italic',
                                                'bulletList',
                                            ]),
                                    ])
                                    ->createItemButtonLabel(trans('planning-evaluation::planning.actions.add_muc_tieu'))
                                    ->collapsible(),

                                \Filament\Forms\Components\Repeater::make('hoat_dong')
                                    ->label(trans('planning-evaluation::planning.fields.hoat_dong'))
                                    ->schema([
                                        \Filament\Forms\Components\MarkdownEditor::make('content')
                                            ->label(trans('planning-evaluation::planning.fields.noi_dung'))
                                            ->toolbarButtons([
                                                'bold',
                                                'italic',
                                                'bulletList',
                                            ]),
                                    ])
                                    ->createItemButtonLabel(trans('planning-evaluation::planning.actions.add_hoat_dong'))
                                    ->collapsible(),

                                \Filament\Forms\Components\Repeater::make('phuong_tien')
                                    ->label(trans('planning-evaluation::planning.fields.phuong_tien'))
                                    ->schema([
                                        \Filament\Forms\Components\MarkdownEditor::make('content')
                                            ->label(trans('planning-evaluation::planning.fields.noi_dung'))
                                            ->toolbarButtons([
                                                'bold',
                                                'italic',
                                                'bulletList',
                                            ]),
                                    ])
                                    ->createItemButtonLabel(trans('planning-evaluation::planning.actions.add_phuong_tien'))
                                    ->collapsible(),

                                \Filament\Forms\Components\Repeater::make('muc_tieu_du_phong')
                                    ->label(trans('planning-evaluation::planning.fields.muc_tieu_du_phong'))
                                    ->schema([
                                        \Filament\Forms\Components\MarkdownEditor::make('content')
                                            ->label(trans('planning-evaluation::planning.fields.noi_dung'))
                                            ->toolbarButtons([
                                                'bold',
                                                'italic',
                                                'bulletList',
                                            ]),
                                    ])
                                    ->createItemButtonLabel(trans('planning-evaluation::planning.actions.add_muc_tieu_du_phong'))
                                    ->collapsible(),
                            ]),
                    ])
                    ->createItemButtonLabel(trans('planning-evaluation::planning.actions.add_row'))
                    ->collapsible()
                    ->columnSpanFull(),
            ]);
    }
}
