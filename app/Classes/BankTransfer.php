<?php

namespace App\Classes;

use App\Interfaces\PaymentMethodInterface;

class BankTransfer implements PaymentMethodInterface
{

    protected $bankName;
    protected $iban;
    protected $image;

    public function __construct(
        $data
    ) {
        $this->bankName = $data['bank_name'];
        $this->iban = $data['iban'];
    }

    public static function make($data): self
    {
        return new self($data);
    }

    public static function rules(): array
    {
        return [
            'bank_name' => 'required|string',
            'iban' => 'required_if:image,null|string',
            'image' => 'required_if:iban,null|image|mimes:jpeg,png,jpg,gif|max:2048',
        ];
    }

    public function getData(): array
    {
        return [
            'bank_name' => $this->bankName,
            'iban' => $this->iban,
            'image' => $this->image,
        ];
    }
}
