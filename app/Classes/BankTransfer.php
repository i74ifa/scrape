<?php

namespace App\Classes;

class BankTransfer
{
    public function __construct(
        public string $bankName,
        public string $accountName,
        public string $accountNumber,
        public ?string $iban = null,
        public ?string $swiftCode = null,
    ) {}

    public static function make(
        string $bankName,
        string $accountName,
        string $accountNumber,
        ?string $iban = null,
        ?string $swiftCode = null
    ): self {
        return new self($bankName, $accountName, $accountNumber, $iban, $swiftCode);
    }
}
