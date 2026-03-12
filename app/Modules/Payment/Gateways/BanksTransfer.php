<?php

namespace App\Modules\Payment\Gateways;

use App\Modules\Payment\Contracts\PaymentGateway;

class BanksTransfer implements PaymentGateway
{
    public $bank_id;
    public $iban;
    public $image;

    public function pay(array $data): ?array
    {
        if ($data['image']) {
            $data['image'] = $data['image']->store('images', 'public');
        }

        return $data;
    }

    public function rules(): array
    {
        return [
            'bank_id' => 'required|string',
            'iban'  => 'required_if:image,null|string|nullable',
            'image' => 'required_if:iban,null|image|mimes:jpeg,png,jpg,gif|max:2048|nullable',
        ];
    }
}
