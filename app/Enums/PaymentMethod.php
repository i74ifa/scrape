<?php

namespace App\Enums;

enum PaymentMethod: string
{
    case BANKS_TRANSFER = 'banks_transfer';

    public function label(): string
    {
        return match ($this) {
            self::BANKS_TRANSFER => __('Bank transfer'),
        };
    }

    public static function all(): array
    {
        return [
            [
                'name' => self::BANKS_TRANSFER->value,
                'label' => self::BANKS_TRANSFER->label(),
            ],
        ];
    }
}
