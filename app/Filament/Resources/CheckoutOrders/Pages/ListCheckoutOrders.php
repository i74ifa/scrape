<?php

namespace App\Filament\Resources\CheckoutOrders\Pages;

use App\Filament\Resources\CheckoutOrders\CheckoutOrderResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListCheckoutOrders extends ListRecords
{
    protected static string $resource = CheckoutOrderResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
