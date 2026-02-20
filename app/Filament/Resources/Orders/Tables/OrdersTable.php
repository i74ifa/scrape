<?php

namespace App\Filament\Resources\Orders\Tables;

use App\Enums\CheckoutOrderStatus;
use App\Enums\OrderStatus;
use App\Notifications\Customer\ChangeOrderStatusNotify;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Tables\Table;
use Filament\Tables\Columns;
use Illuminate\Support\Facades\Log;

class OrdersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                Columns\TextColumn::make('id')->label(trans('ID')),
                Columns\TextColumn::make('user.phone')->label(trans('User')),
                Columns\TextColumn::make('sub_total')->label(trans('Sub Total')),
                Columns\TextColumn::make('grand_total')->label(trans('Grand Total')),
                Columns\TextColumn::make('status')->badge()->label(trans('Status'))->formatStateUsing(fn($state) => $state->label()),
                Columns\TextColumn::make('payment_method')
                    ->badge()
                    ->label(trans('Payment Method'))
                    ->formatStateUsing(fn($state) => $state->label()),
                Columns\TextColumn::make('created_at')->label(trans('Created At')),
            ])
            ->filters([
                //
            ])
            ->defaultSort('created_at', 'desc')
            ->recordActions([
                // EditAction::make(),
                Action::make('change_status')
                    ->label(trans('Change Status'))
                    ->schema([
                        Select::make('status')
                            ->options(CheckoutOrderStatus::toArray())
                            ->required(),
                    ])
                    ->action(function ($record, $data) {
                        $record->update($data);

                        if ($data['status'] == CheckoutOrderStatus::PAID->value) {
                            $record->orders()->update([
                                'status' => OrderStatus::APPROVED->value,
                            ]);
                        }

                        // notify user
                        try {
                            $user = $record->user;
                            $user->notify(new ChangeOrderStatusNotify(
                                order: $record,
                                title: trans('Order Status Changed'),
                                description: trans('Order Status Changed to :status', ['status' => OrderStatus::APPROVED->label()]),
                                url: route('orders.show', $record->id),
                            ));
                        } catch (\Exception $th) {
                            Log::info($th->getMessage());
                            //throw $th;
                        }
                    }),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    // DeleteBulkAction::make(),
                ]),
            ]);
    }
}
