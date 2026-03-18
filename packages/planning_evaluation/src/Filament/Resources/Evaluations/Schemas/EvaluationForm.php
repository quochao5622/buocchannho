<?php

namespace Quochao56\PlanningEvaluation\Filament\Resources\Evaluations\Schemas;

use App\Enum\BaseStatusEnum;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;

class EvaluationForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            \Filament\Forms\Components\TextInput::make('name')
                ->label(trans('planning-evaluation::evaluation.fields.name'))
                ->required(),
            \Filament\Forms\Components\Textarea::make('description')
                ->label(trans('planning-evaluation::evaluation.fields.description')),
            \Filament\Forms\Components\Select::make('planning_id')
                ->label(trans('planning-evaluation::evaluation.fields.planning_id'))
                ->relationship('planning', 'name')
                ->disabled()
                ->dehydrated(false)
                ->required()
                ->searchable(),
            \Filament\Forms\Components\Select::make('status')
                ->label(trans('planning-evaluation::evaluation.fields.status'))
                ->options([
                    BaseStatusEnum::Published->value => BaseStatusEnum::Published->getLabel(),
                    BaseStatusEnum::Pending->value => BaseStatusEnum::Pending->getLabel(),
                    BaseStatusEnum::Draft->value => BaseStatusEnum::Draft->getLabel(),
                ])
                ->default(BaseStatusEnum::Draft->value)
                ->required(),
            \Filament\Forms\Components\Repeater::make('evaluation_details')
                ->label(trans('planning-evaluation::evaluation.fields.evaluation_details'))
                ->schema([
                    \Filament\Schemas\Components\Grid::make(2)
                        ->schema([
                            \Filament\Forms\Components\Textarea::make('linh_vuc')
                                ->label(trans('planning-evaluation::evaluation.fields.linh_vuc'))
                                ->disabled()
                                ->dehydrated(),
                            \Filament\Forms\Components\Repeater::make('muc_tieu')
                                ->label(trans('planning-evaluation::evaluation.fields.muc_tieu'))
                                ->schema([
                                    \Filament\Forms\Components\Textarea::make('content')
                                        ->label(trans('planning-evaluation::evaluation.fields.content'))
                                        ->dehydrated(),
                                    \Filament\Forms\Components\Select::make('danh_gia')
                                        ->label(trans('planning-evaluation::evaluation.fields.danh_gia'))
                                        ->required(fn (Get $get): bool => ($get('../../../../status') === BaseStatusEnum::Published->value))
                                        ->validationMessages([
                                            'required' => trans('planning-evaluation::evaluation.validation.danh_gia_required_when_published'),
                                        ])
                                        ->options([
                                            '+' => '+',
                                            '+/-' => '+/-',
                                            '-' => '-',
                                        ]),
                                    \Filament\Forms\Components\Textarea::make('nhan_xet')
                                        ->label(trans('planning-evaluation::evaluation.fields.nhan_xet')),
                                ])
                                ->createItemButtonLabel(trans('planning-evaluation::evaluation.actions.add_muc_tieu'))
                                ->collapsible(),
                        ]),
                ])
                ->createItemButtonLabel(trans('planning-evaluation::evaluation.actions.add_linh_vuc'))
                ->collapsible()
                ->columnSpanFull(),
        ]);
    }
}
