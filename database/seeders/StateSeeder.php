<?php

namespace Database\Seeders;

use App\Models\State;
use Illuminate\Database\Seeder;

class StateSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $states = json_decode(file_get_contents(base_path('storage/app/YEM.json')), true);

        State::truncate();
        foreach ($states['states'] as $state) {
            State::create([
                //  remove the "Governorate" from the name
                'name' => trim(str_replace('Governorate', '', $state['name'])),
                'code' => $state['state_code'],
            ]);
        }
    }
}
