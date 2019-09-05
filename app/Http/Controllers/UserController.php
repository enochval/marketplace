<?php

namespace App\Http\Controllers;

use Exception;
use App\Utils\Rules;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Repositories\Contracts\IWorkerRepository;
use App\Repositories\Contracts\IEmployerRepository;
use App\Repositories\Contracts\IUserRepository;
use function GuzzleHttp\Promise\all;

class UserController extends Controller
{
  /**
   * @var IUserRepository
   */
  private $userRepo;
  private $employerRepo;

  /**
   * Create a new controller instance.
   * @param IUserRepository $userRepo
   */
  public function __construct(IUserRepository $userRepo, IEmployerRepository $employerRepo)
  {
    $this->userRepo = $userRepo;
    $this->employerRepo = $employerRepo;
  }
  
  /**
     * @OA\Post(
     *     path="/employer-update-profile",
     *     operationId="employerUpdateProfile",
     *     tags={"UpdateProfile"},
     *     summary="Update employer profile",
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
     *                  property="gender",
     *                  description="",
     *                  type="string",
     *              ),
     *              @OA\Property(
     *                  property="bank_verification_number",
     *                  description="",
     *                  type="integer",
     *              ),
     *              @OA\Property(
     *                  property="address",
     *                  description="",
     *                  type="string",
     *              ),
     *              @OA\Property(
     *                  property="city",
     *                  description="",
     *                  type="string",
     *              )
     *              @OA\Property(
     *                  property="state",
     *                  description="",
     *                  type="string",
     *              )
     *              @OA\Property(
     *                  property="date_of_birth",
     *                  description="",
     *                  type="string",
     *              )
     *              @OA\Property(
     *                  property="avatar",
     *                  description="",
     *                  type="string",
     *              )
     *              @OA\Property(
     *                  property="city",
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
  public function updateEmployer(Request $request)
  {
    $validator = Validator::make(request()->all(), Rules::get('UPDATE_EMPLOYER'));
    if ($validator->fails()) {
      return $this->validationErrors($validator->getMessageBag()->all());
    }

    try {
      $this->employerRepo->updateProfile($request);
      return $this->withData("Profile updated successfully!");
    } catch (Exception $e) {
      return $this->error($e->getMessage());
    }
  }


  
  public function editEmployer()
  {
    try{
      $employerProfile = $this->employerRepo->editProfile();
      return $this->withData($employerProfile);
    } catch (Exception $e) {
      return $this->error($e->getMessage());
    }
  }
}