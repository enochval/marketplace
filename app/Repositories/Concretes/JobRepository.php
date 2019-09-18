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

        if (!$this->hasPaid($this->getUser())) {
            if ($this->hasUsedFreemiumSlot($user_id)) {
                throw new Exception("You have used up your Freemium slot! Become a premium subscriber today.");
            }
        }

        $job = JobBoard::create([
            'employer_id' => $user_id,
            'title' => $params['title'],
            'description' => $params['description'],
            'duration' => $params['duration'],
            'frequency' => $params['frequency'],
            'originating_amount' => $params['amount'],
            'supporting_images' => json_encode($params['images']),
            'address' => $params['address'],
            'city' => $params['city'],
            'state' => $params['state']
        ]);

        if ($job) {
            $this->updateSubmitStatus($job);
        }

        $job->delete();

        throw new Exception("Something went wrong, please try again!");
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

    public function hasUsedFreemiumSlot($employer_id): bool
    {
        if (!$date_of_last_post = $this->getDateOfLastPost($employer_id)) {
            return false;
        }
        return $date_of_last_post->addDays(30)->greaterThan(Carbon::now()) ?? false;
    }

    public function getDateOfLastPost($employer_id)
    {
        if (!$last_job = JobBoard::where('employer_id', $employer_id)->get()->last()) {
            return null;
        }
        return $last_job->created_at;
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

}

