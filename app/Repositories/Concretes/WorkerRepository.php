<?php


namespace App\Repositories\Concretes;

use App\Jobs\SendWelcomeEmailJob;
use App\Jobs\UpdateLastLoginJob;
use App\Models\Role;
use App\Models\User;
use App\Jobs\SendVerificationEmailJob;
use App\Models\UsersVerification;
use App\Repositories\Contracts\IWorkerRepository;
use Carbon\Carbon;
use Exception;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class WorkerRepository implements IWorkerRepository
{
    private $worker;

    /**
     * @return mixed
     */
    public function getWorker(): User
    {
        return $this->worker;
    }

    /**
     * @param $worker_id
     */
    public function setWorker($worker_id): void
    {
        $this->worker = User::find($worker_id);
    }

    public function register(array $params) : void
    {
        try {
            [
                'email' => $email,
                'phone' => $phone,
                'first_name' => $first_name,
                'last_name' => $last_name,
                'password' => $password
            ] = $params;

            // Persist data
            $worker = User::create([
                'email' => $email,
                'phone' => $phone,
                'password' => bcrypt($password)
            ]);

            $worker_id = $worker->id;
            $this->setWorker($worker_id);

            $worker->profile()->create([
                'first_name' => $first_name,
                'last_name' => $last_name
            ]);

            // Attach worker role
            $this->assignWorkerRole();

            // Generate user verification token
            if (!$this->createVerificationToken())
                throw new Exception("Could not create verification token for the registered worker with user_id ${worker_id}");

            if (!$this->activate())
                throw new Exception("Could not activate worker user with user_id ${worker_id}");

            // Push this verification email to the queue (Basically sends this email to the registered worker)
            dispatch(new SendVerificationEmailJob($this->getWorker()));
        } catch (Exception $e) {
            report($e);

            //Delete the user to avoid duplicate entry.
            $this->worker->delete();

            // Return a custom error message back....
            throw new Exception("Unable to create user, please try again");
        }
    }

    public function activate(): bool
    {
        return $this->worker->update([
            'is_active' => true
        ]);
    }

    public function createVerificationToken(): UsersVerification
    {
        return $this->worker->verificationToken()->create([
            'token' => str_random(40)
        ]);
    }

    /**
     * @throws Exception
     */
    public function assignWorkerRole() : void
    {
        $workerRole = Role::where('name', 'worker')->first();

        if (!$workerRole) { throw new Exception("Unable to find worker role in the system"); }

        $this->worker->attachRole($workerRole);
    }

    public function getFullDetails(): User
    {
        return User::with(['profile', 'lastLogin'])->find($this->worker->id);
    }

    public function isConfirmed(): bool
    {
        return $this->getWorker()->is_confirmed ?? false;
    }

    public function isNotConfirmed(): bool
    {
        return !$this->worker->is_confirmed ?? true;
    }

    public function isBan(): bool
    {
        return $this->worker->is_ban ?? true;
    }

    public function updateLastLogin($user_id, $ip): void
    {
        $this->setWorker($user_id);
        $this->getWorker()->lastLogin()->updateOrCreate(
            ['user_id' => $this->worker->id],
            [
                'last_login_at' => Carbon::now()->toDateTimeString(),
                'last_login_ip' => $ip
            ]
        );
    }

    public function workHistory(int $user_id, array $params): User
    {
        $this->setWorker($user_id);

        $this->getWorker()->workHistory()->create([
            'employer' => $params['employer'],
            'position' => $params['position'],
            'start_date' => $params['start_date'],
            'end_date' => $params['end_date'],
        ]);

        return $this->getFullDetails();
    }

    public function workerSkills(int $user_id, array $params)
    {
        $this->setWorker($user_id);

        $this->getWorker()->workerSkill()->create([
            'names' => json_encode($params['names']),
            'category_id' => $params['category_id']
        ]);

        return $this->getFullDetails();
    }
}
