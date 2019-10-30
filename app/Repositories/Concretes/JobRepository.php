<?php


namespace App\Repositories\Concretes;

use App\Jobs\SendHireNotification;
use App\Models\AgentCustomer;
use App\Models\GeneralSetting;
use App\Models\JobPitch;
use App\Models\JobReview;
use App\Models\Role;
use Exception;
use App\Models\User;
use App\Models\JobBoard;
use App\Repositories\Contracts\IJobRepository;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use function Composer\Autoload\includeFile;

class JobRepository implements IJobRepository
{
    private $user;

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

        ['no_of_resource' => $no_of_resource] = $params;

        if (!$this->isPremium($this->getUser())) {
            if ($this->exceedFreemiumResource($no_of_resource)) {
                throw new Exception("You have exceed the number of resource as a Freemium subscriber! Become a premium subscriber today.");
            }
        }

        $job = JobBoard::create([
            'employer_id' => $user_id,
            'title' => $params['title'],
            'description' => $params['description'],
            'duration' => $params['duration'],
            'frequency' => $params['frequency'],
            'budget' => !empty($params['budget']) ? $params['budget'] : 'Negotiable',
            'no_of_resource' => $no_of_resource,
            'address' => $params['address'],
            'city_id' => $params['city_id'],
            'state' => $params['state'],
            'category_id' => $params['category_id'],
            'gender' => !empty($params['gender']) ? $params['gender'] : 'Any',
        ]);

        $this->updateSubmitStatus($job);
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

        ['no_of_resource' => $no_of_resource] = $params;

        if (!$this->isPremium($this->getUser())) {
            if ($this->exceedFreemiumResource($no_of_resource)) {
                throw new Exception("You have exceed the number of resource as a Freemium subscriber! Become a premium subscriber today.");
            }
        }

        if ($this->isApproved($job_post)) {
            $this->unApproveJob($job_id);
        }

        $job_post->update([
            'title' => $params['title'],
            'description' => $params['description'],
            'duration' => $params['duration'],
            'frequency' => $params['frequency'],
            'budget' => $params['budget'],
            'no_of_resource' => $no_of_resource,
            'address' => $params['address'],
            'city_id' => $params['city_id'],
            'state' => $params['state'],
            'category_id' => $params['category_id'],
            'gender' => $params['gender'],
        ]);

        return $job_post->refresh();
    }

    /**
     * @param $user_id
     * @param $job_id
     * @return JobBoard
     * @throws Exception
     */
    public function completeJob($user_id, $job_id): JobBoard
    {
        $job_post = $this->retrieveEmployerJobPost($user_id, $job_id);

        $job_post->update([
            'is_running' => false,
            'is_completed' => true
        ]);

        return $job_post->refresh();
    }

    /**
     * @param int $job_id
     * @param int $employer_id
     * @param int $worker_id
     * @param array $params
     * @return JobReview
     * @throws Exception
     */
    public function reviewWorker(int $job_id, int $employer_id, int $worker_id, array $params): JobReview
    {
        if (!$this->isCompleted($job_id))
            throw new Exception('This job is not completed!');

        if ($this->hasReviewed($job_id, $employer_id, $worker_id)) {
            throw new Exception('Worker has already been reviewed');
        }

        $review = JobReview::create([
            'job_id' => $job_id,
            'reviewer_id' => $employer_id,
            'reviewee_id' => $worker_id,
            'no_of_stars' => $params['no_of_stars'],
            'remark' => $params['remark']
        ]);

        return $review;
    }

    /**
     * @param int $job_id
     * @param int $employer_id
     * @param int $worker_id
     * @param array $params
     * @return JobReview
     * @throws Exception
     */
    public function reviewEmployer(int $job_id, int $worker_id, int $employer_id, array $params): JobReview
    {
        if (!$this->isCompleted($job_id))
            throw new Exception('This job is not completed!');

        if ($this->hasReviewed($job_id, $worker_id, $employer_id)) {
            throw new Exception('Employer has already been reviewed');
        }

        $review = JobReview::create([
            'job_id' => $job_id,
            'reviewer_id' => $worker_id,
            'reviewee_id' => $employer_id,
            'no_of_stars' => $params['no_of_stars'],
            'remark' => $params['remark']
        ]);

        return $review;
    }

    public function hasReviewed($job_id, $reviewer_id, $reviewee_id)
    {
        if (!$review = JobReview::where('job_id', $job_id)
            ->where('reviewer_id', $reviewer_id)
            ->where('reviewee_id', $reviewee_id)
            ->first()
        )
            return false;

        return true;
    }

    /**
     * @param $worker_id
     * @param $job_id
     * @param $params
     * @throws Exception
     */
    public function bidForJob($worker_id, $job_id, $params)
    {
        $this->setUser($worker_id);

        if (!$this->isPremium($this->getUser())) {
            if ($this->hasExceedFreeTrial($worker_id)) {
                throw new Exception('You have exceed your freemium subscription! Upgrade to premium!');
            }
        }

        if ($this->hasApplied($worker_id, $job_id)) {
            throw new Exception('You have already applied for this job');
        }

        JobPitch::create([
            'job_board_id' => $job_id,
            'worker_id' => $worker_id,
            'amount' => $params['amount'],
            'proposal' => $params['proposal'],
        ]);

        // don't know what to return here yet
    }

    /**
     * @param $job_id
     * @param $worker_id
     * @return JobPitch
     * @throws Exception
     */
    public function hireResource($job_id, $worker_id): JobPitch
    {
        if (!$job_pitch = JobPitch::where('job_board_id', $job_id)
            ->where('worker_id', $worker_id)
            ->first())
            throw new Exception('Job pitch was not found!');

        if ($this->isHired($worker_id, $job_id))
            throw new Exception('This worker is already hired');

        // check to see that it hasn't exceeded the no of resource
        if ($this->hasHiredRequiredResource($job_id))
            throw new Exception('You cannot hire this resource! you have hired all needed resource for this job.');

        $this->setUser($worker_id);

        if (!$this->isPremium($this->getUser())) {
            if ($this->hasExceedFreeTrial($worker_id)) {
                throw new Exception('Worker cannot be hired! Worker has exceed the free trial usage.');
            }
        }

        $job_pitch->update([
            'is_hired' => true
        ]);

        // Send a mail here to the worker that he has been hired
        dispatch(new SendHireNotification($this->getUser()));

        $this->updateHiredCount($job_id);

        if ($this->hasHiredRequiredResource($job_id)) {
            $this->updateRunningStatus($job_id);
        }

        return $this->getWorkerJobPitch($job_id, $worker_id);
    }

    public function isHired($worker_id, $job_id)
    {
        $job_pitch = $this->getWorkerJobPitch($job_id, $worker_id);
        return $job_pitch->is_hired ?? false;
    }

    public function updateSubmitStatus(JobBoard $jobBoard): void
    {
        $jobBoard->update([
            'is_submitted' => true
        ]);
    }

    public function isBvnVerified(User $user)
    {
        return $user->is_bvn_verified ?? false;
    }

    /**
     * @param $job_id
     * @throws Exception
     */
    public function updateRunningStatus($job_id)
    {
        $this->getJob($job_id)->update([
            'is_running' => true
        ]);
    }

    public function isPremium(User $user): bool
    {
        return $user->is_premium ?? false;
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
        if (!$job_post = JobBoard::with(['city', 'category'])
            ->where('employer_id', $user_id)
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

    /**
     * @param $job_id
     * @return bool|mixed
     * @throws Exception
     */
    public function isCompleted($job_id)
    {
        return $this->getJob($job_id)->is_completed ?? false;
    }

    public function approveJob($job_id): JobBoard
    {
        $job = $this->getJob($job_id);

        $job->update([
            'is_approved' => true
        ]);

        return $job->refresh();
    }

    public function unApproveJob($job_id): JobBoard
    {
        $job = $this->getJob($job_id);

        $job->update([
            'is_approved' => false
        ]);

        return $job->refresh();
    }

    public function getWorkerJobPitch($job_id, $worker_id)
    {
        return JobPitch::with('worker.profile:user_id,first_name,last_name,avatar,gender,state,bio,city_id')
            ->where('job_board_id', $job_id)
            ->where('worker_id', $worker_id)
            ->first();
    }

    public function getJobPitches($job_id)
    {
        return JobPitch::with(
            'worker.profile:user_id,first_name,last_name,avatar,gender,state,bio,city_id'
        )->where('job_board_id', $job_id)->get();
    }

    /**
     * @param $user_id
     * @param int $perPage
     * @param string $orderBy
     * @param string $sort
     * @return LengthAwarePaginator
     * @throws Exception
     */
    public function myJobs($user_id, $perPage = 15, $orderBy = 'created_at', $sort = 'desc')
    {
        $this->setUser($user_id);

        if ($this->getUser()->hasRole(Role::EMPLOYER)) {

            return JobBoard::with(['city', 'category'])
                ->where('employer_id', $user_id)
                ->orderBy($orderBy, $sort)
                ->paginate($perPage);

        } elseif ($this->getUser()->hasRole(Role::WORKER)) {

            return JobPitch::with('job.city')
                ->where('worker_id', $user_id)
                ->orderBy($orderBy, $sort)
                ->paginate($perPage);

        }

        throw new Exception('Unknown User!');
    }

    /**
     * @param $job_id
     * @return mixed
     * @throws Exception
     */
    public function hasHiredRequiredResource($job_id)
    {
        $job = $this->getJob($job_id);
        return ($job->no_of_resource == $job->hired_count) ?? false;
    }

    /**
     * @param $job_id
     * @throws Exception
     */
    public function updateHiredCount($job_id)
    {
        $job = $this->getJob($job_id);

        $job->update([
            'hired_count' => $job->hired_count + 1
        ]);
    }

    /**
     * @throws Exception
     */
    public function getFreeEmployerNoOfResource(): int
    {
        if (!$general_settings = GeneralSetting::find(1)) {
            throw new Exception("Settings not available! Contact the admin.");
        }

        return $general_settings->no_of_employer_free_resource;
    }

    /**
     * @return mixed
     * @throws Exception
     */
    public function getFreeWorkerNoOfJobs()
    {
        if (!$general_settings = GeneralSetting::find(1)) {
            throw new Exception("Settings not available! Contact the admin.");
        }

        return $general_settings->no_of_worker_free_trial;
    }

    /**
     * @param $worker_id
     * @return bool
     * @throws Exception
     */
    public function hasExceedFreeTrial($worker_id): bool
    {
        if (!$job_hired_count = JobPitch::where('worker_id', $worker_id)
            ->where('is_hired', true)
            ->get()->count())
            return false;

        return ($job_hired_count >= $this->getFreeWorkerNoOfJobs()) ?? false;
    }

    public function hasApplied($worker_id, $job_id)
    {
        if (!$job_pitch = JobPitch::where('worker_id', $worker_id)
            ->where('job_board_id', $job_id)
            ->first())
            return false;

        return true;
    }

    public function jobListing($perPage, $orderBy, $sort, $by_title = false, $by_location = false)
    {
        $job_listing = JobBoard::with(['city', 'category'])
            ->where('is_submitted', true)
            ->where('is_approved', true)
            ->where('is_running', false)
            ->where('is_completed', false);

        if ($by_title && !$by_location) {
            $job_listing
                ->where('title', 'like', '%' . $by_title . '%')
                ->orWhere('description', 'like', '%' . $by_title . '%');
        }

        if ($by_location && !$by_title) {
            $job_listing
                ->where('city_id', $by_location);
        }

        if ($by_title && $by_location) {
            $job_listing
                ->where('city_id', $by_location)
                ->where('title', 'like', '%' . $by_title . '%')
                ->orWhere('description', 'like', '%' . $by_title . '%');
        }

        if ($by_title || $by_location) {
            if (!$job_listing->count()) {
                return [];
            }
        }

        return $job_listing
            ->orderBy($orderBy, $sort)
            ->paginate($perPage);
    }

    public function allJobs($perPage = 15, $orderBy = 'created_at', $sort = 'desc')
    {
        return JobBoard::with(['city', 'category'])
            ->orderBy($orderBy, $sort)
            ->paginate($perPage);
    }

    /**
     * @param $job_id
     * @return mixed
     * @throws Exception
     */
    public function getJob($job_id): JobBoard
    {
        if (!$job = JobBoard::find($job_id))
            throw new Exception('Job board not found!');

        return $job;
    }

    public function jobReviews($job_id, $reviewee_id)
    {
        return JobReview::with('reviewer')
            ->where('job_id', $job_id)
            ->where('reviewee_id', $reviewee_id)
            ->get();
    }

    public function getJobsInMyArea($user_id, $perPage = 5, $orderBy = 'created_at', $sort = 'desc')
    {
        $this->setUser($user_id);

        $user_city_id = $this->getUser()->profile->city_id;

        return JobBoard::with(['city', 'category'])
            ->where('city_id', $user_city_id)
            ->where('is_submitted', true)
            ->where('is_approved', true)
            ->where('is_running', false)
            ->where('is_completed', false)
            ->orderBy($orderBy, $sort)
            ->paginate($perPage);
    }

    public function getTopJobs($perPage = 5)
    {
        return JobBoard::with(['city', 'category'])
            ->where('is_submitted', true)
            ->where('is_approved', true)
            ->where('is_running', false)
            ->where('is_completed', false)
            ->orderBy('budget', 'desc')
            ->paginate($perPage);
    }

    public function getUserNoOfCompletedJobs($user_id)
    {
        return JobPitch::where('worker_id', $user_id)
            ->where('is_hired', true)
            ->whereHas('job', function (Builder $query) {
                $query->where('is_completed', true)
                    ->where('is_running', false);
            })
            ->count();

    }

    public function getEmployerNoOfCompletedJobs($user_id)
    {
        return JobBoard::where('employer_id', $user_id)
            ->where('is_completed', true)
            ->where('is_running', false)
            ->count();
    }

    public function getAverageUserRating($user_id)
    {

    }

    public function getJobsThatMayInterestYou($user_id)
    {

    }

    public function getUserRunningJobs($user_id)
    {
        return JobPitch::where('worker_id', $user_id)
            ->where('is_hired', true)
            ->whereHas('job', function (Builder $query) {
                $query->where('is_completed', false)
                    ->where('is_running', true);
            })
            ->count();
    }

    public function getEmployerRunningJobs($user_id)
    {
        return JobBoard::where('employer_id', $user_id)
            ->where('is_completed', false)
            ->where('is_running', true)
            ->count();
    }

    public function getTotalAmountEarned($user_id)
    {
        $jobs = JobPitch::where('worker_id', $user_id)
            ->where('is_hired', true)
            ->whereHas('job', function (Builder $query) {
                $query->where('is_completed', true);
            })
            ->get();

        $total_amount = 0;

        foreach ($jobs as $job) {
            $total_amount += $job->amount;
        }

        return number_format($total_amount);
    }

    public function getTotalAmountSpent($user_id)
    {
        $jobs = JobBoard::where('employer_id', $user_id)
            ->where('is_completed', true)
            ->whereHas('pitch', function (Builder $query) {
                $query->where('is_hired', true);
            })
            ->get();

        $total_amount = 0;

        foreach ($jobs as $job) {
            foreach ($job->pitch as $pitch) {
                $total_amount += $pitch->amount;
            }
        }

        return number_format($total_amount);
    }

    public function getNoOfAgentRegisteredWorkers($user_id)
    {
        return AgentCustomer::where('user_id', $user_id)->count();
    }

    public function employerMonthlyExpenses($user_id)
    {
        $jobs = JobBoard::where('employer_id', $user_id)
            ->where('is_completed', true)
            ->whereHas('pitch', function (Builder $query) {
                $query->where('is_hired', true);
            })
            ->get();

        $monthly_expenses = [
            'Jan' => 0,
            'Feb' => 0,
            'Mar' => 0,
            'Apr' => 0,
            'May' => 0,
            'Jun' => 0,
            'Jul' => 0,
            'Aug' => 0,
            'Sept' => 0,
            'Oct' => 0,
            'Nov' => 0,
            'Dec' => 0,
        ];

        foreach ($jobs as $job) {
            foreach ($job->pitch as $pitch) {
                $month = $pitch->updated_at->month;
                if ($month == 1) {
                    $monthly_expenses['Jan'] += $pitch->amount;
                }if ($month == 2) {
                    $monthly_expenses['Feb'] += $pitch->amount;
                }if ($month == 3) {
                    $monthly_expenses['Mar'] += $pitch->amount;
                }if ($month == 4) {
                    $monthly_expenses['Apr'] += $pitch->amount;
                }if ($month == 5) {
                    $monthly_expenses['May'] += $pitch->amount;
                }if ($month == 6) {
                    $monthly_expenses['Jun'] += $pitch->amount;
                }if ($month == 7) {
                    $monthly_expenses['Jul'] += $pitch->amount;
                }if ($month == 8) {
                    $monthly_expenses['Aug'] += $pitch->amount;
                }if ($month == 9) {
                    $monthly_expenses['Sept'] += $pitch->amount;
                }if ($month == 10) {
                    $monthly_expenses['Oct'] += $pitch->amount;
                }if ($month == 11) {
                    $monthly_expenses['Nov'] += $pitch->amount;
                }if ($month == 12) {
                    $monthly_expenses['Dec'] += $pitch->amount;
                }
            }
        }

        return $monthly_expenses;
    }

    public function getWorkerMonthlyEarnings($user_id)
    {
        $jobs = JobPitch::where('worker_id', $user_id)
            ->where('is_hired', true)
            ->whereHas('job', function (Builder $query) {
                $query->where('is_completed', true);
            })
            ->get();

        $monthly_expenses = [
            'Jan' => 0,
            'Feb' => 0,
            'Mar' => 0,
            'Apr' => 0,
            'May' => 0,
            'Jun' => 0,
            'Jul' => 0,
            'Aug' => 0,
            'Sept' => 0,
            'Oct' => 0,
            'Nov' => 0,
            'Dec' => 0,
        ];

        foreach ($jobs as $pitch) {
            $month = $pitch->updated_at->month;

            if ($month == 1) {
                $monthly_expenses['Jan'] += $pitch->amount;
            }
            if ($month == 2) {
                $monthly_expenses['Feb'] += $pitch->amount;
            }
            if ($month == 3) {
                $monthly_expenses['Mar'] += $pitch->amount;
            }
            if ($month == 4) {
                $monthly_expenses['Apr'] += $pitch->amount;
            }
            if ($month == 5) {
                $monthly_expenses['May'] += $pitch->amount;
            }
            if ($month == 6) {
                $monthly_expenses['Jun'] += $pitch->amount;
            }
            if ($month == 7) {
                $monthly_expenses['Jul'] += $pitch->amount;
            }
            if ($month == 8) {
                $monthly_expenses['Aug'] += $pitch->amount;
            }
            if ($month == 9) {
                $monthly_expenses['Sept'] += $pitch->amount;
            }
            if ($month == 10) {
                $monthly_expenses['Oct'] += $pitch->amount;
            }
            if ($month == 11) {
                $monthly_expenses['Nov'] += $pitch->amount;
            }
            if ($month == 12) {
                $monthly_expenses['Dec'] += $pitch->amount;
            }
        }

        return $monthly_expenses;
    }


    public function dashboardStat($user_id)
    {
        $this->setUser($user_id);

        if ($this->getUser()->hasRole(Role::WORKER)) {
            return [
                'no_of_completed_jobs' => $this->getUserNoOfCompletedJobs($user_id),
                'amount_earned' => $this->getTotalAmountEarned($user_id),
                'active_jobs' => $this->getUserRunningJobs($user_id),
                'monthly_earnings' => $this->getWorkerMonthlyEarnings($user_id)
            ];
        } elseif ($this->getUser()->hasRole(Role::EMPLOYER)) {
            return [
                'no_of_completed_jobs' => $this->getEmployerNoOfCompletedJobs($user_id),
                'amount_spent' => $this->getTotalAmountSpent($user_id),
                'active_jobs' => $this->getEmployerRunningJobs($user_id),
                'monthly_expenses' => $this->employerMonthlyExpenses($user_id)
            ];
        } elseif ($this->getUser()->hasRole(Role::AGENT)) {
            return [
                'no_of_registered_workers' => $this->getNoOfAgentRegisteredWorkers($user_id)
            ];
        }
    }
}

