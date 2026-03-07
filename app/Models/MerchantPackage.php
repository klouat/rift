<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MerchantPackage extends Model
{
    protected $fillable = [
        'package_id',
        'skills',
        'advanced_skills',
        'prices'
    ];

    protected $casts = [
        'skills' => 'array',
        'advanced_skills' => 'array',
        'prices' => 'array',
    ];
}
