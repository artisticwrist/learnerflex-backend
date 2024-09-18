<?php

namespace App\Http\Controllers\Payment;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Unicodeveloper\Paystack\Facades\Paystack;
use App\Models\Transaction; 
use App\Models\Sale; 
use App\Models\User; 
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;

class PaystackController extends Controller
{


    public function make_payment(){
        $formData = [
            'user_id' => request('user_id'),
            'aff_id' => request('aff_id'),
            'product_id' => request('product_id'),
            'amount' => request('amount') * 100,
            'email' => request('email'),
            'currency' => request('currency'),
            'callback_url' => route('callback')
        ];
    
        // Initialize payment with Paystack
        $pay = json_decode($this->initialize_payment($formData));
    
        if($pay){
            // Check if payment initialization was successful
            if($pay->status){
                // Redirect to the authorization URL
                return redirect($pay->data->authorization_url);
            }else{
                return response()->json([
                    'success'=> false,
                    'message' => "Something went wrong with the payment initialization."
                ], 401);
            }
        }else{
            return response()->json([
                'success'=> false,
                'message' => "Something went wrong with the payment initialization."
            ], 401);

        }
    }
    


    public function initialize_payment($formData){
        $url ="https://api.paystack.co/transaction/initialize";
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
        curl_close($ch);
        return $result;
    }

    public function payment_callback(){
        $response = json_decode($this->verify_payment(request('reference')));  
        $amount = request('amount');
        $aff_id = request('aff_id');

        $getAffiliateId = User::where('aff_id', $aff_id)->first();

        if($getAffiliateId){
            $affiliate_id = $getAffiliateId->id;
        }else{
            $affiliate_id = 0;
        }

        // Calculate shares
        $org_company_share = $amount * 0.05; // 5% to company
        $org_vendor_share = $amount * 0.45;  // 45% to vendor
        $org_aff_share = $amount * 0.50;     // 50% to affiliate

        if($response){
            if($response->status){
                Transaction::create([
                    'user_id' => request('user_id'),
                    'email'=> request('email'),
                    'affiliate_id' => $affiliate_id,
                    'product_id' => request('product_id'),
                    'amount' => request('amount'),
                    'currency' => request('currency'),
                    'status' => 'success',
                    'org_company' => $org_company_share,
                    'org_vendor' => $org_vendor_share,
                    'org_aff' => $org_aff_share,
                    'is_onboard' => 1,
                    'tx_ref' => request('reference')
                ]);

                Sale::create([
                     'affiliate_id' => $affiliate_id,
                    'product_id' => request('product_id'),
                    'user_id' => request('user_id'),
                    'transaction_id' => request('reference'),
                    'amount' => request('amount'),
                ]);

                $checkUser = User::where('email', request('email'))->first();

                if(!$checkUser){
                    Mail::to(request('email'))->send(new \App\Mail\RegisterLink($aff_id));  
                }

                return response()->json([
                    'success'=> true,
                    'message' => "  Payment successful."
                ], 200);
                
            }elseif ($response->status == "pending") {
                Transaction::create([
                    'email'=> request('email'),
                    'user_id' => request('user_id'),
                    'affiliate_id' => $affiliate_id,
                    'product_id' => request('product_id'),
                    'amount' => request('amount'),
                    'currency' => request('currency'),
                    'status' => 'pending',
                    'org_company' => 0,
                    'org_vendor' => 0,
                    'org_aff' => 0,
                    'is_onboard' => 1,
                    'tx_ref' => request('reference')
                ]); 

                return response()->json([
                    'success'=> true,
                    'message' => "  Payment pending."
                ], 200);
            }elseif ($response->status == "failed") {
                Transaction::create([
                    'email'=> request('email'),
                    'user_id' => request('user_id'),
                    'affiliate_id' => $affiliate_id,
                    'product_id' => request('product_id'),
                    'amount' => request('amount'),
                    'currency' => request('currency'),
                    'status' => 'failed',
                    'org_company' => 0,
                    'org_vendor' => 0,
                    'org_aff' => 0,
                    'is_onboard' => 1,
                    'tx_ref' => request('reference')
                ]);

                return response()->json([
                    'success'=> true,
                    'message' => "  Payment failed."
                ], 200);

            }else{
                Transaction::create([
                    'email'=> request('email'),
                    'user_id' => request('user_id'),
                    'affiliate_id' => $affiliate_id,
                    'product_id' => request('product_id'),
                    'amount' => request('amount'),
                    'currency' => request('currency'),
                    'status' => 'failed',
                    'org_company' => 0,
                    'org_vendor' => 0,
                    'org_aff' => 0,
                    'is_onboard' => 1,
                    'tx_ref' => request('reference')
                ]);

                return response()->json([
                    'success'=> true,
                    'message' => "  Payment failed."
                ], 200);
            }
        }else{
            return response()->json([
                'success'=> false,
                'message' => "Invalid reference."
            ], 401);
        }


    }

    public function verify_payment($reference){
        $curl = curl_init();
        curl_setopt_array($curl,array(
            CURLOPT_URL => "https://api.paystack.co/transaction/verify/$reference",
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "GET",
            CURLOPT_HTTPHEADER => array(
                "Authorization: Bearer " . env("PAYSTACK_SECRET_KEY"),
                "Cache-Control: no-cache"
            )
        ));

        $response = curl_exec($curl);
        curl_close($curl);
        return $response;
    }
   
}
    // public function make_payment(){
    //     $formData = [
    //         'user_id' => request('user_id'),
    //         'aff_id' => request('aff_id'),
    //         'product_id' => request('product_id'),
    //         'amount' => request('amount') * 100,
    //         'email' => request('email'),
    //         'currency' => request('currency'),
    //     ];
    
    //     // Initialize payment with Paystack
    //     $pay = json_decode($this->initialize_payment($formData));
    
    //     if($pay){
    //         // Check if payment initialization was successful
    //         if($pay->status){
    //             // Redirect to the authorization URL
    //             return redirect($pay->data->authorization_url);
    //         }else{
    //             return response()->json([
    //                 'success'=> false,
    //                 'message' => "Something went wrong with the payment initialization."
    //             ], 401);
    //         }
    //     }else{
    //         return response()->json([
    //             'success'=> false,
    //             'message' => "Somethings went wrong with the payment initialization."
    //         ], 401);

    //     }
    // }