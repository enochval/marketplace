<?php

namespace App\Http\Controllers;

use Exception;
use App\Utils\Rules;
use Illuminate\Support\Facades\Validator;
use App\Repositories\Contracts\IWorkerRepository;
use App\Repositories\Contracts\IEmployerRepository;
use App\Repositories\Contracts\IUserRepository;
use function GuzzleHttp\Promise\all;

class AuthController extends Controller
{
    /**
     * @var IWorkerRepository
     * @var IEmployerRepository
     * @var IUserRepository
     */
    private $workerRepo;
    private $employerRepo;
    private $userRepo;

    /**
     * Create a new controller instance.
     *
     * @param IWorkerRepository $workerRepo
     * @param IEmployerRepository $employerRepo
     * @param IUserRepository $userRepo
     */
    public function __construct(IWorkerRepository $workerRepo, IEmployerRepository $employerRepo, IUserRepository $userRepo)
    {
        $this->workerRepo = $workerRepo;
        $this->employerRepo = $employerRepo;
        $this->userRepo = $userRepo;
    }

    /**
     * @OA\Post(
     *     path="/worker-registration",
     *     operationId="workerRegistration",
     *     tags={"Authentication"},
     *     summary="Register a new worker user",
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
     *                  description="",
     *                  type="string",
     *              ),
     *              @OA\Property(
     *                  property="last_name",
     *                  description="",
     *                  type="string",
     *              ),
     *              @OA\Property(
     *                  property="email",
     *                  description="",
     *                  type="string",
     *              ),
     *              @OA\Property(
     *                  property="phone",
     *                  description="",
     *                  type="integer",
     *              ),
     *              @OA\Property(
     *                  property="password",
     *                  description="",
     *                  type="string",
     *              ),
     *              @OA\Property(
     *                  property="password_confirmation",
     *                  description="",
     *                  type="string",
     *              )
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
    public function registerWorker()
    {
        $validator = Validator::make(request()->all(), Rules::get('REGISTER_WORKER'));
        if ($validator->fails()) {
            return $this->validationErrors($validator->getMessageBag()->all());
        }

        try {
            $this->workerRepo->register(request()->all());
            return $this->withData("Registration successful, check your e-mail and kindly click to verify!");
        } catch (Exception $e) {
            return $this->error($e->getMessage());
        }
    }

    /**
     * @OA\Post(
     *     path="/confirm-email",
     *     operationId="verifyEmail",
     *     tags={"Authentication"},
     *     summary="Verify user's e-mail",
     *     description="",
     *     @OA\RequestBody(
     *       required=true,
     *       description="Request object",
     *       @OA\MediaType(
     *           mediaType="application/json",
     *           @OA\Schema(
     *              type="object",
     *              @OA\Property(
     *                  property="token",
     *                  description="Token",
     *                  type="string",
     *              )
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
    public function confirmEmail()
    {
        $payload = request()->only(['token']);

        $validator = Validator::make($payload, Rules::get('CONFIRM_EMAIL'));
        if ($validator->fails()) {
            return $this->validationErrors($validator->getMessageBag()->all());
        }

        ['token' => $token] = $payload;

        try {
            $this->userRepo->verifyEmail($token);
            return $this->success("E-mail successfully verified! Kindly login to access every opportunity Timbala has to offer!");
        } catch (Exception $e) {
            return $this->error($e->getMessage());
        }
    }

    /**
     * @OA\Post(
     *     path="/authenticate",
     *     operationId="login",
     *     tags={"Authentication"},
     *     summary="Authenticate existing user",
     *     description="",
     *     @OA\RequestBody(
     *       required=true,
     *       description="Request object",
     *       @OA\MediaType(
     *           mediaType="application/json",
     *           @OA\Schema(
     *              type="object",
     *              @OA\Property(
     *                  property="email",
     *                  description="Accepts email or phone",
     *                  type="string",
     *              ),
     *              @OA\Property(
     *                  property="password",
     *                  description="",
     *                  type="string",
     *              )
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
    public function authenticate()
    {
        $validator = Validator::make(request()->all(), Rules::get('AUTHENTICATE'));
        if ($validator->fails()) {
            return $this->validationErrors($validator->getMessageBag()->all());
        }

        if (is_numeric(request()->email)) {
            request()['phone'] = request()->email;
            $credentials = request()->only(['phone', 'password']);
        } else {
            $credentials = request()->only(['email', 'password']);
        }
        
        try {
            $auth = $this->userRepo->authenticate($credentials);
            return $this->withData($auth);
        } catch (Exception $e) {
            return $this->error($e->getMessage());
        }
    }

    /**
     * @OA\Post(
     *     path="/worker-registration",
     *     operationId="workerRegistration",
     *     tags={"Authentication"},
     *     summary="Register a new worker user",
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
     *                  description="",
     *                  type="string",
     *              ),
     *              @OA\Property(
     *                  property="last_name",
     *                  description="",
     *                  type="string",
     *              ),
     *              @OA\Property(
     *                  property="email",
     *                  description="",
     *                  type="string",
     *              ),
     *              @OA\Property(
     *                  property="phone",
     *                  description="",
     *                  type="integer",
     *              ),
     *              @OA\Property(
     *                  property="password",
     *                  description="",
     *                  type="string",
     *              ),
     *              @OA\Property(
     *                  property="password_confirmation",
     *                  description="",
     *                  type="string",
     *              )
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
    public function registerEmployer()
    {
        $validator = Validator::make(request()->all(), Rules::get('REGISTER_EMPLOYER'));
        if ($validator->fails()) {
            return $this->validationErrors($validator->getMessageBag()->all());
        }

        try {
            $this->employerRepo->register(request()->all());
            return $this->withData("Registration successful, check your e-mail and kindly click to verify!");
        } catch (Exception $e) {
            return $this->error($e->getMessage());
        }
    }
}

