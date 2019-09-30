<?php


namespace App\Http\Controllers;


use App\Repositories\Contracts\IJobRepository;
use App\Utils\Rules;
use Exception;
use Illuminate\Http\JsonResponse;
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

        $this->middleware('role:super_admin|employer', ['only' => [
            'createJob', 'updateJob', 'getJobPitches', 'hireWorker',
            'reviewWorker', 'completeJob'
        ]]);

        $this->middleware('role:super_admin|worker', ['only' => [
            'bid', 'reviewEmployer', 'bidStatus'
        ]]);

        $this->middleware('role:super_admin|admin', ['only' => [
            'allJobs', 'approveJob', 'unApproveJob'
        ]]);

        $this->middleware('role:super_admin|employer|worker', ['only' => [
            'jobReviews', 'myJobs'
        ]]);

        $this->jobRepository = $jobRepository;
    }

    /**
     * @OA\Post(
     *     path="/job-board",
     *     operationId="createJjobBoard",
     *     tags={"Job Board Operations"},
     *     security={{"authorization_token": {}}},
     *     summary="Create a new job post",
     *     description="Can only be performed by a employer",
     *     @OA\RequestBody(
     *       required=true,
     *       description="Request object",
     *       @OA\MediaType(
     *           mediaType="application/json",
     *           @OA\Schema(
     *              type="object",
     *              @OA\Property(
     *                  property="title",
     *                  description="Title",
     *                  type="string",
     *              ),
     *              @OA\Property(
     *                  property="description",
     *                  description="Description",
     *                  type="string",
     *              ),
     *              @OA\Property(
     *                  property="duration",
     *                  description="Duration",
     *                  type="string",
     *              ),
     *              @OA\Property(
     *                  property="frequency",
     *                  description="Frequency",
     *                  type="string",
     *              ),
     *              @OA\Property(
     *                  property="amount",
     *                  description="Amount",
     *                  type="string",
     *              ),
     *              @OA\Property(
     *                  property="no_of_resource",
     *                  description="Number of workers needed",
     *                  type="string",
     *              ),
     *              @OA\Property(
     *                  property="supporting_images",
     *                  description="Supporting Images",
     *                  type="array",
     *                  @OA\Items(
     *                      type="string"
     *                  )
     *              ),
     *              @OA\Property(
     *                  property="address",
     *                  description="Address",
     *                  type="string",
     *              ),
     *              @OA\Property(
     *                  property="city",
     *                  description="City",
     *                  type="string",
     *              ),
     *              @OA\Property(
     *                  property="state",
     *                  description="State",
     *                  type="string",
     *              ),
     *           )
     *       )
     *     ),
     *     @OA\Response(
     *         response="200",
     *         description="Returns response object",
     *         @OA\JsonContent()
     *     ),
     *     @OA\Response(
     *          response="422",
     *          description="Error: Unproccessble Entity. When required parameters were not supplied correctly.",
     *          @OA\JsonContent()
     *     )
     * )
     */
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

    /**
     * @OA\Patch(
     *     path="/job-board/{job_id}/update",
     *     operationId="updateJobBoard",
     *     tags={"Job Board Operations"},
     *     security={{"authorization_token": {}}},
     *     summary="Update a job post",
     *     description="Can only be performed by a employer",
     *     @OA\Parameter(
     *         name="job_id",
     *         in="path",
     *         description="ID of job to update",
     *         required=true,
     *         @OA\Schema(
     *             type="integer",
     *             format="int64"
     *         )
     *     ),
     *     @OA\RequestBody(
     *       required=true,
     *       description="Request object",
     *       @OA\MediaType(
     *           mediaType="application/json",
     *           @OA\Schema(
     *              type="object",
     *              @OA\Property(
     *                  property="title",
     *                  description="Title",
     *                  type="string",
     *              ),
     *              @OA\Property(
     *                  property="description",
     *                  description="Description",
     *                  type="string",
     *              ),
     *              @OA\Property(
     *                  property="duration",
     *                  description="Duration",
     *                  type="string",
     *              ),
     *              @OA\Property(
     *                  property="frequency",
     *                  description="Frequency",
     *                  type="string",
     *              ),
     *              @OA\Property(
     *                  property="amount",
     *                  description="Amount",
     *                  type="string",
     *              ),
     *              @OA\Property(
     *                  property="no_of_resource",
     *                  description="Number of workers needed",
     *                  type="string",
     *              ),
     *              @OA\Property(
     *                  property="supporting_images",
     *                  description="Supporting Images",
     *                  type="array",
     *                  @OA\Items(
     *                      type="string"
     *                  )
     *              ),
     *              @OA\Property(
     *                  property="address",
     *                  description="Address",
     *                  type="string",
     *              ),
     *              @OA\Property(
     *                  property="city",
     *                  description="City",
     *                  type="string",
     *              ),
     *              @OA\Property(
     *                  property="state",
     *                  description="State",
     *                  type="string",
     *              ),
     *           )
     *       )
     *     ),
     *     @OA\Response(
     *         response="200",
     *         description="Returns response object",
     *         @OA\JsonContent()
     *     ),
     *     @OA\Response(
     *          response="422",
     *          description="Error: Unproccessble Entity. When required parameters were not supplied correctly.",
     *          @OA\JsonContent()
     *     )
     * )
     * @param $job_id
     * @return JsonResponse
     */
    public function updateJob($job_id)
    {
        $validator = Validator::make(request()->all(), Rules::get('CREATE_JOB'));
        if ($validator->fails()) {
            return $this->validationErrors($validator->getMessageBag()->all());
        }

        try {

            $job = $this->jobRepository->updateJobPost(auth()->id(), $job_id, request()->all());
            return $this->withData($job);

        } catch(Exception $e) {
            return $this->error($e->getMessage());
        }
    }

    /**
     * @OA\Post(
     *     path="/job-board/{job_id}/bid",
     *     operationId="BidForJob",
     *     tags={"Job Board Operations"},
     *     security={{"authorization_token": {}}},
     *     summary="Bid for a Job",
     *     description="Can only be performed by a worker",
     *     @OA\Parameter(
     *         name="job_id",
     *         in="path",
     *         description="ID of job to update",
     *         required=true,
     *         @OA\Schema(
     *             type="integer",
     *             format="int64"
     *         )
     *     ),
     *     @OA\RequestBody(
     *       required=true,
     *       description="Request object",
     *       @OA\MediaType(
     *           mediaType="application/json",
     *           @OA\Schema(
     *              type="object",
     *              @OA\Property(
     *                  property="amount",
     *                  description="Amount",
     *                  type="string",
     *              ),
     *           )
     *       )
     *     ),
     *     @OA\Response(
     *         response="200",
     *         description="Returns response object",
     *         @OA\JsonContent()
     *     ),
     *     @OA\Response(
     *          response="422",
     *          description="Error: Unproccessble Entity. When required parameters were not supplied correctly.",
     *          @OA\JsonContent()
     *     )
     * )
     * @param $job_id
     * @return JsonResponse
     */
    public function bid($job_id)
    {
        $validator = Validator::make(request()->all(), Rules::get('BID'));
        if ($validator->fails()) {
            return $this->validationErrors($validator->getMessageBag()->all());
        }

        try {

            $this->jobRepository->bidForJob(auth()->id(), $job_id, request()->all());

            return $this->success('Bidding successful.');

        } catch(Exception $e) {
            return $this->error($e->getMessage());
        }
    }

    /**
     * @OA\Get(
     *     path="/job-board/{job_id}/pitches",
     *     operationId="getJobPitches",
     *     tags={"Job Board Operations"},
     *     security={{"authorization_token": {}}},
     *     summary="Get all pitches of a job",
     *     description="Can only be performed by a employer",
     *     @OA\Parameter(
     *         name="job_id",
     *         in="path",
     *         description="ID of job to update",
     *         required=true,
     *         @OA\Schema(
     *             type="integer",
     *             format="int64"
     *         )
     *     ),
     *     @OA\Response(
     *         response="200",
     *         description="Returns response object",
     *         @OA\JsonContent()
     *     ),
     * )
     * @param $job_id
     * @return JsonResponse
     */
    public function getJobPitches($job_id)
    {
        try {
            $pitches = $this->jobRepository->getJobPitches($job_id);

            return $this->withData($pitches);
        } catch(Exception $e) {
            return $this->error($e->getMessage());
        }
    }

    /**
     * @OA\Patch(
     *     path="/job-board/{job_id}/hire-worker/{worker_id}",
     *     operationId="hireWorker",
     *     tags={"Job Board Operations"},
     *     security={{"authorization_token": {}}},
     *     summary="Hire a worker",
     *     description="Can only be performed by a employer",
     *     @OA\Parameter(
     *         name="job_id",
     *         in="path",
     *         description="ID of job to update",
     *         required=true,
     *         @OA\Schema(
     *             type="integer",
     *             format="int64"
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="worker_id",
     *         in="path",
     *         description="ID of worker to hire",
     *         required=true,
     *         @OA\Schema(
     *             type="integer",
     *             format="int64"
     *         )
     *     ),
     *     @OA\Response(
     *         response="200",
     *         description="Returns response object",
     *         @OA\JsonContent()
     *     )
     * )
     * @param $worker_id
     * @param $job_id
     * @return JsonResponse
     */
    public function hireWorker($worker_id, $job_id)
    {
        try {

            $job = $this->jobRepository->hireResource($job_id, $worker_id);

            return $this->withData($job);

        } catch (Exception $e) {
            return $this->error($e->getMessage());
        }
    }

    /**
     * @OA\Patch(
     *     path="/job-board/{job_id}/review-worker/{worker_id}",
     *     operationId="revieweWorker",
     *     tags={"Job Board Operations"},
     *     security={{"authorization_token": {}}},
     *     summary="Review a worker",
     *     description="Can only be performed by a employer",
     *     @OA\Parameter(
     *         name="job_id",
     *         in="path",
     *         description="ID of job to update",
     *         required=true,
     *         @OA\Schema(
     *             type="integer",
     *             format="int64"
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="worker_id",
     *         in="path",
     *         description="ID of worker to review",
     *         required=true,
     *         @OA\Schema(
     *             type="integer",
     *             format="int64"
     *         )
     *     ),
     *     @OA\RequestBody(
     *       required=true,
     *       description="Request object",
     *       @OA\MediaType(
     *           mediaType="application/json",
     *           @OA\Schema(
     *              type="object",
     *              @OA\Property(
     *                  property="no_of_stars",
     *                  description="No of stars",
     *                  type="string",
     *              ),
     *              @OA\Property(
     *                  property="remark",
     *                  description="Remarks",
     *                  type="string",
     *              ),
     *           )
     *       )
     *     ),
     *     @OA\Response(
     *         response="200",
     *         description="Returns response object",
     *         @OA\JsonContent()
     *     )
     * )
     * @param $worker_id
     * @param $job_id
     * @return JsonResponse
     */
    public function reviewWorker($worker_id, $job_id)
    {
        $validator = Validator::make(request()->all(), Rules::get('JOB_REVIEW'));
        if ($validator->fails()) {
            return $this->validationErrors($validator->getMessageBag()->all());
        }

        try {
            $job = $this->jobRepository->reviewWorker($job_id, auth()->id(), $worker_id, request()->all());

            return $this->withData($job);

        } catch (Exception $e) {
            return $this->error($e->getMessage());
        }
    }

    /**
     * @OA\Patch(
     *     path="/job-board/{job_id}/review-employer/{employer_id}",
     *     operationId="reviewEmployer",
     *     tags={"Job Board Operations"},
     *     security={{"authorization_token": {}}},
     *     summary="Review an employer",
     *     description="Can only be performed by a worker",
     *     @OA\Parameter(
     *         name="job_id",
     *         in="path",
     *         description="ID of job to update",
     *         required=true,
     *         @OA\Schema(
     *             type="integer",
     *             format="int64"
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="employer_id",
     *         in="path",
     *         description="ID of employer to review",
     *         required=true,
     *         @OA\Schema(
     *             type="integer",
     *             format="int64"
     *         )
     *     ),
     *     @OA\RequestBody(
     *       required=true,
     *       description="Request object",
     *       @OA\MediaType(
     *           mediaType="application/json",
     *           @OA\Schema(
     *              type="object",
     *              @OA\Property(
     *                  property="no_of_stars",
     *                  description="No of stars",
     *                  type="string",
     *              ),
     *              @OA\Property(
     *                  property="remark",
     *                  description="Remarks",
     *                  type="string",
     *              ),
     *           )
     *       )
     *     ),
     *     @OA\Response(
     *         response="200",
     *         description="Returns response object",
     *         @OA\JsonContent()
     *     )
     * )
     * @param $employer_id
     * @param $job_id
     * @return JsonResponse
     */
    public function reviewEmployer($employer_id, $job_id)
    {
        $validator = Validator::make(request()->all(), Rules::get('JOB_REVIEW'));
        if ($validator->fails()) {
            return $this->validationErrors($validator->getMessageBag()->all());
        }

        try {
            $job = $this->jobRepository->reviewEmployer($job_id, auth()->id(), $employer_id, request()->all());

            return $this->withData($job);

        } catch (Exception $e) {
            return $this->error($e->getMessage());
        }
    }

    /**
     * @OA\Get(
     *     path="/job-board/{job_id}/reviews",
     *     operationId="getJobReviews",
     *     tags={"Job Board Operations"},
     *     security={{"authorization_token": {}}},
     *     summary="Get my reviews of a job",
     *     description="Can only be performed by a employer and worker",
     *     @OA\Parameter(
     *         name="job_id",
     *         in="path",
     *         description="ID of job to update",
     *         required=true,
     *         @OA\Schema(
     *             type="integer",
     *             format="int64"
     *         )
     *     ),
     *     @OA\Response(
     *         response="200",
     *         description="Returns response object",
     *         @OA\JsonContent()
     *     ),
     * )
     * @param $job_id
     * @return JsonResponse
     */
    public function jobReviews($job_id)
    {
        try {
            $job = $this->jobRepository->jobReviews($job_id, auth()->id());
            return $this->withData($job);

        } catch (Exception $e) {
            return $this->error($e->getMessage());
        }
    }

    /**
     * @OA\Patch(
     *     path="/job-board/{job_id}/complete",
     *     operationId="completeJob",
     *     tags={"Job Board Operations"},
     *     security={{"authorization_token": {}}},
     *     summary="Complete a job",
     *     description="Can only be performed by a employer",
     *     @OA\Parameter(
     *         name="job_id",
     *         in="path",
     *         description="ID of job to update",
     *         required=true,
     *         @OA\Schema(
     *             type="integer",
     *             format="int64"
     *         )
     *     ),
     *     @OA\Response(
     *         response="200",
     *         description="Returns response object",
     *         @OA\JsonContent()
     *     )
     * )
     * @param $job_id
     * @return JsonResponse
     */
    public function completeJob($job_id)
    {
        try {
            $job = $this->jobRepository->completeJob(auth()->id(), $job_id);
            return $this->withData($job);

        } catch (Exception $e) {
            return $this->error($e->getMessage());
        }
    }

    /**
     * @OA\Get(
     *     path="/job-listing",
     *     operationId="jobListing",
     *     tags={"Job Board Operations"},
     *     security={{"authorization_token": {}}},
     *     summary="Get all job listing",
     *     description="Can only be performed by a worker",
     *     @OA\Parameter(
     *         name="per_page",
     *         in="query",
     *         description="Number per page",
     *         required=true,
     *         @OA\Schema(
     *             type="integer",
     *             format="int64"
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="order_by",
     *         in="query",
     *         description="Order by a column",
     *         required=true,
     *         @OA\Schema(
     *             type="string",
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="sort",
     *         in="query",
     *         description="desc or asc",
     *         required=true,
     *         @OA\Schema(
     *             type="string",
     *         )
     *     ),
     *     @OA\Response(
     *         response="200",
     *         description="Returns response object",
     *         @OA\JsonContent()
     *     ),
     * )
     * @return JsonResponse
     */
    public function jobListing()
    {
        try {
            $payload = request()->all();
            $perPage = request()->has('per_page') ? $payload['per_page'] : 15;
            $orderBy = request()->has('order_by') ? $payload['order_by'] : 'created_at';
            $sort = request()->has('sort') ? $payload['sort'] : 'desc';


            $jobs = $this->jobRepository->jobListing($perPage, $orderBy, $sort);

            return $this->withData($jobs);

        } catch (Exception $e) {
            return $this->error($e->getMessage());
        }
    }

    /**
     * @OA\Get(
     *     path="/admin/jobs",
     *     operationId="allJobs",
     *     tags={"Admin Operations"},
     *     security={{"authorization_token": {}}},
     *     summary="Get all jobs",
     *     description="Can only be performed by an admin",
     *     @OA\Parameter(
     *         name="per_page",
     *         in="query",
     *         description="Number per page",
     *         required=true,
     *         @OA\Schema(
     *             type="integer",
     *             format="int64"
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="order_by",
     *         in="query",
     *         description="Order by a column",
     *         required=true,
     *         @OA\Schema(
     *             type="string",
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="sort",
     *         in="query",
     *         description="desc or asc",
     *         required=true,
     *         @OA\Schema(
     *             type="string",
     *         )
     *     ),
     *     @OA\Response(
     *         response="200",
     *         description="Returns response object",
     *         @OA\JsonContent()
     *     ),
     * )
     * @return JsonResponse
     */
    public function allJobs()
    {
        try {
            $payload = request()->all();
            $perPage = request()->has('per_page') ? $payload['per_page'] : 15;
            $orderBy = request()->has('order_by') ? $payload['order_by'] : 'created_at';
            $sort = request()->has('sort') ? $payload['sort'] : 'desc';


            $jobs = $this->jobRepository->allJobs($perPage, $orderBy, $sort);

            return $this->withData($jobs);

        } catch (Exception $e) {
            return $this->error($e->getMessage());
        }
    }

    /**
     * @OA\Patch(
     *     path="/admin/jobs/{job_id}/approve",
     *     operationId="approvejob",
     *     tags={"Admin Operations"},
     *     security={{"authorization_token": {}}},
     *     summary="Approves a job",
     *     description="Can only be performed by a admin",
     *     @OA\Parameter(
     *         name="job_id",
     *         in="path",
     *         description="ID of job to update",
     *         required=true,
     *         @OA\Schema(
     *             type="integer",
     *             format="int64"
     *         )
     *     ),
     *     @OA\Response(
     *         response="200",
     *         description="Returns response object",
     *         @OA\JsonContent()
     *     )
     * )
     * @param $job_id
     * @return JsonResponse
     */
    public function approveJob($job_id)
    {
        try {
            $job = $this->jobRepository->approveJob($job_id);
            return $this->withData($job);

        } catch (Exception $e) {
            return $this->error($e->getMessage());
        }
    }

    /**
     * @OA\Patch(
     *     path="/admin/jobs/{job_id}/reverse-approval",
     *     operationId="reversedApproval",
     *     tags={"Admin Operations"},
     *     security={{"authorization_token": {}}},
     *     summary="Reverse approval of a job",
     *     description="Can only be performed by a admin",
     *     @OA\Parameter(
     *         name="job_id",
     *         in="path",
     *         description="ID of job to update",
     *         required=true,
     *         @OA\Schema(
     *             type="integer",
     *             format="int64"
     *         )
     *     ),
     *     @OA\Response(
     *         response="200",
     *         description="Returns response object",
     *         @OA\JsonContent()
     *     )
     * )
     * @param $job_id
     * @return JsonResponse
     */
    public function unApproveJob($job_id)
    {
        try {
            $job = $this->jobRepository->unApproveJob($job_id);
            return $this->withData($job);

        } catch (Exception $e) {
            return $this->error($e->getMessage());
        }
    }

    /**
     * @OA\Get(
     *     path="/job-board",
     *     operationId="myJobs",
     *     tags={"Job Board Operations"},
     *     security={{"authorization_token": {}}},
     *     summary="Get my jobs",
     *     description="Can only be performed by a worker and employer",
     *     @OA\Parameter(
     *         name="per_page",
     *         in="query",
     *         description="Number per page",
     *         required=true,
     *         @OA\Schema(
     *             type="integer",
     *             format="int64"
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="order_by",
     *         in="query",
     *         description="Order by a column",
     *         required=true,
     *         @OA\Schema(
     *             type="string",
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="sort",
     *         in="query",
     *         description="desc or asc",
     *         required=true,
     *         @OA\Schema(
     *             type="string",
     *         )
     *     ),
     *     @OA\Response(
     *         response="200",
     *         description="Returns response object",
     *         @OA\JsonContent()
     *     ),
     * )
     * @return JsonResponse
     */
    public function myJobs()
    {
        $payload = request()->all();
        $perPage = request()->has('per_page') ? $payload['per_page'] : 15;
        $orderBy = request()->has('order_by') ? $payload['order_by'] : 'created_at';
        $sort = request()->has('sort') ? $payload['sort'] : 'desc';

        try {
            $job = $this->jobRepository->myJobs(auth()->id(), $perPage, $orderBy, $sort);
            return $this->withData($job);

        } catch (Exception $e) {
            return $this->error($e->getMessage());
        }
    }

    /**
     * @OA\Get(
     *     path="/dashboard-stat",
     *     operationId="dashboardStat",
     *     tags={"Common"},
     *     security={{"authorization_token": {}}},
     *     summary="Get dashboard metrics",
     *     description="",
     *     @OA\Response(
     *         response="200",
     *         description="Returns response object",
     *         @OA\JsonContent()
     *     ),
     * )
     * @return JsonResponse
     */
    public function dashboardStat()
    {

        try {
            $data = $this->jobRepository->dashboardStat(auth()->id());
            return $this->withData($data);

        } catch (Exception $e) {
            return $this->error($e->getMessage());
        }
    }

    /**
     * @OA\Get(
     *     path="/top-jobs",
     *     operationId="topJobs",
     *     tags={"Job Board Operations"},
     *     security={{"authorization_token": {}}},
     *     summary="Get top jobs",
     *     description="",
     *     @OA\Parameter(
     *         name="per_page",
     *         in="query",
     *         description="Number per page",
     *         required=true,
     *         @OA\Schema(
     *             type="integer",
     *             format="int64"
     *         )
     *     ),
     *     @OA\Response(
     *         response="200",
     *         description="Returns response object",
     *         @OA\JsonContent()
     *     ),
     * )
     * @return JsonResponse
     */
    public function topJobs()
    {
        $payload = request()->all();
        $perPage = request()->has('per_page') ? $payload['per_page'] : 5;

        try {
            $data = $this->jobRepository->getTopJobs($perPage);
            return $this->withData($data);

        } catch (Exception $e) {
            return $this->error($e->getMessage());
        }
    }

    /**
     * @OA\Get(
     *     path="/my-area-jobs",
     *     operationId="myAreaJobs",
     *     tags={"Job Board Operations"},
     *     security={{"authorization_token": {}}},
     *     summary="Get jobs in my area",
     *     description="",
     *     @OA\Parameter(
     *         name="per_page",
     *         in="query",
     *         description="Number per page",
     *         required=true,
     *         @OA\Schema(
     *             type="integer",
     *             format="int64"
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="order_by",
     *         in="query",
     *         description="Order by a column",
     *         required=true,
     *         @OA\Schema(
     *             type="string",
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="sort",
     *         in="query",
     *         description="desc or asc",
     *         required=true,
     *         @OA\Schema(
     *             type="string",
     *         )
     *     ),
     *     @OA\Response(
     *         response="200",
     *         description="Returns response object",
     *         @OA\JsonContent()
     *     ),
     * )
     * @return JsonResponse
     */
    public function myAreaJobs()
    {
        $payload = request()->all();
        $perPage = request()->has('per_page') ? $payload['per_page'] : 5;
        $orderBy = request()->has('order_by') ? $payload['order_by'] : 'created_at';
        $sort = request()->has('sort') ? $payload['sort'] : 'desc';

        try {
            $data = $this->jobRepository->getJobsInMyArea(auth()->id(), $perPage, $orderBy, $sort);
            return $this->withData($data);

        } catch (Exception $e) {
            return $this->error($e->getMessage());
        }
    }

    /**
     * @OA\Get(
     *     path="/bid-status/{job_id}",
     *     operationId="bidStatus",
     *     tags={"Job Board Operations"},
     *     security={{"authorization_token": {}}},
     *     summary="Check if a worker has applied for a job",
     *     description="",
     *      @OA\Parameter(
     *         name="job_id",
     *         in="path",
     *         description="The Job id",
     *         required=true,
     *         @OA\Schema(
     *             type="integer",
     *             format="int64"
     *         )
     *     ),
     *     @OA\Response(
     *         response="200",
     *         description="Returns response object",
     *         @OA\JsonContent()
     *     ),
     * )
     * @param $job_id
     * @return JsonResponse
     */
    public function bidStatus($job_id)
    {
        try {
            $data = $this->jobRepository->hasApplied(auth()->id(), $job_id);
            return $this->withData($data);

        } catch (Exception $e) {
            return $this->error($e->getMessage());
        }
    }
}
