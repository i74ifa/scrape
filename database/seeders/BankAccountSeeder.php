<?php

namespace Database\Seeders;

use App\Models\BankAccount;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class BankAccountSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $banks = [
            [
                'id' => 1,
                'name' => 'الكريمي (حاسب)',
                'account_name' => 'طلبي',
                'number' => '2133145',
                'iban' => '',
                'logo' => '/images/banks/kuraimi.png'
            ],
            [
                'id' => 2,
                'name' => 'محفظة جيب',
                'account_name' => 'طلبي',
                'number' => '987654321',
                'iban' => '',
                'logo' => '/images/banks/jaib.jpg'
            ],
            [
                'id' => 3,
                'name' => '',
                'account_name' => 'طلبي',
                'number' => '',
                'iban' => '',
                'logo' => '/images/banks/.png'
            ],
        ];

        foreach ($banks as $bank) {
            if (!BankAccount::find($bank['id'])) {
                BankAccount::create($bank);
            }
        }
    }
}
