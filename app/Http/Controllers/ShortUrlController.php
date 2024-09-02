<?php

namespace App\Http\Controllers;

// use App\Models\ShortUrl;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;

const base62Chars = '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz';
class ShortUrlController extends Controller
{
    public function get_shortner()
    {

        // Get the current database name
        $databaseName = DB::getDatabaseName();

        // Get a list of all tables in the database
        $tables = DB::select('SHOW TABLES');

        // Get information about all tables in the database
        $tableStatus = DB::select('SHOW TABLE STATUS');
        echo $databaseName;
        var_dump($tables);
        exit;
    }

    public function add_shortner(Request $request)
    {

        // Validate the form data
        // $request->validate([
        //     'name' => 'required|string|max:255',
        //     // 'email' => 'required|email|unique:users',
        //     // 'password' => 'required|string|min:8|confirmed',
        //     // 'password_confirmation' => 'required|string|min:8',
        // ]);

        // session()->flash('success', 'Form submitted successfully!');
        // return redirect()->route('/');
        $inputValue = $request->input('url');
        echo "Form submitted successfully!" . $this->base62Encode($inputValue);
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
    
    function base62Decode($hash){
        $value=0;
        $hash=strrev($hash);
        $i=0;
        while($i < strlen($hash)){
            $value+=$this->search1(str_split($hash)[$i])*pow(16,$i);
            $i++;
        }

        return $value;
    }
    

    function search1($string)
    {
        if (preg_match("/\d/", $string)) {
            return $this->search(0, 9, $string,0);
        } else if (preg_match("/[a-z]/", $string)) {
            return $this->search('a', 'z', $string,10);
        } else if (preg_match("/[A-Z]/", $string)) {
            return $this->search('A', 'Z', $string,36);
        }
    }


    function search($initial, $final, $string,$i)
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
