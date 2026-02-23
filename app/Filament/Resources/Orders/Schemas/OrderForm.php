<?php

namespace App\Filament\Resources\Orders\Schemas;

use Filament\Infolists\Components\ImageEntry;
use Filament\Infolists\Components\RepeatableEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class OrderForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                RepeatableEntry::make('items')
                    ->schema([
                        // 1. Image
                        ImageEntry::make('product.image')
                            ->label('')
                            ->circular()
                            ->grow(true),

                        // 2. Name & Link
                        TextEntry::make('product.name')
                            ->label('Product Name')
                            ->weight('bold')
                            ->url(fn($record) => $record->product->url) // Assuming 'link' is a column
                            ->openUrlInNewTab()
                            ->color('primary'),

                        TextEntry::make('price')
                            ->money('SAR'),
                    ])
            ]);
    }
}
