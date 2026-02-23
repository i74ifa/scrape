<?php

namespace App\Filament\Resources\CheckoutOrders;

use App\Filament\Resources\CheckoutOrders\Pages\CreateCheckoutOrder;
use App\Filament\Resources\CheckoutOrders\Pages\EditCheckoutOrder;
use App\Filament\Resources\CheckoutOrders\Pages\ListCheckoutOrders;
use App\Filament\Resources\CheckoutOrders\RelationManagers\OrdersRelationManager;
use App\Filament\Resources\CheckoutOrders\Schemas\CheckoutOrderForm;
use App\Filament\Resources\CheckoutOrders\Tables\CheckoutOrdersTable;
use App\Models\CheckoutOrder;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class CheckoutOrderResource extends Resource
{
    protected static ?string $model = CheckoutOrder::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?string $recordTitleAttribute = 'Checkout Order';

    public static function form(Schema $schema): Schema
    {
        return CheckoutOrderForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return CheckoutOrdersTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            OrdersRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListCheckoutOrders::route('/'),
            'create' => CreateCheckoutOrder::route('/create'),
            'edit' => EditCheckoutOrder::route('/{record}/edit'),
        ];
    }
}
