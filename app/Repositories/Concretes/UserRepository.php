<?php


namespace App\Repositories\Concretes;

use App\Jobs\SendWelcomeEmailJob;
use App\Jobs\UpdateLastLoginJob;
use App\Models\Role;
use App\Models\User;
use App\Models\UsersVerification;
use App\Repositories\Contracts\IUserRepository;
use Carbon\Carbon;
use Exception;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class UserRepository implements IUserRepository
{
    private $user;

    /**
     * @return mixed
     */
    public function getUser(): User
    {
        return $this->user;
    }

    public function setUser($user_id): void
    {
        $this->user = User::find($user_id);
    }


    /**
     * @param $credentials
     * @return array
     * @throws Exception
     */
    public function authenticate(array $credentials): array
    {
        if (!$token = auth()->attempt($credentials)) {
            throw new Exception("Incorrect email/phone or password");
        }

        $this->setUser(auth()->id());

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

    public function setUserWithToken($token): void
    {
        $valid_token = UsersVerification::where('token', $token)->first();
        if (!$valid_token) {
            throw new ModelNotFoundException("Invalid token");
        }
        $this->setUser($valid_token->user->id);
    }

    public function isConfirmed(): bool
    {
        return $this->getUser()->is_confirmed ?? false;
    }

    public function isNotConfirmed(): bool
    {
        return !$this->getUser()->is_confirmed ?? true;
    }

    public function isBan(): bool
    {
        return $this->getUser()->is_ban ?? true;
    }

    public function getFullDetails(): User
    {
        return User::with(['profile', 'lastLogin'])->find($this->user->id);
    }

    /**
     * @return mixed
     */
    public function confirmUser(): bool
    {
        return $this->getUser()->update([
            'is_confirmed' => true
        ]);
    }

    /**
     * @param $token
     * @throws Exception
     */
    public function verifyEmail($token): void
    {
        try {
            $this->setUserWithToken($token);

            if ($this->isConfirmed()) {
                throw new Exception('User\'s e-mail is already verified! Kindly proceed to login');
            }

            if (!$this->confirmUser()) {
                throw new Exception("Could not confirm user with user_id " . $this->user->id);
            }

            dispatch(new SendWelcomeEmailJob($this->getUser()));
        } catch (Exception $e) {
            throw new Exception($e->getMessage());
        }
    }

    public function updateLastLogin($user_id, $ip): void
    {
        $this->setUser($user_id);
        $this->getUser()->lastLogin()->updateOrCreate(
            ['user_id' => $this->user->id],
            [
                'last_login_at' => Carbon::now()->toDateTimeString(),
                'last_login_ip' => $ip
            ]
        );
    }

    public function profile(int $user_id, array $params): User
    {
        $this->setUser($user_id);

        $this->getUser()->profile()->update([
            'first_name' => $params['first_name'],
            'last_name' => $params['last_name'],
            'gender' => $params['gender'],
            'date_of_birth' => $params['date_of_birth'],
            'avatar' => $params['avatar'],
            'address' => $params['address'],
            'city' => $params['city'],
            'state' => $params['state'],
            'bio' => $params['bio']
        ]);

        if ($this->getUser()->hasRole(Role::WORKER)) {
            // return worker's full details
            return $this->getFullDetails();
        } elseif ($this->getUser()->hasRole(Role::EMPLOYER)) {
            // return employer's full details
            return $this->getFullDetails();
        } elseif ($this->getUser()->hasRole(Role::AGENT)) {
            // return agent's full details
            return $this->getFullDetails();
        } elseif ($this->getUser()->hasRole(Role::ADMIN)) {
            // return agent's full details
            return $this->getFullDetails();
        }
    }

    public function bvnVerification(int $user_id, int $bvn)
    {
        $this->setUser($user_id);

        // store the bvn in profile

        // create transaction initials

        // initialize payment on paystack
    }
}
