<?php

namespace App\Filament\Resources\Products\Schemas;

use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\KeyValue;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class ProductForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                // Image
                FileUpload::make('image')
                    ->label(trans('Image'))
                    ->image()
                    ->columnSpanFull(),

                // Name
                TextInput::make('name')
                    ->label(trans('Name'))
                    ->required()
                    ->maxLength(255),

                // Price
                TextInput::make('price')
                    ->label(trans('Price (USD)'))
                    ->numeric()
                    ->prefix('$')
                    ->required(),

                // Category
                TextInput::make('category')
                    ->label(trans('Category'))
                    ->nullable(),

                // Brand
                TextInput::make('brand')
                    ->label(trans('Brand'))
                    ->nullable(),

                // Weight (grams)
                TextInput::make('weight')
                    ->label(trans('Weight (grams)'))
                    ->numeric()
                    ->suffix('g')
                    ->default(\App\Models\Product::DEFAULT_WEIGHT_GRAMS)
                    ->required(),

                // Product URL
                TextInput::make('url')
                    ->label(trans('Product URL'))
                    ->url()
                    ->required()
                    ->columnSpanFull(),

                // Description
                Textarea::make('description')
                    ->label(trans('Description'))
                    ->nullable()
                    ->columnSpanFull(),

                // Platform
                Select::make('platform_id')
                    ->label(trans('Platform'))
                    ->relationship('platform', 'name')
                    ->searchable()
                    ->preload()
                    ->required(),

                // Variants (key-value)
                KeyValue::make('variants')
                    ->label(trans('Variants'))
                    ->nullable()
                    ->columnSpanFull(),

                // Active toggle
                Toggle::make('active')
                    ->label(trans('Active'))
                    ->default(false),
            ])
            ->columns(2);
    }
}
