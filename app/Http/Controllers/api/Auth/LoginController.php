<?php

namespace App\Http\Controllers;


use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Http\Request;
use Tymon\driblapp\Exceptions\JWTException;
use Tymon\driblapp\driblapp;
use Illuminate\Support\Facades\Route;


class LoginController extends Controller
{
    public function login($request)
    {
        // If the class is using the ThrottlesLogins trait, we can automatically throttle
        // the login attempts for this application. We'll key this by the username and
        // the IP address of the client making these requests into this application.
        if ($this->hasTooManyLoginAttempts($request)) {
            $this->fireLockoutEvent($request);

            return response()->json([
                'success' => false,
                'errors' => [
                    "You've been locked out"
                ]
            ]);
        }

        // If the login attempt was unsuccessful we will increment the number of attempts
        // to login and redirect the user back to the login form. Of course, when this
        // user surpasses their maximum number of attempts they will get locked out.
        $this->incrementLoginAttempts($request);

        // attempt login with token
        if ($request->input('token')) {
            $this->auth->setToken($request->input('token'));

            $user = $this->auth->authenticate();
            if ($user) {
                return response()->json([
                    'success' => true,
                    'data' => $request->user(),
                    'token' => $request->input('token')
                ], 200);
            }
        }

        try {
            if (!$token = $this->auth->attempt($request->only('email', 'password'))) {
                return response()->json([
                    'success' => false,
                    'errors' => [
                        'email' => [
                            "Invalid email address or password"
                        ]
                    ]
                ], 422);
            }
        }
        catch (JWTException $e) {
            return response()->json([
                'success' => false,
                'errors' => [
                    'email' => [
                        "Invalid email address or password"
                    ]
                ]
            ], 422);
        }


        return response()->json([
            'success' => true,
            'data' => $request->user(),
            'token' => $request->input('token')
        ], 200);
    }
}