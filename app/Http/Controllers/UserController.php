<?php


namespace App\Http\Controllers;


use App\Repositories\Contracts\IUserRepository;
use App\Repositories\Contracts\IWorkerRepository;
use App\Utils\Rules;
use Exception;
use Illuminate\Support\Facades\Validator;

class UserController extends Controller
{
    /**
     * @var IWorkerRepository
     */
    private $workerRepository;
    /**
     * @var IUserRepository
     */
    private $userRepository;

    /**
     * UserController constructor.
     * @param IWorkerRepository $workerRepository
     * @param IUserRepository $userRepository
     */
    public function __construct(IWorkerRepository $workerRepository, IUserRepository $userRepository)
    {
        $this->middleware('auth:api', ['except' => [
            'paymentCallback'
        ]]);

        $this->middleware('role:worker', ['only' => [
            'workHistory', 'workerSkills'
        ]]);

        $this->workerRepository = $workerRepository;
        $this->userRepository = $userRepository;
    }

    /**
     * @OA\Post(
     *     path="/profile",
     *     operationId="profile",
     *     tags={"User Management"},
     *     summary="Update user's profile",
     *     description="",
     *     @OA\RequestBody(
     *       required=true,
     *       description="Request object",
     *       @OA\MediaType(
     *           mediaType="application/json",
     *           @OA\Schema(
     *              type="object",
     *              @OA\Property(
     *                  property="first_name",
     *                  description="First Name",
     *                  type="string",
     *              ),
     *              @OA\Property(
     *                  property="last_name",
     *                  description="Last Name",
     *                  type="string",
     *              ),
     *              @OA\Property(
     *                  property="avatar",
     *                  description="Profile Picture, accepts base64 string",
     *                  type="string",
     *              ),
     *              @OA\Property(
     *                  property="gender",
     *                  description="Gender",
     *                  type="string",
     *              ),
     *              @OA\Property(
     *                  property="date_of_birth",
     *                  description="Date of birth",
     *                  type="string",
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
     *              @OA\Property(
     *                  property="bio",
     *                  description="Brief description about yourself",
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
    public function profile()
    {
        $payload = request()->all();
        $validator = Validator::make($payload, Rules::get('UPDATE_PROFILE'));
        if ($validator->fails()) {
            return $this->validationErrors($validator->getMessageBag()->all());
        }

        try {
            $profile = $this->userRepository->profile(auth()->id(), $payload);
            return $this->withData($profile);
        } catch (Exception $e) {
            return $this->error($e->getMessage());
        }
    }

    /**
     * @OA\Post(
     *     path="/work-history",
     *     operationId="workHistory",
     *     tags={"User Management"},
     *     summary="Create work history for workers",
     *     description="Can only be performed by a worker user",
     *     @OA\RequestBody(
     *       required=true,
     *       description="Request object",
     *       @OA\MediaType(
     *           mediaType="application/json",
     *           @OA\Schema(
     *              type="object",
     *              @OA\Property(
     *                  property="employer",
     *                  description="Employer",
     *                  type="string",
     *              ),
     *              @OA\Property(
     *                  property="positon",
     *                  description="Position",
     *                  type="string",
     *              ),
     *              @OA\Property(
     *                  property="start_date",
     *                  description="Start Date",
     *                  type="string",
     *              ),
     *              @OA\Property(
     *                  property="end_date",
     *                  description="End Date",
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
    public function workHistory()
    {
        $payload = request()->all();
        $validator = Validator::make($payload, Rules::get('WORK_HISTORY'));
        if ($validator->fails()) {
            return $this->validationErrors($validator->getMessageBag()->all());
        }

        try {
            $profile = $this->workerRepository->workHistory(auth()->id(), $payload);
            return $this->withData($profile);
        } catch (Exception $e) {
            return $this->error($e->getMessage());
        }
    }

    /**
     * @OA\Post(
     *     path="/worker-skill",
     *     operationId="WorkerSkill",
     *     tags={"User Management"},
     *     summary="Create worker skills",
     *     description="Can only be perform by a worker user",
     *     @OA\RequestBody(
     *       required=true,
     *       description="Request object",
     *       @OA\MediaType(
     *           mediaType="application/json",
     *           @OA\Schema(
     *              type="object",
     *              @OA\Property(
     *                  property="names",
     *                  description="Comma seperated values as skills",
     *                  type="array",
     *                  @OA\Items(
     *                      type="string"
     *                  )
     *              ),
     *              @OA\Property(
     *                  property="category_id",
     *                  description="Category ID",
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
    public function workerSkills()
    {
        $payload = request()->all();
        $validator = Validator::make($payload, Rules::get('WORKER_SKILLS'));
        if ($validator->fails()) {
            return $this->validationErrors($validator->getMessageBag()->all());
        }

        try {
            $profile = $this->workerRepository->workerSkills(auth()->id(), $payload);
            return $this->withData($profile);
        } catch (Exception $e) {
            return $this->error($e->getMessage());
        }
    }

    /**
     * @OA\Post(
     *     path="/verify-bvn",
     *     operationId="bvnVerification",
     *     tags={"User Management"},
     *     summary="Verify user's bvn",
     *     description="",
     *     @OA\RequestBody(
     *       required=true,
     *       description="Request object",
     *       @OA\MediaType(
     *           mediaType="application/json",
     *           @OA\Schema(
     *              type="object",
     *              @OA\Property(
     *                  property="bvn",
     *                  description="Bank verification number",
     *                  type="string",
     *              ),
     *              @OA\Property(
     *                  property="callback_url",
     *                  description="Callback URL to be redirected to when transaction is complete",
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
    public function bvnVerification()
    {
        $payload = request()->all();
        $validator = Validator::make($payload, Rules::get('BVN_VERIFICATION'));
        if ($validator->fails()) {
            return $this->validationErrors($validator->getMessageBag()->all());
        }

        [
            'bvn' => $bvn,
            'callback_url' => $callback_url
        ] = $payload;

        try {
            $response = $this->userRepository->bvnVerification(auth()->id(), $bvn, $callback_url);
            return $this->withData($response);
        } catch (Exception $e) {
            return $this->error($e->getMessage());
        }
    }

    public function paymentCallback()
    {
        $ref = request()->reference;
        try {
            $callback_url = $this->userRepository->callback($ref);
            return redirect($callback_url);
        } catch (Exception $e) {
            report($e);
            return redirect('https://timbala.now.sh?error='.$e->getMessage());
        }
    }

    /**
     * @OA\Get(
     *     path="/bvn-analysis",
     *     operationId="bvnAnalysis",
     *     tags={"User Management"},
     *     summary="Get bvn analysis and score",
     *     description="",
     *     @OA\Response(
     *         response="200",
     *         description="Returns response object",
     *         @OA\JsonContent()
     *     ),
     * )
     */
    public function getBvnAnalysis()
    {
        try {
            $response = $this->userRepository->getBvnAnalysis(auth()->id());
            return $this->withData($response);
        } catch (Exception $e) {
            return $this->error($e->getMessage());
        }
    }
}
