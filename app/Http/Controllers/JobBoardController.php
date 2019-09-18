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
        $this->middleware('auth:api');

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

    public function createJob()
    {
        $validator = Validator::make(request()->all(), Rules::get('CREATE_JOB'));
        if ($validator->fails()) {
            return $this->validationErrors($validator->getMessageBag()->all());
        }

        try {
            $this->jobRepository->createJob(auth()->id(), request()->all());
            return $this->success("Job created successfully");
        } catch(Exception $e) {
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
