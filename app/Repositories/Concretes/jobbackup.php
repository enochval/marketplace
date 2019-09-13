<?php

// namespace App\Repositories\Concretes;

// use App\Models\JobBoard;
// use App\Jobs\SendJobPostEmailJob;
// use App\Repositories\Contracts\IJobRepository;
// use App\Repositories\Contracts\IEmployerRepository;
// use Carbon\Carbon;
// use Exception;
// use Illuminate\Database\Eloquent\ModelNotFoundException;
// use phpDocumentor\Reflection\Types\Object_;

// class JobRepository implements IJobRepository
// {

//   public function getJobs()
//   {
//     $jobs = JobBoard::orderBy('id', 'desc')->paginate(10);
//     return [
//       'payload' => $jobs
//     ];
//   }

//   public function postJob($params)
//   {
//     try {
//           [
//             'title' => $title,
//             'description' => $description,
//             'duraiton' => $duration,
//             'frequency' => $frequency,
//             'amount' => $amount,
//             'images' => $images,
//             'address' => $address,
//             'city' => $city,
//             'state' => $state,
//             'latitude' => $latitude,
//             'longitude' => $longitude
//           ] = $params;

//       // Persist data
//       $job = JobBoard::create([
//         'title' => $title,
//         'description' => $description,
//         'duraiton' => $duration,
//         'frequency' => $frequency,
//         'amount' => $amount,
//         'images' => $images,
//         'address' => $address,
//         'city' => $city,
//         'state' => $state,
//         'latitude' => $latitude,
//         'longitude' => $longitude
//       ]);

//       $emp = $this->employer->getEmployer();
//       dd($emp);

//       // Push notification email to queue
//       dispatch(new SendJobPostEmailJob($this->getEmployer()));
//     } catch(Exception $e) {
//         return $this->error($e->getMessage());
//     }
//   }

//   public function getSingleJob($id)
//   {
//     $job = JobBoard::find($id);
//     return [
//       'payload' => $job
//     ];
//   }
// }
