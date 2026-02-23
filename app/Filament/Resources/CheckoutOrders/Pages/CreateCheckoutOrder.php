<?php

namespace App\Filament\Resources\CheckoutOrders\Pages;

use App\Filament\Resources\CheckoutOrders\CheckoutOrderResource;
use Filament\Resources\Pages\CreateRecord;

class CreateCheckoutOrder extends CreateRecord
{
    protected static string $resource = CheckoutOrderResource::class;
}
