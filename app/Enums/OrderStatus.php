<?php

namespace App\Enums;

enum OrderStatus: string
{
    case PENDING = 'pending';
    case APPROVED = 'approved';
    case WAITING_FOR_PAYMENT = 'waiting_for_payment';
    case PAID = 'paid';
    case SHIPPED = 'shipped';
    case DELIVERED = 'delivered';
    case CANCELLED = 'cancelled';
    case RETURNED = 'returned';

    public static function toArray(): array
    {
        return array_map(fn($case) => $case->value, self::cases());
    }

    public function label(): string
    {
        return match ($this) {
            self::PENDING => __('Pending'),
            self::APPROVED => __('Approved'),
            self::SHIPPED => __('Shipped'),
            self::DELIVERED => __('Delivered'),
            self::CANCELLED => __('Cancelled'),
            self::RETURNED => __('Returned'),
        };
    }
}
