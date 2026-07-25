<?php
namespace App\Http\Controllers\Api;
use App\Models\Product;
use Illuminate\Http\Request;
use App\Http\Resources\ProductResource;
use App\Http\Requests\StoreProductRequest;

class ProductController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        // return ProductResource::collection(Product::paginate(10));
        return $this->sendResponse(ProductResource::collection(Product::paginate(10)), 'Products retrieved.');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreProductRequest $request){    
        $product = Product::create($request->all());
        return $this->sendResponse(new ProductResource($product), 'Products Stored.', 201);
        // return new ProductResource($product);
    }

    /**
     * Display the specified resource.
     */
    public function show(Product $product)
    {
        return new ProductResource($product);
    }

    /**
     * Update the specified resource in storage.
     */
    // Update product
    public function update(Request $request, Product $product)
    {
        $product->update($request->all());
        // return new ProductResource($product);
        return $this->sendResponse(new ProductResource($product), 'Products Updated.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Product $product)
    {
        $product->delete();
        return $this->sendResponse([], 'Product deleted', 200);
    }
}
