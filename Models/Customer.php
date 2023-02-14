<?php

namespace App\Models;

use App\Http\Traits\StripeCustomer;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Laravel\Cashier\Billable;
use function Illuminate\Events\queueable;

class Customer extends Model
{
    use HasFactory, Billable, StripeCustomer;

    protected $fillable = [
        'name',
        'email',
        'company_id',
        'product_id',
        'company_token',
        'billing_details',
        'pm_card_brand',
        'pm_last_four',
        'pm_type'
    ];

    protected static function booted()
    {
        static::updated(queueable(function ($customer) {
            if($customer->stripe_id){
                $customer->syncStripeCustomerDetails();
            }
        }));
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function taxRates()
    {
        $billing_details = json_decode($this->billing_details, true);

        if($billing_details){
            $countryCode = $billing_details['country'];
            $tax = Tax::where('country', $countryCode)->first();
        }
        else{
            $tax = null;
        }


        if($tax){
            return [$tax->gateway_id];
        }
        else{
            return [];
        }
    }
}
