<?php

namespace App\Http\Controllers;

use App\Http\Resources\BankAccountResource;
use App\Models\BankAccount;

class BankAccountController extends Controller
{
    public function index()
    {
        return BankAccountResource::collection(BankAccount::all());
    }
}
