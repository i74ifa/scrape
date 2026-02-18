<?php

namespace App\Enums;

enum CheckoutOrderStatus: string
{
    case PENDING_PAYMENT = 'pending_payment';
    case PAID = 'paid';
    case PARTIALLY_REFUNDED = 'partially_refunded';
    case REFUNDED = 'refunded';
    case FAILED = 'failed';

    public static function toArray(): array
    {
        return collect(self::cases())
            ->mapWithKeys(fn($case) => [
                $case->value => $case->label(),
            ])
            ->toArray();
    }

    public function label(): string
    {
        return match ($this) {
            self::PENDING_PAYMENT => __('Waiting for payment'),
            self::PAID => __('Paid'),
            self::PARTIALLY_REFUNDED => __('Partially refunded'),
            self::REFUNDED => __('Refunded'),
            self::FAILED => __('Failed'),
        };
    }
}
