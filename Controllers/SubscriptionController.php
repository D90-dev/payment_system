<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\Subscription;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class SubscriptionController extends Controller
{

    /**
     * List all subscriptions
     */
    public function index(){
        $subscriptions = Subscription::with('customer')->get();

        return view('subscription.index', compact('subscriptions'));
    }

    /**
     * Set subscription as canceled for given date
     */
    public function setAsCanceled(Request $request){

        $activeSubscription = Subscription::findOrFail($request->subscription_reference);
        $contractual_period = $activeSubscription->contractual_period;
        $activeSubscription->cancelAt(Carbon::parse($contractual_period));

        return response()->json('success');
    }

    /**
     * Set subscription as active
     */
    public function setAsActive(Request $request){

        $canceledSubscription = Subscription::findOrFail($request->subscription_reference);

        if($canceledSubscription->onGracePeriod()){
            $canceledSubscription->resume();
        }
        else{
            //
        }

        return response()->json('success');
    }

    /**
     * Cancel subscription imediately
     */
    public function cancelSubscription(Request $request){
        $subscription = Subscription::findOrFail($request->subscription_reference);
        $subscription->cancelNow();

        return response()->json('success');
    }

    /**
     * Delete subscription
     */
    public function delete($customer_id){
        $customer = Customer::findOrFail($customer_id);

        if($customer->stripe_id){
            $customer->deleteStripeCustomer();
        }
        $customer->delete();

        return redirect()->route('subscriptions');
    }
}
