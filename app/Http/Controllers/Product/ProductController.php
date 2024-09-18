<?php

namespace App\Http\Controllers\Product;

use App\Http\Controllers\Controller;
use App\Http\Requests\DigitalProductRequest;
use App\Http\Requests\OtherProductRequest;
use App\Models\Vendor;
use App\Models\User;
use App\Service\ProductService;
use App\Service\VendorService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use App\Models\Transaction; 
use App\Models\Product;

class ProductController extends Controller
{
    protected $productService;
    protected $vendorService;
    public function __construct(ProductService $productService, VendorService $vendorService)
    {
        $this->productService = $productService;
        $this->vendorService = $vendorService;
    }


    public function getProduct($id, $reffer_id)
    {
        // Check if the user has a referral_id of 0 or null
        $checkUser = User::where('id', $id)
                         ->where(function($query) {
                             $query->where('refferal_id', 0)
                                   ->orWhereNull('refferal_id');
                         })
                         ->first();
    
        // If user has no referral_id (0 or null), return all products
        if ($checkUser) {
            return Product::all();
        } else {
            // Otherwise, return products by the given referral ID
            return Product::where('refferal_id', $reffer_id)->get();
        }
    }
    


    public function viewProductsByVendor($vendor_id) {
        // Retrieve all products that match the given vendor_id
        $products = Product::where('vendor_id', $vendor_id)->get();
    
        // Check if products are found
        if ($products->isEmpty()) {
            return response()->json([
                'message' => 'No products found for this vendor.',
            ], 404);
        }
    
        // Return the products as a JSON response
        return response()->json([
            'message' => 'Products retrieved successfully!',
            'products' => $products
        ], 200);
    }

    public function viewProductByVendor($vendor_id, $product_id) {
        // Retrieve all products that match the given vendor_id
        $products = Product::where('vendor_id', $vendor_id)->where('id', $product_id)->get();
    
        // Check if products are found
        if ($products->isEmpty()) {
            return response()->json([
                'message' => 'product not found.',
            ], 404);
        }
    
        // Return the products as a JSON response
        return response()->json([
            'message' => 'Product Retrieved!',
            'products' => $products
        ], 200);
    }
    
    public function addProduct(Request $request) {
        // Validate the incoming request data
        // $validate = $request->validate([
        //     'user_id' => 'bail|required|integer',
        //     'name' => 'required|string',
        //     'description' => 'sometimes|nullable|string',
        //     'price' => 'required|numeric|min:0',
        //     'type' => 'required|string', // You might want to validate this against specific enum values
        //     'commission' => 'sometimes|nullable|string',
        //     'contact_email' => 'sometimes|nullable|string',
        //     'vsl_pa_link' => 'sometimes|nullable|string',
        //     'access_link' => 'sometimes|nullable|string',
        //     'sale_page_link' => 'sometimes|nullable|string',
        //     'sale_challenge_link' => 'sometimes|nullable|string',
        //     'promotional_material' => 'sometimes|nullable|string',
        //     'x_link' => 'sometimes|nullable|string',
        //     'ig_link' => 'sometimes|nullable|string',
        //     'yt_link' => 'sometimes|nullable|string',
        //     'tt_link' => 'sometimes|nullable|string',
        //     'fb_link' => 'sometimes|nullable|string',
        // ]);

        $validate = $request->validate([
            '*' => 'sometimes|nullable',
        ]);


        $vendor = Vendor::where('user_id', $request->user_id)->first();

        if(!$vendor){
            return response()->json([
                'message' => 'vendor not found'
            ]);
        }

        $vendor_id = $vendor->id;

    
        //Create a new product with the validated data
        $product = Product::create([
            'user_id' => $request->input('user_id'),
            'vendor_id' => $vendor_id,
            'name' => $request->input('name'),
            'description' => $request->input('description'),
            'image' => null, // Save the image name in the database
            'price' => $request->input('price'),
            'old_price' => $request->input('price'),
            'type' => $request->input('type'),
            'commission' => $request->input('commission'),
            'contact_email' => $request->input('contact_email'),
            'vsl_pa_link' => $request->input('vsl_pa_link'),
            'access_link' => $request->input('access_link'),
            'sale_page_link' => $request->input('sale_page_link'),
            'sale_challenge_link' => $request->input('sale_challenge_link'),
            'promotional_material' => $request->input('promotional_material'),
            'is_partnership' => 0,
            'is_affiliated' => 1,
            'x_link' => $request->input('x_link'),
            'ig_link' => $request->input('ig_link'),
            'yt_link' => $request->input('yt_link'),
            'tt_link' => $request->input('tt_link'),
            'fb_link' => $request->input('fb_link'),
            'status' => 'pending', // Default status
        ]);
    
        return response()->json([
            'success' => true,
            'message' => 'Product created successfully!',
            'product' => $product
        ], 201);
    }
    

    public function deleteProduct($id)
    {
        // Find the product by its ID
        $product = Product::find($id);

        // Check if the product exists
        if (!$product) {
            return response()->json([
                'message' => 'Product not found.',
            ], 404);
        }

        // Delete the product
        $product->delete();

        // Return a success response
        return response()->json([
            'success' => true,
            'message' => 'Product deleted successfully!',
        ], 200);
    }


    
    

    public function index(): JsonResponse
    {
        try {
            $products = $this->productService->getAllProducts();
            return $this->success($products, 'All Products!');
        } catch (\Throwable $th) {
            return $this->error([], $th->getMessage(), Response::HTTP_BAD_REQUEST);
        }
    }

    public function getApprovedProducts(string $status): JsonResponse
    {
        try {
            $products = $this->productService->getProductsWhereStatus($status);
            return $this->success($products, 'Products with status of approved!');
        } catch (\Throwable $th) {
            return $this->error([], $th->getMessage(), Response::HTTP_BAD_REQUEST);
        }
    }

    public function createDigitalProduct(DigitalProductRequest $digitalProductRequest): JsonResponse
    {
        try {
            $user = $digitalProductRequest->user();
            $productData = $digitalProductRequest->validated();
            $productData['user_id'] = $user->id;
            $digitalProduct = $this->vendorService->newVendorProduct($user->vendor, $productData);
            return $this->success($digitalProduct, 'Digital Product Pending!', Response::HTTP_CREATED);
        } catch (\Throwable $th) {
            return $this->error([], $th->getMessage(), Response::HTTP_BAD_REQUEST);
        }
    }

    public function createOtherProduct(OtherProductRequest $otherProductRequest): JsonResponse
    {
        try {
            $user = $otherProductRequest->user();
            $productData = $otherProductRequest->validated();
            if ($otherProductRequest->hasFile('image') && $otherProductRequest->file('image')->isValid()) {
                $path = $otherProductRequest->file('image')->store('images/products', 'public');
                $productData['image'] = $path;
            } else {
                $productData['image'] = null;
            }
            $productData['user_id'] = $user->id;
            $otherProduct = $this->vendorService->newVendorProduct($user->vendor, $productData);
            $otherProduct['image'] = $otherProduct->image ? Storage::url($otherProduct->image) : null;
            return $this->success($otherProduct, 'Other Product Created!', Response::HTTP_CREATED);
        } catch (\Throwable $th) {
            Log::error('other product creation failed', ['error' => $th->getMessage()]);
            return $this->error(null, $th->getMessage(), Response::HTTP_BAD_REQUEST);
        }
    }

    public function show(int $product): JsonResponse
    {
        try {
            $product = $this->productService->getProductById($product);
            return $this->success($product, 'Single Product!');
        } catch (\Throwable $th) {
            return $this->error([], $th->getMessage(), Response::HTTP_BAD_REQUEST);
        }
    }

    public function edit(int $product, Request $request): JsonResponse
    {
        try {
            $product = $this->productService->updateProductById($product, $request->all());
            return $this->success($product, 'Updated Product!');
        } catch (\Throwable $th) {
            return $this->error([], $th->getMessage(), Response::HTTP_BAD_REQUEST);
        }
    }

    public function destroy(int $product): JsonResponse
    {
        try {
            $product = $this->productService->deleteOneProduct($product);
            return $this->success($product, 'Product Removed!');
        } catch (\Throwable $th) {
            return $this->error([], $th->getMessage(), Response::HTTP_BAD_REQUEST);
        }
    }

    public function unlockMarketAccess(Request $request)
    {
        try {
            $user = $request->user();
            $result = $this->productService->generateMarketAccessPayment($user);
            return $this->success($result, 'unlock market');
        } catch (\Throwable $th) {
            Log::error("unlock market: $th");
            return $this->error([], $th->getMessage(), 400);
        }
    }

    
    public function addToCart(Request $request)
    {
        // Validate the request
        $validated = $request->validate([
            'user_id' => 'required|exists:users,id',
            'affiliate_id' => 'required|exists:affiliates,id',
            'amount' => 'required|numeric|min:0',
        ]);


        // Create a new cart entry
        $cart = Transaction::create([
            'user_id' => $validated['user_id'],
            'affiliate_id' => $validated['affiliate_id'],
            'amount' => $validated['amount'],
            'status' => 'pending',
            'tx_ref' => 'pending',
            'currency' => 'NGN',
        ]);

        // Return response
        return response()->json([
            'status' => 'success',
            'message' => 'Item added to cart successfully.',
            'data' => $cart
        ]);
    }

    /**
     * Generate a unique transaction reference.
     *
     * @return string
     */
    protected function generateUniqueTxRef()
    {
        do {
            $tx_ref = 'TX-' . strtoupper(Str::random(10)) . '-' . time();
        } while (Transaction::where('tx_ref', $tx_ref)->exists());

        return $tx_ref;
    }
}
