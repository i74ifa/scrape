<?php

namespace App\Filament\Resources\CheckoutOrders\Schemas;

use Filament\Schemas\Components\Section;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class CheckoutOrderForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make(trans('Order Information'))
                    ->columns(2)
                    ->schema([
                        TextInput::make('code')
                            ->label(trans('Code'))
                            ->disabled(),

                        TextInput::make('user.phone')
                            ->label(trans('Customer Phone'))
                            ->disabled(),

                        TextInput::make('status')
                            ->label(trans('Status'))
                            ->formatStateUsing(fn($state) => $state)
                            ->disabled(),

                        TextInput::make('payment_method')
                            ->label(trans('Payment Method'))
                            ->formatStateUsing(fn($state) => $state)
                            ->disabled(),
                    ]),

                Section::make(trans('Payment Details'))
                    ->columns(3)
                    ->schema([
                        TextInput::make('sub_total')
                            ->label(trans('Sub Total'))
                            ->prefix(trans('SAR'))
                            ->disabled(),

                        TextInput::make('tax')
                            ->label(trans('Tax'))
                            ->prefix(trans('SAR'))
                            ->disabled(),

                        TextInput::make('discount')
                            ->label(trans('Discount'))
                            ->prefix(trans('SAR'))
                            ->disabled(),

                        TextInput::make('shipping')
                            ->label(trans('Shipping'))
                            ->prefix(trans('SAR'))
                            ->disabled(),

                        TextInput::make('local_shipping')
                            ->label(trans('Local Shipping'))
                            ->prefix(trans('SAR'))
                            ->disabled(),

                        TextInput::make('grand_total')
                            ->label(trans('Grand Total'))
                            ->prefix(trans('SAR'))
                            ->disabled(),
                    ]),

                Section::make(trans('Payment Reference'))
                    ->schema([
                        TextInput::make('payment_reference')
                            ->label(trans('Payment Reference'))
                            ->columnSpanFull()
                            ->disabled(),
                    ]),
            ]);
    }
}
