<?php

namespace App\Interfaces;

interface PaymentMethodInterface
{
    /**
     * Get the validation rules for this payment method.
     *
     * @return array
     */
    public static function rules(): array;
}
