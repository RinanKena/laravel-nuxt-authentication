<?php

namespace App\Http\Controllers\api\Auth;
namespace Illuminate\Http\Request; 



use App\Models\Account;
use App\Models\User;
use App\Models\UserSocial;
use Illuminate\Http\Request; 
use Laravel\Socialite\Facades\Socialite;
use Laravel\Socialite\Two\InvalidStateException;
use Spatie\FlareClient\Http\Exceptions\InvalidData;
use App\Http\Controllers\Controller;


class SocialLoginController extends Controller
{

    protected $auth;

    
    public function __construct($auth)
    {
       // $this->middleware(['social', 'web']);
       $this->auth = $auth;
        $this->middleware(['social']);
    }

    public function redirect($service)
    {
        return Socialite::driver($service)->redirect();
        //return Socialite::driver($service)->stateless()->redirect();


    }

    public function callback($service)
    {

        // dd($service);
        try {
            $serviceUser = Socialite::driver($service)->user();
        } catch (InvalidStateException $e) {
           
            return redirect(env('CLIENT_BASE_URL') . '/auth/social-callback?error=Unable to login using ' . $service . '. Please try again' . '&origin=login');

        }


        $email = $serviceUser->getEmail();
        if ($service != 'google') {
            $email = $serviceUser->getId() . '@' . $service . '.local';
        }


        $user = $this->getExistingUser($serviceUser, $email, $service);
        $newUser = false;
        if (!$user) {
            $newUser = true;
            $user = User::create([
                'name' => $serviceUser->getName(),
                'email' => $email,
                'password' => ''
            ]);
        }

        if ($this->needsToCreateSocial($user, $service)) {
            UserSocial::create([
                'user_id' => $user->id,
                'social_id' => $serviceUser->getId(),
                'service' => $service
            ]);
        }


        /*dd($serviceUser);
        if ($service == 'google') {
            $user = User::updateOrCreate([
                'id' => $serviceUser->id,
            ], [
                'first_name' => $serviceUser->name,
            ]);
        } else if ($service == 'github') {
            $user = User::updateOrCreate([
                'id' => $serviceUser->id,
            ], [
                'first_name' => $serviceUser->nickname,
            ]);
        } else if ($service == 'facebook') {
            $user = User::updateOrCreate([
                'id' => $serviceUser->id,
            ], [
                'first_name' => $serviceUser->name,
            ]);
        } else {
        }
*/
        /**
         * Test: using  dunp user login information to see user deatial 
         * Default: using reutrn redirect(env...)...
         */
        //return redirect(env('APP_URL') . '/Auth/Social-callback?token=' . $serviceUser->token);
        
        return redirect(env('CLIENT_BASE_URL') . '/auth/social-callback?token=' . $this->auth->fromUser($user) . '&origin=' . ($newUser ? 'register' : 'login'));
    }

    public function needsToCreateSocial(User $user, $service)
    {
        return !$user->hasSocialLinked($service);
    }

    public function getExistingUser($serviceUser, $email, $service)
    {
        if ($service == 'google') {
            return User::where('email', $email)->orWhereHas('social', function($q) use ($serviceUser, $service) {
                $q->where('social_id', $serviceUser->getId())->where('service', $service);
            })->first();
        } else {
            $userSocial = UserSocial::where('social_id', $serviceUser->getId())->first();
            return $userSocial ? $userSocial->user : null;
        }
    }
}


    /**
     * return a user if has one,
     * return null if do not have that user. 
     */
    // public function getExistingUser($serviceUser, $email, $service)
    // {
    //     if ($service != null) {
    //         return User::where('email', $email)->orWhereHas('social', function ($q) use ($serviceUser, $service) {
    //             $q->where('id', $serviceUser->getID())->where('service', $service);
    //         })->first();
    //     } else {
    //         $userSocial = User::where('id', $serviceUser->getID())->first();
    //         return $userSocial ? $userSocial->user : null;
    //     }
    // }
    

 