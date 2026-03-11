<?php


namespace App\Modules\Massaging\Contracts;

interface MessageSender
{
    public function send($to, $message, $countryCode = '967'): bool;
}
