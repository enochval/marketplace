<?php


namespace App\Repositories\Contracts;
interface IJobRepository
{
    public function createJob(int $user_id, array $params);

    public function updateJobPost(int $user_id, int $job_id, array $params);

    public function bidForJob($worker_id, $job_id, $params);

    public function getJobPitches($job_id);

    public function myJobs($user_id, $perPage = 15, $orderBy = 'created_at', $sort = 'desc');

    public function hireResource($job_id, $worker_id);

    public function jobListing($perPage, $orderBy, $sort);

    public function allJobs($perPage, $orderBy, $sort);

    public function reviewWorker(int $job_id, int $employer_id, int $worker_id, array $params);

    public function reviewEmployer(int $job_id, int $employer_id, int $worker_id, array $params);

    public function jobReviews($job_id, $reviewee_id);

    public function completeJob($user_id, $job_id);

    public function approveJob($job_id);

    public function unApproveJob($job_id);
}
