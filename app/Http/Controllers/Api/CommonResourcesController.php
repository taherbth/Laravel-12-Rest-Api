<?php

namespace App\Http\Controllers\Api;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Gender;
use App\Models\Country;
use Illuminate\Http\JsonResponse;
use Exception;

class CommonResourcesController extends Controller
{
    /**
     * Fetch genders and countries for the customer creation dropdowns.
     */
    public function getCommonResources(): JsonResponse
    {
        try {
            // Fetch only necessary columns to keep payload small and performant
            $genders = Gender::all();
            $countries = Country::all();

            return response()->json([
                'success' => true,
                'data' => [
                    'gender_lists' => $genders,
                    'country_lists' => $countries
                ]
            ], 200);

        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to load form options.',
                'error' => $e->getMessage() // Turn off or log in production
            ], 500);
        }
    }
}
