<?php

namespace App\Http\Controllers;

use App\Models\UrlShortner;
use Illuminate\Support\Facades\Redis;
use Illuminate\Http\Request;

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
                // 'updated_at' => timestamp()
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
        $hashValue = Redis::get($longUrl);
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
                    'active' => 1,
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
}
