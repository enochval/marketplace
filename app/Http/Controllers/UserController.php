<?php

namespace App\Http\Controllers;

use Exception;
use App\Utils\Rules;
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
  public function updateEmployer()
  {
    $validator = Validator::make(request()->all(), Rules::get('UPDATE_EMPLOYER'));
    if ($validator->fails()) {
      return $this->validationErrors($validator->getMessageBag()->all());
    }

    try {
      $this->employerRepo->updateProfile(request()->all());
      return $this->withData("Registration successful, check your e-mail and kindly click to verify!");
    } catch (Exception $e) {
      return $this->error($e->getMessage());
    }
  }

  public function editEmployer()
  {
    try{
      $this->employerRepo->editProfile();
    } catch (Exception $e) {
      return $this->error($e->getMessage());
    }
  }
}