<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Hash;
use App\Models\UrlShortner;
use App\Models\User;
use Illuminate\Support\Facades\Redis;
use Illuminate\Http\Request;

use function Laravel\Prompts\password;

const base62Chars = '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz';
class ShortUrlController extends Controller
{
    public function home()
    {
        echo "Welcome to URL Cutter. Homepage!!";
        exit;
    }


    public function redirect($hashValue)
    {
        // Check if long URL is present in Redis
        $longUrl = Redis::get($hashValue);
        if (!$longUrl) {
            // If not present in Redis, check in database
            $urlShortner = UrlShortner::where('hash_value', $hashValue)
                ->where('active', 1)
                ->first();
            // if no record is available then it is inactive or wrong hash value
            if (!$urlShortner) {
                return  "No record found";
            }
            // Update the record
            UrlShortner::where('id', $urlShortner->id)->update([
                'traffic' => $urlShortner->traffic + 1,
                'updated_at' => now()
            ]);

            //redirect to long_url in future
            $longUrl = $urlShortner->long_url;
            Redis::set($hashValue, $longUrl); // Store in Redis for future requests
        }


        return response()->json(['long_url' => $longUrl]);
    }

    public function add(Request $request)
    {
        $longUrl = $request->input('long_url');
        if ($longUrl == "") {
            return response()->json(['error' => 'Empty URL!!']);
        }

        // Check if long URL is present in Redis

        // need to do in today most probably
        $hashValue = Redis::search($longUrl);
        if (!$hashValue) {
            // If not present in Redis, check in database
            $urlShortner = UrlShortner::where('long_url', $longUrl)->first();
            if ($urlShortner) {
                $hashValue = $urlShortner->hash_value;
            } else {
                // If not present in database, create a new URL shortner
                $urlShortner = UrlShortner::create([
                    'active' => 0,
                    'traffic' => 0,
                    'long_url' => $longUrl,
                ]);

                $hashValue = $this->base62Encode($urlShortner->id);
                UrlShortner::where('id', $urlShortner->id)->update([
                    'hash_value' => $hashValue,
                    'active' => 1
                ]);
            }

            Redis::set($hashValue, $longUrl); // Store in Redis for future requests
        }

        return response()->json(['hash_value' => $hashValue]);
    }


    public function base62Encode($id)
    {
        $base62 = "";
        $i = $id;
        while ($i > 0) {
            $remainder = $i % 62;
            $base62 = base62Chars[$remainder] . $base62;
            $i = intval($i / 62);
        }

        return $base62;
    }

    function base62Decode($hash)
    {
        $value = 0;
        $hash = strrev($hash);
        $i = 0;
        while ($i < strlen($hash)) {
            $value += $this->search(str_split($hash)[$i]) * pow(16, $i);
            $i++;
        }

        return $value;
    }


    function search($string)
    {
        if (preg_match("/\d/", $string)) {
            $initial = 0;
            $final = 9;
            $i = 0;
        } else if (preg_match("/[A-Z]/", $string)) {
            $initial = 'A';
            $final = 'Z';
            $i = 10;
        } else if (preg_match("/[a-z]/", $string)) {
            $initial = 'a';
            $final = 'z';
            $i = 36;
        }

        foreach (range($initial, $final) as $elements) {
            if ($elements == $string) {
                return $i;
            }
            $i++;
        }

        return -1;
    }

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

        $login = UrlShortner::where('email', $email)
            ->where('password', HASH::make($password))->first();

        if (!$login) {
            return response()->json(['Error', 'Internal Server Errror']);
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

        User::where('id', $login_token)->update([
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
