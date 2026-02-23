<?php

namespace App\Filament\Resources\Orders\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
// use Filament\Actions\EditAction;
use Filament\Tables\Table;
use Filament\Tables\Columns;


class OrdersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                Columns\TextColumn::make('id')->label(trans('ID')),
                Columns\TextColumn::make('code')->label(trans('Code')),
                Columns\TextColumn::make('platform.name')->label(trans('Platform')),
                Columns\TextColumn::make('checkout_order.user.phone')->label(trans('User')),
                Columns\TextColumn::make('sub_total')->label(trans('Sub Total')),
                Columns\TextColumn::make('grand_total')->label(trans('Grand Total')),
                Columns\TextColumn::make('status')->badge()->label(trans('Status'))->formatStateUsing(fn($state) => $state->label()),
                Columns\TextColumn::make('created_at')->label(trans('Created At')),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                // EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    // DeleteBulkAction::make(),
                ]),
            ]);
    }
}
