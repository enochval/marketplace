<?php

use App\Models\JobBoard;
use Illuminate\Database\Seeder;

class JobBoardTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        factory(JobBoard::class, 4)->create();

        $job_example1 = JobBoard::create([
            'employer_id' => 4,
            'title' => 'Example title one',
            'description' => 'Example description one',
            'duration' => 5,
            'originating_amount' => 20000,
            'address' => '1 example street',
            'city' => 'example city',
            'state' => 'example state'
        ]);
    }
}
