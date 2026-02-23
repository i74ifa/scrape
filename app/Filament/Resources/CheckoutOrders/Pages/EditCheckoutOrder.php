<?php

namespace App\Filament\Resources\CheckoutOrders\Pages;

use App\Filament\Resources\CheckoutOrders\CheckoutOrderResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditCheckoutOrder extends EditRecord
{
    protected static string $resource = CheckoutOrderResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
