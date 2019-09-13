<?php




// namespace App\Http\Controllers;

// use App\Repositories\Contracts\IJobRepository;
// use App\Utils\Rules;
// use Exception;
// use Illuminate\Support\Facades\Validator;

// class JobBoardController extends Controller
// {
//   /**
//    * @var IJobRepository
//    */
//   private $jobRepository;

//   /**
//    * JobBoardController constructor.
//    * @param IJobRepository $jobRepository
//    */
//   public function __construct(IJobRepository $jobRepository)
//   {
//     $this->jobRepository = $jobRepository;
//   }

//   public function index() 
//   {
//     try {
//       $jobs = $this->jobRepository->getJobs();
//       return [
//         'payload' => $jobs
//       ];
//     } catch (Exception $e) {
//       return $this->error($e->getMessage());
//     }
//   }


//   public function postJob() 
//   {
    
//     $payload = request()->all();

//     $validator = Validator::make($payload, Rules::get('POST_JOB'));
//     if ($validator->fails()) {
//       return $this->validationErrors($validator->getMessageBag()->all());
//     }
//     try {
//       return $this->jobRepository->postJob($payload);
//     } catch(Excection $e) {
//         return $this->error($e->getMessage());
//     }
//   }

//   public function getSingleJob($id)
//   {
//     try {
//       $job = $this->jobRepository->getSingleJob($id);
//       return [
//         'payload' => $job
//       ];
//     } catch (Exception $e) {
//         return $this->error($e->getMessage());
//     }
//   }
// }
