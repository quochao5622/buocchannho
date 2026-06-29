<?php

namespace Quochao56\PlanningEvaluation\Filament\Resources\Evaluations\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Repeater;
use Filament\Schemas\Components\Grid;
use App\Enum\BaseStatusEnum;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;

class EvaluationForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            TextInput::make('name')
                ->label(trans('packages.planning_evaluation::evaluation.fields.name'))
                ->required(),
            Textarea::make('description')
                ->label(trans('packages.planning_evaluation::evaluation.fields.description')),
            Select::make('planning_id')
                ->label(trans('packages.planning_evaluation::evaluation.fields.planning_id'))
                ->relationship('planning', 'name')
                ->disabled()
                ->dehydrated(false)
                ->required()
                ->searchable(),
            Select::make('status')
                ->label(trans('packages.planning_evaluation::evaluation.fields.status'))
                ->options([
                    BaseStatusEnum::Published->value => BaseStatusEnum::Published->getLabel(),
                    BaseStatusEnum::Pending->value => BaseStatusEnum::Pending->getLabel(),
                    BaseStatusEnum::Draft->value => BaseStatusEnum::Draft->getLabel(),
                ])
                ->default(BaseStatusEnum::Draft->value)
                ->required(),
            Repeater::make('evaluation_details')
                ->label(trans('packages.planning_evaluation::evaluation.fields.evaluation_details'))
                ->schema([
                    Grid::make(2)
                        ->schema([
                            Textarea::make('linh_vuc')
                                ->label(trans('packages.planning_evaluation::evaluation.fields.linh_vuc'))
                                ->disabled()
                                ->dehydrated(),
                            Repeater::make('muc_tieu')
                                ->label(trans('packages.planning_evaluation::evaluation.fields.muc_tieu'))
                                ->schema([
                                    Textarea::make('content')
                                        ->label(trans('packages.planning_evaluation::evaluation.fields.content'))
                                        ->rows(3)
                                        ->dehydrated(),
                                    Select::make('danh_gia')
                                        ->label(trans('packages.planning_evaluation::evaluation.fields.danh_gia'))
                                        ->required(fn (Get $get): bool => ($get('../../../../status') === BaseStatusEnum::Published->value))
                                        ->validationMessages([
                                            'required' => trans('packages.planning_evaluation::evaluation.validation.danh_gia_required_when_published'),
                                        ])
                                        ->options([
                                            '+' => '+',
                                            '+/-' => '+/-',
                                            '-' => '-',
                                        ]),
                                    Textarea::make('nhan_xet')
                                        ->label(trans('packages.planning_evaluation::evaluation.fields.nhan_xet'))
                                        ->rows(4),
                                ])
                                ->createItemButtonLabel(trans('packages.planning_evaluation::evaluation.actions.add_muc_tieu'))
                                ->collapsible(),
                        ]),
                ])
                ->createItemButtonLabel(trans('packages.planning_evaluation::evaluation.actions.add_linh_vuc'))
                ->collapsible()
                ->columnSpanFull(),
        ]);
    }
}
