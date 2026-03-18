<?php

namespace Quochao56\PlanningEvaluation\Filament\Resources\Plannings\RelationManagers;

use App\Enum\BaseStatusEnum;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class EvaluationRelationManager extends RelationManager
{
    protected static string $relationship = 'evaluation';

    public function form(Schema $schema): Schema
    {
        return $schema->components([
            TextInput::make('name')
                ->label(trans('planning-evaluation::evaluation.fields.name'))
                ->required(),
            Textarea::make('description')
                ->label(trans('planning-evaluation::evaluation.fields.description')),
            Textarea::make('evaluation_details')
                ->label(trans('planning-evaluation::evaluation.fields.evaluation_details')),
            \Filament\Forms\Components\Select::make('status')
                ->label(trans('planning-evaluation::evaluation.fields.status'))
                ->options([
                    BaseStatusEnum::Published->value => BaseStatusEnum::Published->getLabel(),
                    BaseStatusEnum::Pending->value => BaseStatusEnum::Pending->getLabel(),
                    BaseStatusEnum::Draft->value => BaseStatusEnum::Draft->getLabel(),
                ])
                ->default(BaseStatusEnum::Published->value)
                ->required(),
        ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label(trans('planning-evaluation::evaluation.fields.name')),
                TextColumn::make('description')
                    ->label(trans('planning-evaluation::evaluation.fields.description'))
                    ->limit(60),
                TextColumn::make('status')
                    ->label(trans('planning-evaluation::evaluation.fields.status'))
                    ->badge(),
            ])
            ->headerActions([
                CreateAction::make()
                    ->visible(fn () => $this->getOwnerRecord()->evaluation === null),
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
