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

    public static function all()
    {
        return [
            self::PENDING->value,
            self::APPROVED->value,
            self::PURCHASING->value,
            self::PURCHASED->value,
            self::READY_TO_SHIP->value,
            self::CUSTOMS_CLEARANCE->value,
            self::SHIPPED->value,
            self::DELIVERED->value,
            self::CANCELLED->value,
            self::RETURNED->value,
        ];
    }

    public function icon()
    {
        return match ($this) {
            self::PENDING => 'pending',
            self::APPROVED => 'received',
            self::PURCHASING => 'processing',
            self::PURCHASED => 'purchased',
            self::READY_TO_SHIP => 'ready_to_ship',
            self::CUSTOMS_CLEARANCE => 'customs_clearance',
            self::SHIPPED => 'shipped',
            self::DELIVERED => 'shipped',
            self::CANCELLED => 'cancelled',
            self::RETURNED => 'returned',
        };
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

    public function title()
    {
        return match ($this) {
            self::PENDING => __('titles.pending'),
            self::APPROVED => __('titles.approved'),
            self::PURCHASING => __('titles.purchasing'),
            self::SHIPPED => __('titles.shipped'),
            self::PURCHASED => __('titles.purchased'),
            self::READY_TO_SHIP => __('titles.ready_to_ship'),
            self::CUSTOMS_CLEARANCE => __('titles.customs_clearance'),
            self::DELIVERED => __('titles.delivered'),
            self::CANCELLED => __('titles.cancelled'),
            self::RETURNED => __('titles.returned'),
        };
    }

    public static function getTimelines($statues, $platform)
    {
        $historyMap = collect($statues)->keyBy('status')->map(fn($s) => $s['created_at']);

        return collect(self::cases())->map(function ($case) use ($historyMap, $platform) {
            return [
                'status'     => $case->value,
                'message'    => $case->title(),
                'completed'  => $historyMap->has($case->value),
                'current'    => $case->next(),
                'created_at' => $historyMap->get($case->value),
                'icon'       => $case->icon(),
            ];
        });
    }

    public function next()
    {
        return match ($this) {
            self::PENDING => self::APPROVED,
            self::APPROVED => self::PURCHASING,
            self::PURCHASING => self::PURCHASED,
            self::PURCHASED => self::READY_TO_SHIP,
            self::READY_TO_SHIP => self::CUSTOMS_CLEARANCE,
            self::CUSTOMS_CLEARANCE => self::SHIPPED,
            self::SHIPPED => self::DELIVERED,
            self::DELIVERED => null,
            self::CANCELLED => null,
            self::RETURNED => null,
        };
    }

    public function percentage()
    {
        return match ($this) {
            self::PENDING => 10,
            self::APPROVED => 20,
            self::PURCHASING => 30,
            self::PURCHASED => 40,
            self::READY_TO_SHIP => 50,
            self::CUSTOMS_CLEARANCE => 60,
            self::SHIPPED => 70,
            self::DELIVERED => 80,
            self::CANCELLED => 90,
            self::RETURNED => 100,
        };
    }
}
