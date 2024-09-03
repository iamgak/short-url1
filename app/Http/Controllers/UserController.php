<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redis;

use Illuminate\Support\Facades\Hash;
use App\Models\User;
// use function Laravel\Prompts\password;
class UserController extends Controller
{
    
    function login(Request $request)
    {
        $email = $request->input('email');
        if ($email == "") {
            return response()->json(['Error', 'Enter email first']);
        }

        $password = $request->input('password');
        if ($password == "") {
            return response()->json(['Error', 'Enter Password First']);
        }

        $login = User::where('email', $email)->first();

        if (!$login || !Hash::check($password, $login->password)) {
            return response()->json(['Error', 'Invalid credentials']);
        }
        

        $login_token = sha1(time() . $_SERVER['REMOTE_ADDR'] . 'micky_mouse');
        User::where('id', $login->id)->update([
            'remember_token' => $login_token
        ]);

        setcookie('ldata', $login_token, time() + 1 * 60 * 60, '/');

        //Because we are going to check valid user or not using redis
        Redis::set($login_token, $login->id);
        return response()->json(['Message', 'Login']);
    }

    function register(Request $request)
    {
        $email = $request->input('email');
        if ($email == "") {
            return response()->json(['Error', 'Enter email first']);
        }

        $password = $request->input('password');
        if ($password == "") {
            return response()->json(['Error', 'Enter Password First']);
        }

        $password_again = $request->input('password_again');
        if ($password_again == "" || $password != $password_again) {
            return response()->json(['Error', 'Enter Same Password Again']);
        }

        $name = $request->input('name');
        if ($name == "") {
            return response()->json(['Error', 'Enter name First']);
        }

        $valid_email = User::where('email', $email)->exists();
        if ($valid_email) {
            return response()->json(['Error', 'Please, select any other email( already register) ']);
        }

        // echo sha1(time() . $_SERVER['REMOTE_ADDR'] . 'token');die;
        $register = User::create([
            'email' => $email,
            'password' => HASH::make($password),
            'name' => $name,
            'verification_token' => sha1(time() . $_SERVER['REMOTE_ADDR'] . 'token')
        ]);

        if (!$register) {
            return response()->json(['Error', 'Interval Server Error']);
        }

        return response()->json(['Message', 'register']);
    }

    function logout()
    {
        $login_token = $_COOKIE['ldata'];
        if (!preg_match('/^[a-f0-9A-Z]{40}$/', $login_token)) {
            return response()->json(['Message', 'Access Denied']);
        }

        User::where('remember_token', $login_token)->update([
            'remember_token' => NULL
        ]);

        // And Set Redis to erase data. Dont know command yet 
        Redis::delete($login_token);

        return response()->json(['Message', 'logout']);
    }

    function email_verification($token)
    {
        if (!preg_match('/^[a-f0-9A-Z]{40}$/', $token)) {
            return response()->json(['Message', 'Access Denied']);
        }

        User::where('verification_token', $token)->update([
            'verification_token' => NULL,
            'email_verified_at' => now(),
            'active' => 1
        ]);

        return response()->json(['Message', 'verified']);
    }
}
