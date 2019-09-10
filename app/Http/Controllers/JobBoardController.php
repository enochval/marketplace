<?php


namespace App\Http\Controllers;

use App\Repositories\Contracts\IJobRepository;
use App\Utils\Rules;
use Exception;
use Illuminate\Support\Facades\Validator;

class UserController extends Controller
{
  /**
   * @var IJobRepository
   */
  private $jobRepo;

  /**
   * UserController constructor.
   * @param IJobRepository $jobRepository
   */
  public function __construct(IJobRepository $jobRepo)
  {
    $this->jobRepo = $jobRepo;
  }

  public function index() 
  {
    try {
      $jobs = $this->jobRepo->getJobs();
      return [
        'payload' => $jobs
      ];
    } catch (Exception $e) {
      return $this->error($e->getMessage());
    }
  }


  public function postJob() 
  {
    
    $payload = request()->all();

    $validator = Validator::make($payload, Rules::get('POST_JOB'));
    if ($validator->fails()) {
      return $this->validationErrors($validator->getMessageBag()->all());
    }
    try {
      return $this->jobRepo->postJob($payload);
    } catch(Excection $e) {
      return $this->error($e->getMessage());
    }
  }

  public function getSingleJob($id)
  {
    try {
      $job = $this->jobRepo->getJob($id);
      return [
        'payload' => $job
      ];
    } catch (Exception $e) {
      return $this->error($e->getMessage());
    }
  }
}
