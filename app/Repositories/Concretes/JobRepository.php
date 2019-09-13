<?php


namespace App\Repositories\Concretes;

use Exception;
use Carbon\Carbon;
use App\Models\User;
use App\Models\JobBoard;
use App\Jobs\SendJobPostEmailJob;
use App\Repositories\Contracts\IJobRepository;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use phpDocumentor\Reflection\Types\Object_;

class JobRepository implements IJobRepository
{
    
    private $employer;
    private $job;
    
    public function getEmployer()
    {
        return $this->employer;
    }

    
    public function setEmployer($employer_id): void
    {
        $this->employer = User::find($employer_id);
    }

    
    public function getJobs()
    {
        $jobs = JobBoard::orderBy('id', 'desc')->paginate(10);
        return [
            'payload' => $jobs
        ];
    }

    public function postJob(int $employer_id, array $params)
    {
        try {
            
            [
                'title' => $title,
                'description' => $description,
                'duration' => $duration,
                'frequency' => $frequency,
                'amount' => $amount,
                'address' => $address,
                'city' => $city,
                'state' => $state,
            ] = $params;

            
            // Persist data
            $job = JobBoard::create([
                'employer_id' => $employer_id,
                'title' => $title,
                'description' => $description,
                'duration' => $duration,
                'frequency' => $frequency,
                'originating_amount' => $amount,
                'address' => $address,
                'city' => $city,
                'state' => $state,
            ]);

            return [
                'payload' => $job,
            ];

        } catch(Exception $e) {
            report($e);

            $job->delete();

            // Return a custom error message back....
            throw new Exception("Unable to create job post, please try again");
        }
    }

    
    public function getSingleJob($id)
    {
        try{
            $jobs = JobBoard::where('id', $id)->firstOrFail();
            return [
                'payload' => $jobs
            ];
        } catch (Exception $e) {
            report($e);

            throw new Exception("Error getting selected job, please try again");
        }
        
    }
    
}

