<?php

namespace App\Http\Controllers\Vendor;

use App\Http\Controllers\Controller;
use App\Http\Requests\VendorRequest;
use App\Models\User;
use App\Models\Sale;
use App\Models\Vendor;
use App\Service\UserService;
use App\Service\VendorService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use App\Mail\VendorAccountWanted;
use App\Models\Transaction;
use Illuminate\Support\Facades\Mail;



class VendorController extends Controller
{
    protected $userService;
    protected $vendorService;

    public function __construct(UserService $userService, VendorService $vendorService)
    {
        $this->userService = $userService;
        $this->vendorService = $vendorService;
    }

    public function index(User $user): JsonResponse
    {
        try {
            $vendor = $this->userService->getUserVendor($user);
            return $this->success($vendor, 'Retrieved user vendor data!');
        } catch (\Throwable $th) {
            return $this->error([], $th->getMessage(), 400);
        }
    }

    
    public function store(Request $request)
    {
        // Validate the incoming request
        $validator = Validator::make($request->all(), [
            'user_id' => 'required|exists:users,id',
            'name' => 'required|string|max:255',
        ]);

        // Check if validation fails
        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        // Check if the user_id exists in the users table
        $userExists = User::where('id', $request->user_id)->exists();
        if (!$userExists) {
            return response()->json(['message' => 'Invalid user_id, user does not exist'], 422);
        }

        DB::table('vendors')->insert([
            'user_id' => $request->user_id,
            'name' => $request->name,
            'description' => "New Vendor",
            'photo' => null,   
            'created_at' => now(),
            'updated_at' => now(),         
        ]);

        $user_id = $request->user_id;
    
        // Delete any existing vendor_status entries for this user
        DB::statement("DELETE FROM vendor_status WHERE user_id = ?", [$user_id]);
    
        // Update the is_vendor column in the users table to 1
        User::where('id', $user_id)->update(['is_vendor' => 1, 'vendor_status' => 'up']);
    
        // Return a successful JSON response
        return response()->json([
            'message' => 'Vendor created successfully and user updated to vendor',
        ], 201);
    }

    public function sendVendorRequest(Request $request){
            
            $validate = $request->validate([
                'email' => 'bail|required|string',
                'sale_url' => 'required|string',
            ]);
            
            $user = User::where('email', $validate['email'])->first();
            
            if (!$user) {
                return response()->json(['error' => 'Not a user. Cannot request for vendor'], 400);
            }
            
            $user_id = $user->id;
            $saleurl = $validate['sale_url'];
            
            DB::table('vendor_status')->insert([
                'user_id' => $user_id,
                'sale_url' => $validate['sale_url'],
                'created_at' => now(),
                'updated_at' => now(),            
            ]);

            Mail::to('learnerflex@gmail.com')->send(new VendorAccountWanted($user, $saleurl));
            
            return response()->json(['success' => true, 'message' => 'Vendor Request sent successfully'], 201);
    
        }

        

    public function updateVendorProfile(Request $request){

        $validator = Validator::make($request->all(), [
            'email' => 'bail|required|string',
            'photo' => 'required|exists:users,id',
            'description' => 'required|string|max:255',
        ]); 


        // Check if validation fails
        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }


        $user = User::where('email', $request->email)->first();
            
        if (!$user) {
            return response()->json(['error' => 'Not a user'], 400);
        }        

    }

    

    // public function store(VendorRequest $request): JsonResponse
    // {
    //     try {
    //         $validatedData = $request->validated();
    //         if ($request->hasFile('photo') && $request->file('photo')->isValid()) {
    //             $path = $request->file('photo')->store('images/vendors', 'public');
    //             $validatedData['photo'] = $path;
    //         } else {
    //             $validatedData['photo'] = null;
    //         }
    //         $user = $request->user();
    //         $result = $this->userService->createVendorForUser($user, $validatedData);
    //         $result['photo'] = $result->photo ? Storage::url($result->photo) : null;
    //         return $this->success($result, 'Vendor Created!', Response::HTTP_CREATED);
    //     } catch (\Throwable $th) {
    //         Log::error('Vendor creation failed', ['error' => $th->getMessage()]);
    //         return $this->error(null, $th->getMessage(), Response::HTTP_BAD_REQUEST);
    //     }
    // }

    public function delete(int $vendor_id): JsonResponse
    {
        try {
            $vendor = $this->vendorService->deleteVendor($vendor_id);
            return $this->success($vendor, 'deleted vendor!');
        } catch (\Throwable $th) {
            return $this->error([], $th->getMessage(), 400);
        }
    }

    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        // Check if the user exists
        $user = User::where('email', $request->email)->first();
        
        if (!$user || !Hash::check($request->password, $user->password)) {
            return response()->json(['message' => 'Invalid credentials'], 401);
        }

        // Check if the user is a vendor
        $vendor = Vendor::where('user_id', $user->id)->first();

        if (!$vendor) {
            return response()->json(['message' => 'User is not a vendor'], 403);
        }

        // Generate a token or handle login logic
        $token = $user->createToken('VendorAuthToken')->plainTextToken;

        return response()->json([
            'message' => 'Login successful',
            'token' => $token,
            'vendor' => $vendor
        ]);
    }

    public function getVendorSales($id){
       $sales = Sale::where('user_id', $id)->get();

        return response()->json([
            'success'=> true,
            'message' => 'transaction successful sales',
            'Sales' => $sales
        ]);
   
    }

    public function getVendorTotalSaleAmount($id){
        $totalAmount = Sale::where('user_id', $id)->sum('amount');

        return response()->json([
            'success'=> true,
            'message' => 'total amount sales made',
            'Total sale' => $totalAmount
        ]);
     }

     public function vendorEarnings($id){
        $totalAmount = Transaction::where('user_id', $id)->sum('org_vendor');

        return response()->json([
            'success'=> true,
            'message' => 'total earnings for withdrawal',
            'Total sale' => $totalAmount
        ]);

     }

     public function getStudentEmailsAndNames($id){
        $emails = Transaction::where('user_id', $id)
            ->distinct('email')
            ->pluck('email');
    
        $users = User::whereIn('email', $emails)->get(['email', 'name']);
    
        $result = $users->mapWithKeys(function ($user) {
            return [$user->email => $user->name];
        });

        return response()->json([
            'success'=> true,
            'message' => 'Students retrievd successfully',
            'Students' => $result
        ]);
    
    }
    
}
