<?php


namespace App\Enums;

enum NotificationType: string
{
    case ORDER = 'order';
    case PRODUCT = 'product';
    case PROMO = 'promo';
    case OTHER = 'other';

    public static function toArray(): array
    {
        return [
            'order'     => 'order',
            'product'   => 'product',
            'promo' => 'promo',
            'other'     => 'other',
        ];
    }

    public function label(): string
    {
        return match ($this) {
            self::ORDER      => __('order'),
            self::PRODUCT    => __('product'),
            self::PROMO  => __('promotion'),
            self::OTHER      => __('other'),
        };
    }
}
