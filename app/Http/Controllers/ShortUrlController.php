<?php

namespace App\Http\Controllers;

use App\Models\UrlShortner;
use Illuminate\Support\Facades\DB;
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

    public function add_shortner(Request $request)
    {        // Validate the form data
        // $request->validate([
        //     'long_url' => 'required|string|max:255',
        // ]);

        // session()->flash('success', 'Form submitted successfully!');
        // return redirect()->route('/');
        $inputValue = $request->input('long_url');
        $urlShortner = UrlShortner::create([
            'active' => 1,
            'traffic' => 0,
            'long_url' => $inputValue,
            'traffic' => 0,
        ]);

        $hash_value = $this->base62Encode($inputValue);
        // Update the record
        UrlShortner::where('id', $urlShortner->id)->update([
            'hash_value' => $hash_value,
            'active' => 0,
        ]);


        echo "Form submitted successfully! $inputValue -" . $hash_value;
        exit;
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
