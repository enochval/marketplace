<?php


namespace App\Repositories\Concretes;

use App\Models\GeneralSetting;
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

    private $user;
    private $job;

    /**
     * @return mixed
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * @param $user_id
     */
    public function setUser($user_id): void
    {
        $this->user = User::find($user_id);
    }

    /**
     * @param int $user_id
     * @param array $params
     * @return void
     * @throws Exception
     */
    public function createJob(int $user_id, array $params): void
    {
        $this->setUser($user_id);

        ['number_of_resource' => $no_of_resource] = $params;

        if (!$this->hasPaid($this->getUser())) {
            if ($this->exceedFreemiumResource($no_of_resource)) {
                throw new Exception("You have exceed the number of resource as a Freemium subscriber! Become a premium subscriber today.");
            }
        }

        try {
            $job = JobBoard::create([
                'employer_id' => $user_id,
                'title' => $params['title'],
                'description' => $params['description'],
                'duration' => $params['duration'],
                'frequency' => $params['frequency'],
                'amount' => $params['amount'],
                'number_of_resource' => $no_of_resource,
                'supporting_images' => json_encode($params['images']),
                'address' => $params['address'],
                'city' => $params['city'],
                'state' => $params['state']
            ]);

            $this->updateSubmitStatus($job);
        } catch (Exception $e) {

            $job->delete();

            throw new Exception("Something went wrong, please try again!");
        }
    }

    /**
     * @param int $user_id
     * @param int $job_id
     * @param array $params
     * @return JobBoard
     * @throws Exception
     */
    public function updateJobPost(int $user_id, int $job_id, array $params): JobBoard
    {
        $this->setUser($user_id);
        $job_post = $this->retrieveEmployerJobPost($user_id, $job_id);

        ['number_of_resource' => $no_of_resource] = $params;

        if (!$this->hasPaid($this->getUser())) {
            if ($this->exceedFreemiumResource($no_of_resource)) {
                throw new Exception("You have exceed the number of resource as a Freemium subscriber! Become a premium subscriber today.");
            }
        }

        if ($this->isApproved($job_post)) {
            $this->unApproveJob($job_post);
        }

        $job_post->update([
            'title' => $params['title'],
            'description' => $params['description'],
            'duration' => $params['duration'],
            'frequency' => $params['frequency'],
            'amount' => $params['amount'],
            'number_of_resource' => $no_of_resource,
            'supporting_images' => json_encode($params['images']),
            'address' => $params['address'],
            'city' => $params['city'],
            'state' => $params['state']
        ]);

        return $job_post->refresh();
    }

    public function completeJob($user_id, $job_id): JobBoard
    {
        $job_post = $this->retrieveEmployerJobPost($user_id, $job_id);

        $job_post->update([
            'is_completed' => true
        ]);

        return $job_post->refresh();
    }

    public function reviewWorker(int $job_id, int $employer_id, int $worker_id, array $params)
    {

    }

    public function updateSubmitStatus(JobBoard $jobBoard): void
    {
        $jobBoard->update([
            'is_submitted' => true
        ]);
    }

    public function hasPaid(User $user): bool
    {
        return $user->has_paid ?? false;
    }

    /**
     * @param $no_of_resource
     * @return bool
     * @throws Exception
     */
    public function exceedFreemiumResource($no_of_resource): bool
    {
        return ($no_of_resource > $this->getFreeEmployerNoOfResource()) ?? false;
    }

    /**
     * @param $user_id
     * @param $job_id
     * @return mixed
     * @throws Exception
     */
    public function retrieveEmployerJobPost($user_id, $job_id)
    {
        if(!$job_post = JobBoard::where('employer_id', $user_id)
            ->where('id', $job_id)->first())
            throw new Exception('Job post not found!');

        return $job_post;
    }

    public function isSubmitted(JobBoard $jobBoard)
    {
        return $jobBoard->is_submitted ?? false;
    }

    public function isApproved(JobBoard $jobBoard)
    {
        return $jobBoard->is_approved ?? false;
    }

    public function unApproveJob(JobBoard $jobBoard): void
    {
        $jobBoard->update([
            'is_approved' => false
        ]);
    }

    public function myJobs($user_id)
    {
        // return with pitches for
        return JobBoard::with('hiredWorker')->where('employer_id', $user_id)->get();
    }


    public function getSingleJob($id)
    {
        try {
            $jobs = JobBoard::where('id', $id)->firstOrFail();
            return [
                'payload' => $jobs
            ];
        } catch (Exception $e) {
            report($e);

            throw new Exception("Error getting selected job, please try again");
        }

    }

    public function getFileNameToStore($request)
    {
        //Get full filename
        $filenameWithExt = $request->file('avatar')->getClientOriginalName();

        //Extract filename only
        $filenameWithoutExt = pathinfo($filenameWithExt, PATHINFO_FILENAME);

        //Extract extenstion only
        $extension = $request->file('avatar')->getClientOriginalExtension();

        //Combine again with timestamp in the middle to differentiate files with same filename.
        $filenameToStore = $filenameWithoutExt . '_' . time() . '.' . $extension;

        return $filenameToStore;
    }

    public function getJobs()
    {
        $jobs = JobBoard::orderBy('id', 'desc')->paginate(10);
        return [
            'payload' => $jobs
        ];
    }

    /**
     * @throws Exception
     */
    public function getFreeEmployerNoOfResource(): int
    {
        if (!$general_settings = GeneralSetting::find(1)) {
            throw new Exception("Settings not available! Contact the admin.");
        }

        return $general_settings->no_of_free_resource;
    }

}

