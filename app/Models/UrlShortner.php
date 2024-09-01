<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UrlShortner extends Model
{
    use HasFactory;
    protected $fillable = [
        'long_url',
        'hash_value',
    ];
}
