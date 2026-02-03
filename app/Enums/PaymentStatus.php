<?php

namespace App\Enums;

enum PaymentStatus: string
{
    case PENDING = 'pending';
    case PAID = 'paid';
    case FAILED = 'failed';
    case REFUNDED = 'refunded';

    public function label(): string
    {
        return match ($this) {
            self::PENDING => __('Pending'),
            self::PAID => __('Paid'),
            self::FAILED => __('Failed'),
            self::REFUNDED => __('Refunded'),
        };
    }
}
