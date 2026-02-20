<?php


namespace App\Enums;

enum NotificationType: string
{
    case ORDER = 'order';
    case PRODUCT = 'product';
    case OTHER = 'other';

    public static function toArray(): array
    {
        return [
            'order' => 'order',
            'product' => 'product',
            'other' => 'other',
        ];
    }

    public function label(): string
    {
        return match ($this) {
            self::ORDER => __('order'),
            self::PRODUCT => __('product'),
            self::OTHER => __('other'),
        };
    }
}
