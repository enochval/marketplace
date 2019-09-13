<?php


namespace App\Http\Controllers;


use App\Repositories\Contracts\IJobRepository;
use App\Utils\Rules;
use Exception;
use Illuminate\Support\Facades\Validator;

class JobBoardController extends Controller
{
    /**
     * @var IJobRepository
     */
    private $jobRepository;

    public function __construct(IJobRepository $jobRepository)
    {
        $this->jobRepository = $jobRepository;
    }

    public function getJobs()
    {
        try {
            $jobs = $this->jobRepository->getJobs();
            return $jobs;
        } catch (Exception $e) {
            return $this->error($e->getMessage());
        }
    }

    public function postJob() 
    {
        
        $payload = request()->all();

        $validator = Validator::make($payload, Rules::get('POST_JOB'));
        if ($validator->fails()) {
            return $this->validationErrors($validator->getMessageBag()->all());
        }
        try {
            $employer_id = auth()->user()->id;
            $job = $this->jobRepository->postJob($employer_id, $payload);
            return $this->withData($job);
            // return $this->success("Job post creation successful!");
        } catch(Excection $e) {
            return $this->error($e->getMessage());
        }
    }

    public function getSingleJob($id)
    {
        try {
            $job = $this->jobRepository->getSingleJob($id);
            return $this->withData($job);
        } catch (Exception $e) {
            return $this->error($e->getMessage());
        }
    }
}
