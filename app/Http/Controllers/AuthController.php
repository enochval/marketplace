<?php

namespace App\Http\Controllers;

use App\Repositories\Contracts\IWorkerRepository;
use App\Utils\Rules;
use Illuminate\Support\Facades\Validator;

class AuthController extends Controller
{
    /**
     * @var IWorkerRepository
     */
    private $workerRepo;

    /**
     * Create a new controller instance.
     *
     * @param IWorkerRepository $workerRepo
     */
    public function __construct(IWorkerRepository $workerRepo)
    {
        $this->workerRepo = $workerRepo;
    }

    public function registerWorker()
    {
        $validator = Validator::make(request()->all(), Rules::get('REGISTER'));
        if ($validator->fails()) {
            return $this->validationErrors($validator->getMessageBag()->all());
        }

        try {
            $register_worker = $this->workerRepo->register(request()->all());
            return $this->withData($register_worker);
        } catch (\Exception $e) {
            return $this->error($e->getMessage());
        }
    }
}
