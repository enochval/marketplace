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
use phpDocumentor\Reflection\Types\Object_;

class UserRepository implements IUserRepository
{
  private $user;

  /**
   * @return mixed
   */
  public function getUser()
  {
    return $this->user;
  }

  public function setUser($user_id): void
  {
    $this->user = User::find($user_id);
  }


  public function authenticate($credentials)
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

  public function isConfirmed()
  {
    return $this->getUser()->is_confirmed ?? false;
  }

  public function isNotConfirmed()
  {
    return !$this->user->is_confirmed ?? true;
  }

  public function isBan()
  {
    return $this->user->is_ban ?? true;
  }

  public function getFullDetails()
  {
    return User::with(['profile', 'lastLogin'])->find($this->user->id);
  }

  public function confirmUser()
  {
    return $this->user->update([
      'is_confirmed' => true
    ]);
  }

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

  public function updateLastLogin($user_id, $ip)
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
}
