<?php

namespace App\Http\Controllers;

use App\Models\UrlShortner;
use Illuminate\Support\Facades\Redis;
use Illuminate\Http\Request;

const base62Chars = '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz';
class ShortUrlController extends Controller
{
    public function get_shortner(Request $request)
    {
        $hash_url = $request->input("hash_url");
        $urlShortners = UrlShortner::where('hash_value', $hash_url)->get();
        echo "Form submitted successfully! $hash_url -" . $this->base62Decode($hash_url);

        foreach ($urlShortners as $urlShortner) {
            echo "ID: " . $urlShortner->id . "<br>";
            echo "Active: " . $urlShortner->active . "<br>";
            echo "Traffic: " . $urlShortner->traffic . "<br>";
            echo "Long URL: " . $urlShortner->long_url . "<br>";
            echo "Hash Value: " . $urlShortner->hash_value . "<br>";
            echo "Created At: " . $urlShortner->created_at . "<br>";
            echo "<hr>";
        }

        exit;
    }


    public function get_url(Request $request)
    {
        $url = $request->input("long_url");
        $urlShortners = UrlShortner::whereDate('long_url', $url)->get();
        echo "Form submitted successfully! $url -" . $this->base62Decode($url);
        exit;
    }


    public function add(Request $request)
    {
        $longUrl = $request->input('long_url');
    
        // Check if long URL is present in Redis
        $hashValue = Redis::get($longUrl);
        if ($hashValue) {
            // If present in Redis, return the hash value
            return response()->json(['hash_value' => $hashValue]);
        }
    
        // If not present in Redis, check in database
        $urlShortner = UrlShortner::where('long_url', $longUrl)->first();
        if ($urlShortner) {
            // If present in database, return the hash value
            $hashValue = $urlShortner->hash_value;
            Redis::set($longUrl, $hashValue); // Store in Redis for future requests
            return response()->json(['hash_value' => $hashValue]);
        }
    
        // If not present in database, create a new URL shortner
        $urlShortner = UrlShortner::create([
            'active' => 0,
            'traffic' => 0,
            'long_url' => $longUrl,
        ]);
    
        $hashValue = $this->base62Encode($urlShortner->id);
        // Update the record
        UrlShortner::where('id', $urlShortner->id)->update([
            'hash_value' => $hashValue,
            'active' => 1,
        ]);
    
        Redis::set($longUrl, $hashValue); // Store in Redis for future requests
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
            $value += $this->search1(str_split($hash)[$i]) * pow(16, $i);
            $i++;
        }

        return $value;
    }


    function search1($string)
    {
        if (preg_match("/\d/", $string)) {
            return $this->search(0, 9, $string, 0);
        } else if (preg_match("/[A-Z]/", $string)) {
            return $this->search('A', 'Z', $string, 10);
        } else if (preg_match("/[a-z]/", $string)) {
            return $this->search('a', 'z', $string, 36);
        }
    }


    function search($initial, $final, $string, $i)
    {
        foreach (range($initial, $final) as $elements) {
            if ($elements == $string) {
                return $i;
            }
            $i++;
        }

        return -1;
    }
}
