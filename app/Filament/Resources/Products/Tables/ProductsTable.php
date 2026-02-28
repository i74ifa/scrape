<?php

namespace App\Filament\Resources\Products\Tables;

use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\BulkActionGroup;
use Filament\Tables\Columns;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;

class ProductsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                Columns\ImageColumn::make('image')
                    ->label(trans('Image'))
                    ->circular(),

                Columns\TextColumn::make('name')
                    ->label(trans('Name'))
                    ->searchable()
                    ->sortable(),

                Columns\TextColumn::make('category')
                    ->label(trans('Category'))
                    ->searchable()
                    ->sortable()
                    ->badge()
                    ->color('info'),

                Columns\TextColumn::make('brand')
                    ->label(trans('Brand'))
                    ->searchable()
                    ->sortable()
                    ->badge()
                    ->color('gray'),

                Columns\TextColumn::make('price')
                    ->label(trans('Price'))
                    ->money('USD')
                    ->sortable(),

                Columns\TextColumn::make('weight')
                    ->label(trans('Weight'))
                    ->suffix(' g')
                    ->sortable(),

                Columns\TextColumn::make('platform.name')
                    ->label(trans('Platform'))
                    ->badge()
                    ->sortable(),

                Columns\ToggleColumn::make('is_active')
                    ->label(trans('Active'))
                    ->sortable(),

                Columns\TextColumn::make('created_at')
                    ->label(trans('Created At'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                TernaryFilter::make('is_active')
                    ->label(trans('Active')),
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
    }
}
