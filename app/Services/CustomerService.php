<?php

namespace App\Services;
use App\Models\Customer;
use App\Http\Resources\CustomerResource;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class CustomerService
{
    /**
     * Fetch paginated customer records with optional Scout search and status filter.
     */
    public function getPaginatedCustomers(array $filters): LengthAwarePaginator
    {
        
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
        $search = $filters['search'] ?? '';
        $perPage = (int) ($filters['per_page'] ?? 10);
        $status = $filters['status'] ?? null;
        $sortOrder = strtolower($filters['sort_order'] ?? 'desc') === 'asc' ? 'asc' : 'desc';

        // Initialize Scout Search
        $builder = Customer::search($search ?? '')
            ->query(fn ($query) => $query->whereNull('deleted_at'));

        // Filter by status if provided and not 'all'
        if (!empty($status) && $status !== 'all') {
            $builder->where('status', (string) $status);
        }
        $customers = $builder->paginate($perPage);

        // 2. Transform the internal collection using CustomerResource
        $customers->setCollection(
            CustomerResource::collection($customers->getCollection())->collection
        );
                        
        return $customers; 

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

        return $customers;         
    }

    /**
     * Create a new customer record.
     */
    public function createCustomer(array $data): Customer
    {
        return Customer::create($data);
    }

    /**
     * Update an existing customer record.
     */
    public function updateCustomer(Customer $customer, array $data): bool
    {
        return $customer->update($data);
    }

    /**
     * Bulk delete customer records.
     */
    public function removeCustomers(array $itemIds): int
    {
        return Customer::whereIn('id', $itemIds)->delete();
    }
}