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

    public static function values(): array
    {
        return collect(self::cases())
            ->map(fn($case) => $case->value)
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

    public function next(): ?self
    {
        return match ($this) {
            self::PENDING_PAYMENT => self::PAID,
            self::PAID => null,
            self::PARTIALLY_REFUNDED => self::REFUNDED,
            self::REFUNDED => null,
            self::FAILED => null,
        };
    }
}
