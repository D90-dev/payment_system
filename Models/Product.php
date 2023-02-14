<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory;

    public $fillable = [
        'name',
        'stripe_product_id'
    ];

    public function features(){
        return $this->hasMany(Feature::class);
    }
}
