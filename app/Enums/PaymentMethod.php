<?php

namespace App\Enums;

use App\Classes\BankTransfer;

enum PaymentMethod: string

{
    case BANK_TRANSFER = 'bank_transfer';
    case CASH_ON_DELIVERY = 'cash_on_delivery';

    public function label(): string
    {
        return match ($this) {
            self::BANK_TRANSFER => __('Bank transfer'),
            self::CASH_ON_DELIVERY => __('Cash on delivery'),
        };
    }

    public function bankTransfer(): ?BankTransfer
    {
        return match ($this) {
            self::BANK_TRANSFER => BankTransfer::make(
                bankName: 'Al-Kuraimi Bank',
                accountName: 'Company Name',
                accountNumber: '1234567890',
            ),
            default => null,
        };
    }

    public static function all(): array
    {
        return [
            [
                'name' => self::BANK_TRANSFER->value,
                'label' => self::BANK_TRANSFER->label(),
            ],
            [
                'name' => self::CASH_ON_DELIVERY->value,
                'label' => self::CASH_ON_DELIVERY->label(),
            ],
        ];
    }
}
