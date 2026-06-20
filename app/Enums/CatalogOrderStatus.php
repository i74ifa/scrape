<?php

namespace App\Enums;

/**
 * Lifecycle of a catalog order. A lean, local-fulfillment chain (distinct from
 * the scraped CheckoutOrderStatus / OrderStatus state machines):
 *
 *   pending → confirmed → shipped → delivered
 *
 * `cancelled` is a terminal off-ramp reachable from any non-terminal state.
 */
enum CatalogOrderStatus: string
{
    case PENDING = 'pending';
    case CONFIRMED = 'confirmed';
    case SHIPPED = 'shipped';
    case DELIVERED = 'delivered';
    case CANCELLED = 'cancelled';

    /** Human (Arabic) label. */
    public function label(): string
    {
        return match ($this) {
            self::PENDING => 'قيد الانتظار',
            self::CONFIRMED => 'مؤكّد',
            self::SHIPPED => 'تم الشحن',
            self::DELIVERED => 'تم التسليم',
            self::CANCELLED => 'ملغى',
        };
    }

    /** A UI color token (matches the admin chip palette). */
    public function color(): string
    {
        return match ($this) {
            self::PENDING => 'warning',
            self::CONFIRMED => 'primary',
            self::SHIPPED => 'secondary',
            self::DELIVERED => 'success',
            self::CANCELLED => 'danger',
        };
    }

    /** The next forward status, or null at a terminal state. */
    public function next(): ?self
    {
        return match ($this) {
            self::PENDING => self::CONFIRMED,
            self::CONFIRMED => self::SHIPPED,
            self::SHIPPED => self::DELIVERED,
            self::DELIVERED, self::CANCELLED => null,
        };
    }

    /** Whether the order can still be cancelled. */
    public function canCancel(): bool
    {
        return ! in_array($this, [self::DELIVERED, self::CANCELLED], true);
    }

    public function isTerminal(): bool
    {
        return in_array($this, [self::DELIVERED, self::CANCELLED], true);
    }

    /** @return array<string, string> value => label */
    public static function toArray(): array
    {
        return collect(self::cases())
            ->mapWithKeys(fn ($c) => [$c->value => $c->label()])
            ->toArray();
    }

    /** @return array<int, string> */
    public static function values(): array
    {
        return array_map(fn ($c) => $c->value, self::cases());
    }
}
