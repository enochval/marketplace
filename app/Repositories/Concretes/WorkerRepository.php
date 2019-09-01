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
use phpDocumentor\Reflection\Types\Object_;

class WorkerRepository implements IWorkerRepository
{
    private $worker;

    /**
     * @return mixed
     */
    public function getWorker()
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

    public function register($params) : void
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
            if (!$this->createVerificationToken()) { throw new Exception("Could not create verification token for the registered worker with user_id ${worker_id}"); }

            if (!$this->activate()) { throw new Exception("Could not activate worker user with user_id ${worker_id}"); }

            // Push this verification email to the queue (Basically sends this email to the registered worker)
            dispatch(new SendVerificationEmailJob($this->getWorker()));
        } catch (Exception $e) {
            // Log the actual error to your logger... that's $e->getMessage()...

            //Delete the user to avoid duplicate entry.
            $this->worker->delete();

            // Return a custom error message back....
            throw new Exception("Unable to create user, please try again");
        }
    }

    public function activate()
    {
        return $this->worker->update([
            'is_active' => true
        ]);
    }

    public function createVerificationToken()
    {
        return $this->worker->verificationToken()->create([
            'token' => str_random(40)
        ]);
    }

    public function assignWorkerRole() : void
    {
        $workerRole = Role::where('name', 'worker')->first();

        if (!$workerRole) { throw new Exception("Unable to find worker role in the system"); }

        $this->worker->attachRole($workerRole);
    }

    public function getFullDetails()
    {
        return User::with(['profile', 'lastLogin'])->find($this->worker->id);
    }

    public function verifyEmail($token) : void
    {
        try {
            $this->setUserWithToken($token);

            if ($this->isConfirmed()) { throw new Exception('User\'s e-mail is already verified! Kindly proceed to login'); }

            if (!$this->confirmWorker()) { throw new Exception("Could not confirm worker with user_id " . $this->worker->id); }

            dispatch(new SendWelcomeEmailJob($this->getWorker()));

        } catch (Exception $e) {
            throw new Exception($e->getMessage());
        }
    }

    public function setUserWithToken($token) : void
    {
        $valid_token = UsersVerification::where('token', $token)->first();
        if (!$valid_token) {
            throw new ModelNotFoundException("Invalid token");
        }
        $this->setWorker($valid_token->user->id);
    }

    public function confirmWorker()
    {
        return $this->worker->update([
            'is_confirmed' => true
        ]);
    }

    public function isConfirmed()
    {
        return $this->getWorker()->is_confirmed ?? false;
    }

    public function isNotConfirmed()
    {
        return !$this->worker->is_confirmed ?? true;
    }

    public function isBan()
    {
        return $this->worker->is_ban ?? true;
    }

    public function authenticate($credentials)
    {
        if (!$token = auth()->attempt($credentials)) {
            throw new Exception("Incorrect email/phone or password");
        }

        $this->setWorker(auth()->id());

        if ($this->isNotConfirmed()) {
            throw new Exception("E-mail not verified! Kindly check your e-mail to confirm");
        }

        if ($this->isBan()) {
            throw new Exception("User is banned! Kindly contact the admin");
        }

        // Update last login
        dispatch((new UpdateLastLoginJob(auth()->id(), request()->ip()))
            ->delay(Carbon::now()->addSeconds(10)));

        return [
            'access_token' => $token,
            'payload' => $this->getFullDetails()
        ];
    }

    public function updateLastLogin($user_id, $ip)
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
}