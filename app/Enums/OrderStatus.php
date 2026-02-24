<?php

namespace App\Enums;

enum OrderStatus: string
{
    case PENDING = 'pending';
    case APPROVED = 'approved';
    case PURCHASING = 'purchasing';
    case PURCHASED = 'purchased';
    case READY_TO_SHIP = 'ready_to_ship';
    case CUSTOMS_CLEARANCE = 'customs_clearance';
    case SHIPPED = 'shipped';
    case DELIVERED = 'delivered';
    case CANCELLED = 'cancelled';
    case RETURNED = 'returned';

    public static function toArray(): array
    {
        return collect(self::cases())
            ->mapWithKeys(fn($case) => [
                $case->value => trans($case->value)
            ])
            ->toArray();
    }

    public function message($platform): string
    {
        return match ($this) {
            self::PENDING => __('messages.pending', ['platform' => $platform->name]),
            self::APPROVED => __('messages.approved', ['platform' => $platform->name]),
            self::PURCHASING => __('messages.purchasing', ['platform' => $platform->name]),
            self::SHIPPED => __('messages.shipped', ['platform' => $platform->name]),
            self::PURCHASED => __('messages.purchased', ['platform' => $platform->name]),
            self::READY_TO_SHIP => __('messages.ready_to_ship', ['platform' => $platform->name]),
            self::CUSTOMS_CLEARANCE => __('messages.customs_clearance', ['platform' => $platform->name]),
            self::DELIVERED => __('messages.delivered', ['platform' => $platform->name]),
            self::CANCELLED => __('messages.cancelled', ['platform' => $platform->name]),
            self::RETURNED => __('messages.returned', ['platform' => $platform->name]),
        };
    }
}
