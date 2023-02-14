<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FeaturePlan extends Model
{
    use HasFactory;

    protected $fillable = [
        'plan_id',
        'feature_id',
        'value',
        'price'
    ];
}
