<?php

namespace App\Filament\Resources\Users\Tables;

use App\Notifications\Customer\SendPromotionNotification;
use Filament\Actions\Action;
use Filament\Actions\BulkAction;
use Filament\Actions\BulkActionGroup;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Tables\Columns;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Log;

class UsersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                Columns\TextColumn::make('id')
                    ->label(trans('ID'))
                    ->sortable(),

                Columns\TextColumn::make('name')
                    ->label(trans('Name'))
                    ->searchable(),

                Columns\TextColumn::make('phone')
                    ->label(trans('Phone'))
                    ->searchable(),

                Columns\TextColumn::make('email')
                    ->label(trans('Email'))
                    ->searchable(),

                Columns\TextColumn::make('country_code')
                    ->label(trans('Country Code')),

                Columns\TextColumn::make('created_at')
                    ->label(trans('Created At'))
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                Action::make('send_promotion')
                    ->label(trans('Send Notification'))
                    ->icon('heroicon-o-bell')
                    ->color('warning')
                    ->schema([
                        TextInput::make('title')
                            ->label(trans('Title'))
                            ->required()
                            ->maxLength(255),

                        Textarea::make('body')
                            ->label(trans('Message'))
                            ->required()
                            ->rows(3),

                        TextInput::make('url')
                            ->label(trans('URL'))
                            ->url()
                            ->nullable(),
                    ])
                    ->action(function ($record, array $data) {
                        try {
                            $record->notify(new SendPromotionNotification(
                                title: $data['title'],
                                body: $data['body'],
                                url: $data['url'] ?? null,
                            ));
                        } catch (\Exception $e) {
                            Log::warning('FCM promotion notification failed: ' . $e->getMessage());
                        }
                    }),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    BulkAction::make('send_promotion_bulk')
                        ->label(trans('Send Notification'))
                        ->icon('heroicon-o-bell')
                        ->color('warning')
                        ->schema([
                            TextInput::make('title')
                                ->label(trans('Title'))
                                ->required()
                                ->maxLength(255),

                            Textarea::make('body')
                                ->label(trans('Message'))
                                ->required()
                                ->rows(3),

                            TextInput::make('url')
                                ->label(trans('URL'))
                                ->url()
                                ->nullable(),
                        ])
                        ->action(function (Collection $records, array $data) {
                            foreach ($records as $user) {
                                try {
                                    $user->notify(new SendPromotionNotification(
                                        title: $data['title'],
                                        body: $data['body'],
                                        url: $data['url'] ?? null,
                                    ));
                                } catch (\Exception $e) {
                                    Log::warning('FCM promotion bulk notification failed for user ' . $user->id . ': ' . $e->getMessage());
                                }
                            }
                        })
                        ->deselectRecordsAfterCompletion(),
                ]),
            ]);
    }
}
