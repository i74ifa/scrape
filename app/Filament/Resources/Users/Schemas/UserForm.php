<?php

namespace App\Filament\Resources\Users\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class UserForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->label(trans('Name'))
                    ->maxLength(255),

                TextInput::make('email')
                    ->label(trans('Email'))
                    ->email()
                    ->unique(ignoreRecord: true)
                    ->maxLength(255),

                TextInput::make('phone')
                    ->label(trans('Phone'))
                    ->unique(ignoreRecord: true)
                    ->maxLength(20),

                TextInput::make('country_code')
                    ->label(trans('Country Code'))
                    ->default('967')
                    ->maxLength(3),
            ]);
    }
}
