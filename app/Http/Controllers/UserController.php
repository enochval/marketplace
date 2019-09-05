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
   * 
   */
  public function updateEmployer(Request $request)
  {
    $validator = Validator::make(request()->all(), Rules::get('UPDATE_EMPLOYER'));
    if ($validator->fails()) {
      return $this->validationErrors($validator->getMessageBag()->all());
    }

    try {
      $this->employerRepo->updateProfile($request);
      return $this->withData("Profile update successful!");
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