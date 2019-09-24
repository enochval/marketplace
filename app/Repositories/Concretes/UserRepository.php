<?php


namespace App\Repositories\Concretes;

use App\Jobs\SendChangePasswordEmail;
use App\Jobs\SendPaymentReceiptEmailJob;
use App\Jobs\SendWelcomeEmailJob;
use App\Jobs\StoreBVNAnalysisJob;
use App\Jobs\UpdateLastLoginJob;
use App\Models\GeneralSetting;
use App\Models\Role;
use App\Models\Transaction;
use App\Models\User;
use App\Models\UsersVerification;
use App\Repositories\Contracts\IUserRepository;
use App\Services\Paystack;
use Carbon\Carbon;
use Exception;
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
        return User::with(['profile', 'roles', 'lastLogin'])->find($this->user->id);
    }

    public function getWorkerDetails()
    {
        return User::with(['profile', 'workHistory', 'skill', 'roles', 'lastLogin'])->find($this->getUser()->id);
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
            'bio' => $params['bio'],
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
     * @param string $callback_url
     * @return mixed
     * @throws Exception
     */
    public function bvnVerification(int $user_id, int $bvn, string $callback_url): array
    {
        $this->setUser($user_id);

        if (!$this->isProfileUpdated()) {
            throw new Exception("Profile not updated! update your profile to proceed");
        }

        $this->getUser()->profile()->update([
            'bank_verification_number' => $bvn
        ]);

        if (!$this->hasPaid()) {
            $paystack = new Paystack();

            $amount = $this->getVerificationFee();
            $reference = $paystack->genTranxRef();

            $this->initializeTransaction($amount, $reference);

            Cache::forget('callback_url');
            Cache::put('callback_url', [
                'callback_url' => $callback_url
            ], Carbon::now()->addMinutes(10));

            $initialization = $paystack->initialize($reference, $amount, $this->getUser()->email);

            $url = data_get($initialization, 'data.authorization_url');

            return ['authorization_url' => $url];
        }

        $bvn_verify = (new Paystack())->bvnVerification($this->getUserBVN());

        $bvn_data = data_get($bvn_verify, 'data');

        $bvn_status = $this->analyzeBVN($bvn_data);

        if ($bvn_status) {
            // Update bvn_verified status
            $this->updateBvnVerificationStatus();

            //update premium status here I guess


            return ["bvn_verification_status" => "Valid"];
        }

        return ["bvn_verification_status" => "Invalid"];
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
    public function getVerificationFee(): string
    {
        if (!$setting = GeneralSetting::find(1)) {
            throw new Exception('Settings not found');
        }

        return $setting->verification_fee;
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

        [
            'status' => $status,
        ] = $verify_payment;

        $amount = $this->toNaira(data_get($verify_payment, 'data.amount'));
        $status_value = data_get($verify_payment, 'data.gateway_response');

        $transaction = $this->getTransactionWithReference($reference);

        // A bit slower process... Will update this later...
        $this->setUser($transaction->user->id);

        $transaction->update([
            'status' => $status,
            'meta' => json_encode($verify_payment)
        ]);

        $bvn_status = false;

        if ($payment->wasSuccessful($status_value)) {
            // send a mail
            dispatch(new SendPaymentReceiptEmailJob($this->getUser(), $amount, $status_value));

            // Update payment status
            $this->updatePaymentStatus();

            $bvn_verify = $payment->bvnVerification($this->getUserBVN());

            $bvn_data = data_get($bvn_verify, 'data');

            $bvn_status = $this->analyzeBVN($bvn_data);

            if ($bvn_status) {
                // Update bvn_verified status
                $this->updateBvnVerificationStatus();

                //update premium status here I guess
            }

            // I don't know if you should make the user premium here....
        }

        $bvn_status = $bvn_status ? "Valid" : "Invalid";

        return $callback_url . '?payment_status=' . $status_value . '&bvn_verification_status=' . $bvn_status;
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

    public function updatePaymentStatus(): void
    {
        $this->getUser()->update([
            'has_paid' => true
        ]);
    }

    public function getUserBVN()
    {
        return $this->getUser()->profile->bank_verification_number;
    }

    public function analyzeBVN($data) : bool
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

    public function hasPaid() : bool
    {
        return $this->getUser()->has_paid ?? false;
    }

    public function updateProfileStatus() : void
    {
        $this->getUser()->update([
            'profile_updated' => true
        ]);
    }

    public function updateBvnVerificationStatus() : void
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

//    /**
//     * @param $params
//     * @throws Exception
//     */
//    public function createUser($params)
//    {
//        try {
//            $user = User::create([
//                'email' => $params['email'],
//                'phone' => $params['phone'],
//                'password' => bcrypt($params['password'])
//            ]);
//
//            $user->profile()->create([
//                'first_name' => $params['first_name'],
//                'last_name' => $params['last_name'],
//            ]);
//
//            $user->attachRole($params['role_id']);
//
//            $this->createVerificationToken($user);
//
//            $this->activate($user);
//        } catch (Exception $e) {
//            report($e);
//
//            //Delete the user to avoid duplicate entry.
//            $user->delete();
//
//            // Return a custom error message back....
//            throw new Exception("Unable to create user, please try again");
//        }
//    }
//
//    public function update($user_id, $params)
//    {
//        try {
//            $user = User::find($user_id);
//
//            $user->update([
//                'email' => $params['email'],
//                'phone' => $params['phone'],
//            ]);
//
//            $user->profile()->update([
//                'first_name' => $params['first_name'],
//                'last_name' => $params['last_name'],
//            ]);
//
//            $user->attachRole($params['role_id']);
//
//        } catch (Exception $e) {
//            report($e);
//
//            //Delete the user to avoid duplicate entry.
//            $user->delete();
//
//            // Return a custom error message back....
//            throw new Exception("Unable to create user, please try again");
//        }
//    }

//    public function createVerificationToken(User $user): void
//    {
//        $user->verificationToken()->create([
//            'token' => str_random(40)
//        ]);
//    }
//
//    public function activate(User $user): bool
//    {
//        return $user->update([
//            'is_active' => true
//        ]);
//    }
    public function allUsers($perPage = 15, $orderBy = 'created_at', $sort = 'desc')
    {
        return User::orderBy($orderBy, $sort)->paginate($perPage);
    }
}
