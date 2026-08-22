<?php
namespace App\Http\Controllers\Api;
use App\Http\Resources\CustomerResource;
use App\Http\Requests\CustomerRequest;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use App\Services\CustomerService;
use Illuminate\Http\Request;
use App\Models\Customer;
use Throwable;

class CustomerController extends Controller
{
    protected CustomerService $customerService;

    public function __construct(CustomerService $customerService)
    {
        $this->customerService = $customerService;
    }
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): JsonResponse
    {        
        try {
            $filters = $request->only(['search', 'per_page', 'status', 'sort_order']);
            $customers = $this->customerService->getPaginatedCustomers($filters);

            return $this->sendResponse($customers, 'Customers retrieved.');
        } catch (Throwable $e) {
            \Log::error('Search Operation Error: ' . $e->getMessage());
            return response()->json([
                'error' => 'Search operation failed',
                'message' => $e->getMessage()
            ], 500);
        }
    }
    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(CustomerRequest $request ): JsonResponse
    {                 
       $customer = $this->customerService->createCustomer($request->validated());

        if($customer) {
            return $this->sendResponse(new CustomerResource($customer), 'Customer created successfully.', 201);
        }
        return response()->json([
            'message' => 'Error: Customer not created, Please try again'
        ], 500); 
    }
    /**
     * Display the specified resource.
     */
    /**
     * Display the specified customer.
     */
    public function show($id): JsonResponse
    {
        $customer = Customer::find($id);

        if (!$customer) {
            return response()->json([
                'status' => 404,
                'message' => 'Customer not found.'
            ], 404);
        }

        return response()->json([
            'status' => 200,
            'data'   => $customer
        ], 200);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        echo "Hello === ".$id;
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(CustomerRequest $request , string $id): JsonResponse
    {      
        $customer = Customer::find($id);
        if (!$customer) {
            return response()->json([
                'message' => 'Error: Customer not found.'
            ], 404);
        }

        $updated = $this->customerService->updateCustomer($customer, $request->validated());

        if ($updated) {
            return $this->sendResponse(new CustomerResource($customer), 'Customer updated successfully.', 200);
        }

        return response()->json([
            'message' => 'Error: Unable to update customer, Please Try again.'
        ], 500);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function removeCustomer( Request $request ) : JsonResponse
    {   
        $itemIds = $request->input('item_ids');

        if (empty($itemIds) || !is_array($itemIds)) {
            return response()->json([
                'success' => false,
                'message' => 'No items selected for deletion.'
            ], 400);
        }

        $this->customerService->removeCustomers($itemIds);

        return response()->json([
            'success' => true,
            'message' => 'Customer(s) deleted successfully.'
        ]);
    }
}
