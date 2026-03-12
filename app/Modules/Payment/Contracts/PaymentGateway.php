<?php

namespace App\Modules\Payment\Contracts;

interface PaymentGateway
{
    public function pay(array $data): ?array;

    public function rules(): array;
}
