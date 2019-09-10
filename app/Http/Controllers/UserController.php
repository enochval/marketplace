<?php


namespace App\Http\Controllers;


use App\Repositories\Contracts\IUserRepository;
use App\Repositories\Contracts\IWorkerRepository;
use App\Repositories\Contracts\IEmployerRepository;
use App\Utils\Rules;
use Exception;
use Illuminate\Support\Facades\Validator;

class UserController extends Controller
{
    /**
     * @var IEmployerRepository
     */
    private $employerRepository;
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
     * @param IEmployerRepository $employerRepository
     */
    public function __construct(IEmployerRepository $employerRepository, IWorkerRepository $workerRepository, IUserRepository $userRepository)
    {
        $this->employerRepository = $employerRepository;
        $this->workerRepository = $workerRepository;
        $this->userRepository = $userRepository;
    }

    public function updateProfile()
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

    public function bvnVerification()
    {
        $payload = request()->all();
        $validator = Validator::make($payload, Rules::get('BVN_VERIFICATION'));
        if ($validator->fails()) {
            return $this->validationErrors($validator->getMessageBag()->all());
        }

        try {
            $profile = $this->userRepository->workerSkills(auth()->id(), $payload);
            return $this->withData($profile);
        } catch (Exception $e) {
            return $this->error($e->getMessage());
        }
    }
}
