<?php


namespace App\Repositories\Concretes;

use App\Jobs\SendWelcomeEmailJob;
use App\Jobs\UpdateLastLoginJob;
use App\Models\Role;
use App\Models\User;
use App\Jobs\SendVerificationEmailJob;
use App\Models\UsersVerification;
use App\Repositories\Contracts\IEmployerRepository;
use Carbon\Carbon;
use Exception;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use phpDocumentor\Reflection\Types\Object_;

class EmployerRepository implements IEmployerRepository
{
  private $employer;

  /**
   * @return mixed
   */
  public function getEmployer()
  {
    return $this->employer;
  }

  /**
   * @param $employer_id
   */
  public function setEmployer($employer_id): void
  {
    $this->employer = User::find($employer_id);
  }

  public function register($params): void
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
      $employer = User::create([
        'email' => $email,
        'phone' => $phone,
        'password' => bcrypt($password)
      ]);

      $employer_id = $employer->id;
      $this->setEmployer($employer_id);

      $employer->profile()->create([
        'first_name' => $first_name,
        'last_name' => $last_name
      ]);

      // Attach employer role
      $this->assignEmployerRole();

      // Generate user verification token
      if (!$this->createVerificationToken()) {
        throw new Exception("Could not create verification token for the registered employer with user_id ${employer_id}");
      }

      if (!$this->activate()) {
        throw new Exception("Could not activate employer user with user_id ${employer_id}");
      }

      // Push this verification email to the queue (Basically sends this email to the registered employer)
      dispatch(new SendVerificationEmailJob($this->getEmployer()));
    } catch (Exception $e) {
      // Log the actual error to your logger... that's $e->getMessage()...

      //Delete the user to avoid duplicate entry.
      $this->employer->delete();

      // Return a custom error message back....
      throw new Exception("Unable to create user, please try again");
    }
  }

  public function activate()
  {
    return $this->employer->update([
      'is_active' => true
    ]);
  }

  public function createVerificationToken()
  {
    return $this->employer->verificationToken()->create([
      'token' => str_random(40)
    ]);
  }

  public function assignEmployerRole(): void
  {
    $employerRole = Role::where('name', 'employer')->first();

    if (!$employerRole) {
      throw new Exception("Unable to find employer role in the system");
    }

    $this->employer->attachRole($employerRole);
  }

  public function getFullDetails()
  {
    return User::with(['profile', 'lastLogin'])->find($this->employer->id);
  }

  public function verifyEmail($token): void
  {
    try {
      $this->setUserWithToken($token);

      if ($this->isConfirmed()) {
        throw new Exception('User\'s e-mail is already verified! Kindly proceed to login');
      }

      if (!$this->confirmEmployer()) {
        throw new Exception("Could not confirm employer with user_id " . $this->employer->id);
      }

      dispatch(new SendWelcomeEmailJob($this->getEmployer()));
    } catch (Exception $e) {
      throw new Exception($e->getMessage());
    }
  }

  public function setUserWithToken($token): void
  {
    $valid_token = UsersVerification::where('token', $token)->first();
    if (!$valid_token) {
      throw new ModelNotFoundException("Invalid token");
    }
    $this->setEmployer($valid_token->user->id);
  }

  public function confirmEmployer()
  {
    return $this->employer->update([
      'is_confirmed' => true
    ]);
  }

  public function isConfirmed()
  {
    return $this->getEmployer()->is_confirmed ?? false;
  }

  public function isNotConfirmed()
  {
    return !$this->employer->is_confirmed ?? true;
  }

  public function isBan()
  {
    return $this->employer->is_ban ?? true;
  }

  public function authenticate($credentials)
  {
    if (!$token = auth()->attempt($credentials)) {
      throw new Exception("Incorrect email/phone or password");
    }

    $this->setEmployer(auth()->id());

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
    $this->setEmployer($user_id);
    $this->getEmployer()->lastLogin()->updateOrCreate(
      ['user_id' => $this->employer->id],
      [
        'last_login_at' => Carbon::now()->toDateTimeString(),
        'last_login_ip' => $ip
      ]
    );
  }
}
