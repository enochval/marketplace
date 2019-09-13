<?php

namespace App\Jobs;

use Illuminate\Support\Facades\Mail;

class StoreBVNAnalysisJob extends Job
{
    private $user;
    private $params;

    /**
     * Create a new job instance.
     *
     * @param $user
     * @param $params
     */
    public function __construct($user, $params)
    {
        $this->user = $user;
        $this->params = $params;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $this->user->bvnAnalysis()->updateOrCreate(
            ['user_id' => $this->user->id],
            [
                'first_name_match' => $this->params['first_name_match'],
                'last_name_match' => $this->params['last_name_match'],
                'dob_match' => $this->params['dob_match'],
                'phone_match' => $this->params['phone_match'],
                'score' => $this->params['score']
            ]
        );
    }
}
