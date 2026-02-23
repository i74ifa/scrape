<?php

namespace App\Filament\Schemas\Components;

use Filament\Schemas\Components\Component;

class Products extends Component
{
    protected string $view = 'filament.schemas.components.products';

    public static function make(): static
    {
        return app(static::class);
    }
}
