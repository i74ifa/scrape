<?php

namespace App\Modules\Payment\Gateways;

use App\Modules\Payment\Contracts\PaymentGateway;

class BanksTransfer implements PaymentGateway
{
    public $bankName;
    public $iban;
    public $image;

    public function pay(array $data): ?array
    {
        if ($data['image']) {
            $data['image'] = $data['image']->store('images', 'public');
        }

        return $this->getData();
    }

    public function rules(): array
    {
        return [
            'bank_name' => 'required|string',
            'iban'  => 'required_if:image,null|string|nullable',
            'image' => 'required_if:iban,null|image|mimes:jpeg,png,jpg,gif|max:2048|nullable',
        ];
    }

    private function getData(): array
    {
        return [
            'bank_name' => $this->bankName,
            'iban' => $this->iban,
            'image' => $this->image,
        ];
    }
}
