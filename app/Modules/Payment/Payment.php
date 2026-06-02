<?php


namespace App\Modules\Payment;

use App\Modules\Payment\Contracts\PaymentGateway;
use App\Modules\Payment\Gateways\BanksTransfer;
use Exception;

final readonly class Payment
{
    public static function handle(string $gateway): PaymentGateway
    {
        return match ($gateway) {
            'banks_transfer' => new BanksTransfer(),
            default => throw new Exception(__('messages.unsupported_gateway', ['gateway' => $gateway])),
        };
    }

    public static function rules(string $gateway): array
    {
        return match ($gateway) {
            'banks_transfer' => (new BanksTransfer())->rules(),
            default => throw new Exception(__('messages.unsupported_gateway', ['gateway' => $gateway])),
        };
    }
}
