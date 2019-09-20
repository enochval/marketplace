<?php

use Illuminate\Database\Seeder;
use App\Models\GeneralSetting;

class GeneralSettingsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        GeneralSetting::create([
            'verification_fee' => 1000,
            'no_of_employer_free_resource' => 1,
            'no_of_worker_free_trial' => 2
        ]);
    }
}
