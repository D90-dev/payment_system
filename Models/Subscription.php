<?php

namespace App\Models;

use App\Events\SubscriptionDeletedEvent;
use App\Http\Services\SubscriptionService;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;
use Laravel\Cashier\Subscription as CashierSubscription;

class Subscription extends CashierSubscription
{
    use HasFactory;

    protected $fillable = [
        'customer_id',
        'name',
        'stripe_id',
        'stripe_status',
        'stripe_price',
        'quantity',
        'trial_ends_at',
        'ends_at',
        'amount',
        'contractual_period',
        'next_billing_at',
        'gateway',
        'features'
    ];

    protected $with = ['plan'];
    protected $appends = ['price'];

    /**
     * The "booted" method of the model.
     *
     * @return void
     */
    protected static function booted()
    {
        // when all attempts of payment failed, stripe will delete the subscription
        // system will send webhook to APP to change the status to PENDING
        static::created(function ($subscription){
            if(request()->path() == 'api/customer/checkout' && request()->has('features')){
                $subscription->features = request()->get('features');
                $subscription->save();
            }
        });
    }


    public function plan(){
        return $this->belongsTo(Plan::class, 'stripe_price', 'stripe_plan_id');
    }

    public function getPriceAttribute(){
        $subscriptionService = new SubscriptionService();
        return $subscriptionService->getSubscriptionPrice($this);
    }

    public function customer(){
        return $this->belongsTo(Customer::class);
    }
}
