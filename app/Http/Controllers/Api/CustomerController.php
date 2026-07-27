<?php
namespace App\Http\Controllers\Api;
use Illuminate\Http\JsonResponse;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Requests\CustomerRequest;
use App\Http\Resources\CustomerResource;
use App\Models\Customer;


class CustomerController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): JsonResponse
    {        
        // $perPage = $request->input('per_page', 10);
        // $search = $request->input('search');

        try{
            // $query = Customer::query()->select(['id', 'first_name', 'last_name', 'email', 'customer_no','cell_phone','city', 'created_at']);

            // if (!empty($search)) {
            //     $query->where(function($q) use ($search) {
            //         $q->where('first_name', 'LIKE', "%{$search}%")
            //           ->orWhere('last_name', 'LIKE', "%{$search}%")
            //           ->orWhere('email', 'LIKE', "%{$search}%");
            //     });
            // }

            // 💥 CRITICAL FIX: Cursor pagination requires an ordered column!
            // $query->orderBy('id', 'desc');

            // if (!empty($search)) {
            //     // This runs a high-speed "MATCH() AGAINST()" query in MySQL/PostgreSQL
            //     $query->whereFullText(['first_name', 'last_name', 'email'], $search);
            // }

            // Capture standard paginator construct instances
            // $customers = $query->paginate($perPage);

            // $customers = $query->cursorPaginate($perPage); // doesn't return total or last_page, but rather next_cursor and prev_cursor pointers).

            // $customers = $query->cursorPaginate($perPage, ['*'], 'cursor', $request->input('cursor'));

            // $customers = Customer::search($search)->paginate($perPage);


            // Formulate a response payload structure that retains pagination properties
            
            // 2. Formulate the response using metadata that the Cursor Paginator actually provides 
 
            // $responseData = [
            //     'data'        => CustomerResource::collection($customers->items()),
            //     'next_cursor' => $customers->nextCursor() ? $customers->nextCursor()->encode() : null,
            //     'prev_cursor' => $customers->previousCursor() ? $customers->previousCursor()->encode() : null,
            //     'per_page'    => $customers->perPage(),
            //     'has_more'    => $customers->hasMorePages(),
            // ];
            // return $this->sendResponse($responseData, 'Customers retrieved.');   

            // Below code 2M + data search
            $search = $request->input('search', '');
            $perPage = (int) $request->input('per_page', 10);
            $status = $request->input('status');
            $sortOrder = $request->input('sort_order', 'desc') === 'asc' ? 'asc' : 'desc';

            // Initialize Scout Search
            $builder = Customer::search($search ?? '');

            // Filter by status if provided and not 'all'
            if (!empty($status) && $status !== 'all') {
                $builder->where('status', (string) $status);
            }
            $customers = $builder->paginate($perPage);

            // 2. Transform the internal collection using CustomerResource
            $customers->setCollection(
                CustomerResource::collection($customers->getCollection())->collection
            );
                        
            return $this->sendResponse($customers, 'Customers retrieved.'); 

            // return response()->json($customers);

            // 1. IF SEARCH IS PROVIDED -> Search via Meilisearch
            // if (!empty($search)) {
            //     return Customer::search($search)
            //         ->when($status && $status !== 'all', function ($query) use ($status) {
            //             return $query->where('status', $status);
            //         })
            //         ->paginate($perPage);
            // }

            // 2. IF NO SEARCH QUERY -> Fetch straight from Database using Primary Keys
            // MUST have an index on (created_at) or (id) in your MySQL table!
            $query = Customer::query();

            if ($status && $status !== 'all') {
                $query->where('status', $status);
            }

            $customers = $query->orderBy('created_at', $sortOrder)
                               ->paginate($perPage);

            return response()->json($customers);
        }catch (\Throwable $e) {
            \Log::error('Meilisearch Scout Error: ' . $e->getMessage());
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
        // $message = "";
        // $customer_data['customer_no'] = $request->customer_no;
        // $customer_data['gender_id'] = $request->gender_id;
        // $customer_data['country_id'] = $request->country_id;
        // $customer_data['first_name'] = $request->first_name;
        // $customer_data['last_name'] = $request->last_name;
        // $customer_data['cell_phone'] = isset($request->cell_phone) ? $request->cell_phone:'';
        // $customer_data['work_phone'] = isset($request->work_phone) ? $request->work_phone:'';
        // $customer_data['email'] = isset($request->email) ? $request->email:'';
        // $customer_data['date_of_birth'] = isset($request->date_of_birth) ? $request->date_of_birth:'1971-01-01';
        // $customer_data['address'] = isset($request->address) ? $request->address:'';
        // $customer_data['address2'] = isset($request->address2) ? $request->address2:'';
        // $customer_data['city'] = $request->city;
        // $customer_data['zip'] = isset($request->zip) ? $request->zip:'';
        // $customer_created = Customer::create($customer_data);
        $customer_created = Customer::create($request->validated());
        if ($customer_created) {
            // 2. Wrap the new customer model inside the resource
            return $this->sendResponse(new CustomerResource($customer_created), 'Customer Created successfully.', 201);
        } else {
            return response()->json([
                'message' => 'Error: Customer Not Created, Please Try again'
            ], 500);
        }          
        
    }
    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
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
    public function removeCustomer( Request $request )
    {
        if(!empty( $request->item_ids)){
            Customer::destroy( $request->item_ids );
            return response([
                'message' => 'Customer deleted successfully!'
            ],200);
        }
    }
}
