<?php

namespace App\Http\Controllers;

use App\Http\Requests\CreateCustomerRequest;
use App\Http\Requests\DeleteCustomerRequest;
use App\Http\Requests\UpdateBillingRequest;
use App\Http\Requests\UpdateCustomerRequest;
use App\Models\ApiKey;
use App\Models\Customer;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use phpDocumentor\Reflection\Types\Null_;

class CustomerController extends Controller
{
    /**
     * List all customers
     */
    public function index(){
        $customers = Customer::all();
        $products = Product::all();

        return view('customers.index', compact('customers', 'products'));
    }

    /**
     * Create new customer
     */
    public function store(CreateCustomerRequest $request){
        $apiKey = ApiKey::where('client_id', $request->header('client-id'))->first();

        $company = $request->input('company');
        $user = $request->input('user');
        $customer = Customer::where([
            'company_id' => $company['id'],
            'product_id' => $apiKey->product_id
        ])->first();

        if(!$customer){
            // create customer
            $customer = Customer::create([
                'name' => $company['name'],
                'email' => $user['email'],
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
                'company_id' => $company['id'],
                'product_id' => $apiKey->product_id
            ]);
        }

        return response()->json([
            'customer_id' => $customer->id
        ]);
    }

    /**
     * Update customer data
     */
    public function update(UpdateCustomerRequest $request){
        $apiKey = ApiKey::where('client_id', $request->header('client-id'))->first();
        $customer = Customer::where([
            'company_id' => $request->input('company_id'),
            'product_id' => $apiKey->product_id
        ])->first();

        if($customer){
            if($request->input('email')){
                $customer->email = $request->input('email');
            }
            if($request->input('name')){
                $customer->name = $request->input('name');
            }
            $customer->save();

            return response()->json([
                'customer_id' => $customer->id
            ]);
        }
        else{
            return response()->json([
                'message' => 'Customer not found'
            ]);
        }

    }

    /**
     * Delete a signgle customer
     */
    public function delete(DeleteCustomerRequest $request){
        $apiKey = ApiKey::where('client_id', $request->header('client-id'))->first();
        $customer = Customer::where([
            'company_id' => $request->input('company_id'),
            'product_id' => $apiKey->product_id
        ])->firstOrFail();

        if($customer->stripe_id){
            $customer->deleteStripeCustomer();
        }
        $customer->delete();

        return response()->json([
            'message' => 'Customer deleted'
        ]);
    }

    /**
     * Update billing details of the customer
     */
    public function updateBilling(UpdateBillingRequest $request){
        $apiKey = ApiKey::where('client_id', $request->header('client-id'))->firstOrFail();

        $customer = Customer::where(['company_id' => $request->company_id, 'product_id' => $apiKey->product_id])->firstOrFail();
        $customer->billing_details = $request->billing_info;
        $customer->save();

        return response()->json('success');
    }

    /**
     * Delete billing details of the customer
     */
    public function deleteBilling(Request $request){
        $apiKey = ApiKey::where('client_id', $request->header('client-id'))->firstOrFail();

        $customer = Customer::where(['company_id' => $request->company_id, 'product_id' => $apiKey->product_id])->firstOrFail();
        $customer->billing_details = null;
        $customer->save();

        return response()->json('success');
    }
}
