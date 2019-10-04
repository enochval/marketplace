<?php


namespace App\Repositories\Concretes;

use App\Jobs\SendChangePasswordEmail;
use App\Jobs\SendPaymentReceiptEmailJob;
use App\Jobs\SendVerificationEmailJob;
use App\Jobs\SendWelcomeEmailJob;
use App\Jobs\StoreBVNAnalysisJob;
use App\Jobs\UpdateLastLoginJob;
use App\Models\AgentCustomer;
use App\Models\GeneralSetting;
use App\Models\Profile;
use App\Models\Role;
use App\Models\Transaction;
use App\Models\User;
use App\Models\UsersVerification;
use App\Repositories\Contracts\IUserRepository;
use App\Services\Paystack;
use Carbon\Carbon;
use Exception;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;

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
     * @param array $params
     * @param $role
     * @throws Exception
     */
    public function register(array $params, $role): void
    {

        [
            'email' => $email,
            'phone' => $phone,
            'first_name' => $first_name,
            'last_name' => $last_name,
            'password' => $password
        ] = $params;

        try {
            // Persist data
            $user = User::create([
                'email' => $email,
                'phone' => $phone,
                'password' => bcrypt($password)
            ]);

            $user_id = $user->id;
            $this->setUser($user_id);

            $this->getUser()->profile()->create([
                'first_name' => $first_name,
                'last_name' => $last_name,
                'avatar' => Profile::AVATAR
            ]);

            // Attach worker role
            $this->assignRole($role);

            // Generate user verification token
            if (!$this->createVerificationToken())
                throw new Exception("Could not create verification token for the registered user with id ${user_id}");

            if (!$this->activate())
                throw new Exception("Could not activate user user with id ${user_id}");

            // Push this verification email to the queue (Basically sends this email to the registered worker)
            dispatch(new SendVerificationEmailJob($this->getUser()));
        } catch (Exception $e) {
            report($e);

            //Delete the user to avoid duplicate entry.
            $this->getUser()->delete();

            // Return a custom error message back....
            throw new Exception("Unable to create user, please try again");
        }
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

        $profile = ($this->getUser()->hasRole(Role::WORKER)) ? $this->getWorkerDetails()
            : $this->getFullDetails();

        return [
            'access_token' => $token,
            'payload' => $profile
        ];
    }

    public function activate(): bool
    {
        return $this->getUser()->update([
            'is_active' => true
        ]);
    }

    public function createVerificationToken(): UsersVerification
    {
        return $this->getUser()->verificationToken()->create([
            'token' => str_random(40)
        ]);
    }

    /**
     * @param $role
     * @throws Exception
     */
    public function assignRole($role): void
    {
        $user_role = Role::where('name', $role)->first();

        if (!$user_role) {
            throw new Exception("Unable to find expected role in the system");
        }

        $this->getUser()->attachRole($user_role);
    }

    public function workHistory(int $user_id, array $params): User
    {
        $this->setUser($user_id);

        $this->getUser()->workHistory()->create([
            'employer' => $params['employer'],
            'position' => $params['position'],
            'start_date' => $params['start_date'],
            'end_date' => $params['end_date'],
        ]);

        $this->updateWorkHistoryStatus();

        return $this->getWorkerDetails();
    }

    public function updateWorkHistoryStatus(): void
    {
        $this->getUser()->update([
            'work_history_updated' => true
        ]);
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
        return User::with(['profile.city', 'roles', 'lastLogin'])->find($this->user->id);
    }

    public function getWorkerDetails()
    {
        return User::with(['profile.city', 'workHistory', 'roles', 'lastLogin'])->find($this->getUser()->id);
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
            'city_id' => $params['city_id'],
            'state' => $params['state'],
            'job_interest' => json_encode($params['job_interest']),
            'bio' => $params['bio']
        ]);

        $this->updateProfileStatus();

        if ($this->getUser()->hasRole(Role::WORKER)) {
            return $this->getWorkerDetails();
        }

        return $this->getFullDetails();
    }

    /**
     * @param int $user_id
     * @param int $bvn
     * @return mixed
     * @throws Exception
     */
    public function bvnVerification(int $user_id, int $bvn = null): array
    {
        $this->setUser($user_id);

        if (!$this->isProfileUpdated()) {
            throw new Exception("Profile not updated! update your profile to proceed");
        }

        if (!is_null($bvn)) {
            $this->getUser()->profile()->update([
                'bank_verification_number' => $bvn
            ]);
        }

        try {
            $bvn_verify = (new Paystack())->bvnVerification($this->getUserBVN());
        } catch (Exception $e) {
            throw new Exception("The BVN provided is not correct.");
        }

        $bvn_data = data_get($bvn_verify, 'data');

        $bvn_status = $this->analyzeBVN($bvn_data);

        if ($bvn_status) {
            // Update bvn_verified status
            $this->updateBvnVerificationStatus();

            return ["bvn_verification_status" => true];
        }

        return ["bvn_verification_status" => false];
    }

    /**
     * @param $user_id
     * @param $callback_url
     * @return array
     * @throws Exception
     */
    public function subscribe($user_id, $callback_url)
    {
        $this->setUser($user_id);

        if ($this->isPremium()) {
            throw new Exception('You are already a premium user!');
        }
        $paystack = new Paystack();

        $amount = $this->getSubscriptionFee();
        $reference = $paystack->genTranxRef();

        $this->initializeTransaction($amount, $reference);

        Cache::forget('callback_url');
        Cache::put('callback_url', [
            'callback_url' => $callback_url
        ], Carbon::now()->addMinutes(10));

        try {

            $initialization = $paystack->initialize($reference, $amount, $this->getUser()->email);
        } catch (Exception $e) {
            throw new Exception("Connection Error: Please try again");
        }

        $url = data_get($initialization, 'data.authorization_url');

        return ['authorization_url' => $url];
    }

    public function initializeTransaction($amount, $reference): void
    {
        $this->getUser()->transaction()->create([
            'reference' => $reference,
            'amount' => $amount,
        ]);
    }

    /**
     * @return string
     * @throws Exception
     */
    public function getSubscriptionFee(): string
    {
        if (!$setting = GeneralSetting::find(1)) {
            throw new Exception('Settings not found');
        }

        return $setting->subscription_fee;
    }

    /**
     * @param string $reference
     * @return string
     * @throws Exception
     */
    public function callback(string $reference): string
    {
        $callback_url = Cache::get('callback_url')['callback_url'];

        $payment = (new Paystack());

        $verify_payment = $payment->verify($reference);

        ['status' => $status] = $verify_payment;

        $amount = $this->toNaira(data_get($verify_payment, 'data.amount'));
        $status_value = data_get($verify_payment, 'data.gateway_response');

        $transaction = $this->getTransactionWithReference($reference);

        // A bit slower process... Will update this later...
        $this->setUser($transaction->user->id);

        $transaction->update([
            'status' => $status,
            'meta' => json_encode($verify_payment)
        ]);

        if ($payment->wasSuccessful($status_value)) {
            // send a mail
            dispatch(new SendPaymentReceiptEmailJob($this->getUser(), $amount, $status_value));

            $this->updatePremiumStatus();
        }
        return $callback_url . '?payment_status=' . $status_value;
    }

    public function manualSubscription($user_id)
    {
        $this->setUser($user_id);
        $this->updatePremiumStatus();
    }

    /**
     * @param $reference
     * @return Transaction
     * @throws Exception
     */
    public function getTransactionWithReference($reference): Transaction
    {
        if (!$transaction = Transaction::where('reference', $reference)->first())
            throw new Exception('Transaction no found!');

        return $transaction;
    }

    public function updatePremiumStatus(): void
    {
        $this->getUser()->update([
            'is_premium' => true
        ]);
    }

    public function getUserBVN()
    {
        return $this->getUser()->profile->bank_verification_number;
    }

    public function analyzeBVN($data): bool
    {
        $first_name = $this->getUser()->profile->first_name;
        $last_name = $this->getUser()->profile->last_name;
        $date_of_birth = $this->getUser()->profile->date_of_birth;
        $phone = $this->getUser()->phone;

        [
            'first_name' => $bvn_first_name,
            'last_name' => $bvn_last_name,
            'mobile' => $bvn_phone,
            'formatted_dob' => $bvn_date_of_birth,
        ] = $data;

        $payload = [
            'first_name_match' => false,
            'last_name_match' => false,
            'dob_match' => false,
            'phone_match' => false,
            'score' => 0
        ];

        if (strtolower($first_name) == strtolower($bvn_first_name) ||
            strtolower($first_name) == strtolower($bvn_last_name)
        ) {
            $payload['first_name_match'] = true;
            $payload['score'] += 25;
        }

        if (strtolower($last_name) == strtolower($bvn_first_name) ||
            strtolower($last_name) == strtolower($bvn_last_name)
        ) {
            $payload['last_name_match'] = true;
            $payload['score'] += 25;
        }

        if (Carbon::parse($date_of_birth)->eq(Carbon::parse($bvn_date_of_birth))) {
            $payload['dob_match'] = true;
            $payload['score'] += 25;
        }

        if ($phone == $bvn_phone) {
            $payload['phone_match'] = true;
            $payload['score'] += 25;
        }

        dispatch(new StoreBVNAnalysisJob($this->getUser(), $payload));

        return ($payload['score'] > 50) ? true : false;
    }

    public function isPremium(): bool
    {
        return $this->getUser()->is_premium ?? false;
    }

    public function updateProfileStatus(): void
    {
        $this->getUser()->update([
            'profile_updated' => true
        ]);
    }

    public function updateBvnVerificationStatus(): void
    {
        $this->getUser()->update([
            'is_bvn_verified' => true
        ]);
    }

    public function getBvnAnalysis(int $user_id)
    {
        $this->setUser($user_id);

        return $this->getUser()->bvnAnalysis;
    }

    public function toNaira($amount)
    {
        return $amount / 100;
    }

    public function isProfileUpdated(): bool
    {
        return $this->getUser()->profile_updated ?? false;
    }

    /**
     * @param int $user_id
     * @param array $params
     * @throws Exception
     */
    public function updatePassword(int $user_id, array $params): void
    {
        $this->setUser($user_id);

        [
            'current_password' => $current_password,
            'new_password' => $new_password,
        ] = $params;

        if (!Hash::check($current_password, $this->getUser()->password))
            throw new Exception("Current password is incorrect");

        $this->getUser()->update([
            'password' => app('hash')->make($new_password)
        ]);

        dispatch(new SendChangePasswordEmail($this->getUser()));
    }

    public function allUsers($perPage = 15, $orderBy = 'created_at', $sort = 'desc')
    {
        return User::orderBy($orderBy, $sort)->paginate($perPage);
    }

    /**
     * @param int $user_id
     * @param array $params
     * @return User|User[]|Builder|Builder[]|Collection|Model|null
     * @throws Exception
     */
    public function registerWorkerByAgent(int $user_id, array $params)
    {
        $worker = User::create([
            'email' => $params['email'],
            'phone' => $params['phone'],
            'password' => bcrypt($params['password'])
        ]);

        $worker_id = $worker->id;

        $this->setUser($worker_id);

        $this->assignRole(Role::WORKER);

        if (!$this->activate())
            throw new Exception("Could not activate user user with id ${worker_id}");

        if (!$this->confirmUser()) {
            throw new Exception("Could not confirm user with user_id " . $worker_id);
        }

        dispatch(new SendWelcomeEmailJob($this->getUser()));

        $worker->profile()->create([
            'first_name' => $params['first_name'],
            'last_name' => $params['last_name'],
            'gender' => $params['gender'],
            'date_of_birth' => $params['date_of_birth'],
            'avatar' => $params['avatar'],
            'address' => $params['address'],
            'city_id' => $params['city_id'],
            'state' => $params['state'],
            'job_interest' => json_encode($params['job_interest']),
            'bio' => $params['bio'],
            'bank_verification_number' => $params['bvn'],
        ]);

        $worker->workHistory()->create([
            'employer' => $params['employer'],
            'position' => $params['position'],
            'start_date' => $params['start_date'],
            'end_date' => $params['end_date'],
        ]);

        $this->setUser($user_id);

        $this->getUser()->agentCustomer()->create([
            'worker_id' => $worker_id
        ]);

        $this->setUser($worker_id);

        return $this->getWorkerDetails();
    }

    public function getAgentWorkers(int $user_id)
    {
        return AgentCustomer::with('workers')->where('user_id', $user_id)->get();
    }
}
