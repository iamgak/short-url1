<?php
// app/Models/UrlShortner.php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UrlShortner extends Model
{
    protected $fillable = [
        'active',
        'traffic',
        'long_url',
        'hash_value',
    ];

    protected $casts = [
        'active' => 'boolean',
    ];

    public $timestamps = true;
}
