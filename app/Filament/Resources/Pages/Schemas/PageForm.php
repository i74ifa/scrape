<?php

namespace App\Filament\Resources\Pages\Schemas;

use Filament\Forms;
use Filament\Schemas\Schema;

class PageForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Forms\Components\TextInput::make('title')
                    ->required(),
                Forms\Components\TextInput::make('slug')
                    ->required(),
                Forms\Components\RichEditor::make('content')
                    ->columnSpanFull()
                    ->required(),
            ]);
    }
}
