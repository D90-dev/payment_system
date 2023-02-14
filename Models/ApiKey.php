<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ApiKey extends Model
{
    use HasFactory;

    public $fillable = [
        'product_id',
        'client_id',
        'client_secret',
        'webhook_token',
        'client_url',
        'client_webhook_url',
        'client_api_url'
    ];

    public function product(){
        return $this->belongsTo(Product::class);
    }
}
