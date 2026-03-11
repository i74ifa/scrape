<?php

namespace App\Filament\Resources\States\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class StateForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('delivery_cost')
                    ->label(trans('Delivery Cost'))
                    ->numeric()
                    ->prefix('YER')
                    ->required(),
            ]);
    }
}
