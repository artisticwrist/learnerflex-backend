<?php

namespace App\Http\Controllers\Payment;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Transaction; 
use App\Models\User; 
use App\Models\Product; 
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;

class MarketplacePaymentController extends Controller
{
    //
    public function make_payment()
    {
        // Prepare the data for the payment
        $formData = [
            'email' => request('email'),
            'amount' => 1000 * 100, 
            'currency' => request('currency'),
            'callback_url' => 'https://learnerflex.com/auth/register?otp=sggd63vx7td3dydg3', 
        ];


        // Initialize payment with Paystack
        $pay = json_decode($this->initialize_payment($formData));
    
        if ($pay) {
            // Check if payment initialization was successful
            if ($pay->status) {
                // Return the authorization URL in the JSON response
                return response()->json([
                    'success' => true,
                    'authorization_url' => $pay->data->authorization_url
                ], 200);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => "Something went wrong with the payment initialization."
                ], 401);
            }
        } else {
            return response()->json([
                'success' => false,
                'message' => "Something went wrong with the payment initialization."
            ], 401);
        }
    }

    public function initialize_payment($formData)
    {
        $url = "https://api.paystack.co/transaction/initialize";
        $fields_string = http_build_query($formData);

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $fields_string);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            "Authorization: Bearer " . env("PAYSTACK_SECRET_KEY"),
            "Cache-Control: no-cache"
        ));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        $result = curl_exec($ch);

        return $result;
    }

    public function payment_callback()
    {
        $reference = request('reference');
        $response = json_decode($this->verify_payment($reference));
        
        do {
            $aff_id = Str::random(20);
            $exists = DB::table('users')->where('aff_id', $aff_id)->exists();
        } while ($exists);
            
        if ($response && $response->status == "success") {

            $user = User::create([
                'aff_id' => $aff_id,
                'name' => null,
                'email' => request('email'),
                'phone' => null,
                'password' => null,
                'country' => null,
                'refferal_id' => 0,
                'image' => null,
                'has_paid_onboard' => 1,
                'is_vendor' => 0,
                'vendor_status' => 'down',
                'otp' => 'sggd63vx7td3dydg3',
                'market_access' => 1,    
            ]);

            Transaction::create([
                'user_id' => 0, 
                'email' => request('email'),
                'affiliate_id' => 0,
                'product_id' => 0,
                'amount' => request('amount'),
                'currency' => request('currency'),
                'status' => 'success',
                'org_company' => 0,
                'org_vendor' => 0,
                'org_aff' => 0,
                'is_onboard' => 1,
                'tx_ref' => request('reference')
            ]);

            return response()->json([
                'success' => true,
                'user' => $user,
            ]);
            
        } else {

            $status = $response->status == "pending" ? 'pending' : 'failed';

            $user = User::create([
                'aff_id' => $aff_id,
                'name' => null,
                'email' => request('email'),
                'phone' => null,
                'password' => null,
                'country' => null,
                'refferal_id' => 0,
                'image' => null,
                'has_paid_onboard' => 1,
                'is_vendor' => 0,
                'vendor_status' => 'down',
                'otp' => 'sggd63vx7td3dydg3',
                'market_access' => 1,    
            ]);

            Transaction::create([
                'user_id' => 0,
                'email' => request('email'),
                'affiliate_id' => 0,
                'product_id' => 0,
                'amount' => request('amount'),
                'currency' => request('currency'),
                'status' => $status,
                'org_company' => 0,
                'org_vendor' => 0,
                'org_aff' => 0,
                'is_onboard' => 1,
                'tx_ref' => request('reference')
            ]);

        }
    }

    public function verify_payment($reference)
    {
        $url = "https://api.paystack.co/transaction/verify/" . rawurlencode($reference);

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            "Authorization: Bearer " . env("PAYSTACK_SECRET_KEY"),
            "Cache-Control: no-cache"
        ));

        $result = curl_exec($ch);


        curl_close($ch);

        return $result;
    }
}




