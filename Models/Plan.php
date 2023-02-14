<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Plan extends Model
{
    use HasFactory;

    protected $fillable = [
      'stripe_plan_id',
      'name',
      'code',
      'price',
      'product_id',
      'billing_type' // fixed, product_based, user_based
    ];

    public function features(){
        return $this->belongsToMany(Feature::class, 'feature_plans')->withPivot('value', 'price');
    }

    public function product(){
        return $this->belongsTo(Product::class);
    }
}
