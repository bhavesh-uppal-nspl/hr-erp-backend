<?php

namespace App\Http\Controllers\GeneralController;

use Illuminate\Support\Facades\Validator;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Database\QueryException;
use App\Http\Controllers\Controller;
use App\Models\GeneralModel\GeneralCountry;
use Illuminate\Http\Request;

class GeneralCountryController extends Controller
{


     public function indexV1()
    {
        try {
            $countries = GeneralCountry::all();

            return response()->json([
                'message' => 'All General Countries',
                'data' => $countries
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Something went wrong. Please try again later.'
            ], 500);
        }
    }


    public function index()
    {
        try {
            $countries = GeneralCountry::all();
            $countries->load('states.cities');
            return response()->json([
                'message' => 'All General Countries',
                'countries' => $countries
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Something went wrong. Please try again later.'
            ], 500);
        }
    }


       public function indexV2()
    {
        try {
            $countries = GeneralCountry::all();
            return response()->json([
                'message' => 'All General Countries',
                'countries' => $countries
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Something went wrong. Please try again later.'
            ], 500);
        }
    }


    public function store(Request $request)
    {
        try {
            // Validate the incoming request data
            $request->validate([
                'country_name' => 'required|string|max:255',
                'country_phone_code' => 'required|regex:/^\+?[0-9]{1,5}$/',
                'country_code' => 'required|string|size:2',
                'country_subcode' => 'nullable|string|size:3',
                'nationality' => 'required|string|max:40',
                'capital' => 'required|string|max:30',

            ]);

            // Create the new organization in the database
            $country = GeneralCountry::create([
                'country_name' => $request->country_name,
                'country_phone_code' => $request->country_phone_code,
                'country_code' => $request->country_code,
                'country_subcode' => $request->country_subcode,
                'nationality' => $request->nationality,
                'capital' => $request->capital,
              

            ]);

            // Return the created organization as a JSON response
            return response()->json([
                'message' => 'Country Addeed SucessFully',
                'Country' => $country
            ], 201);

        } catch (\Illuminate\Database\QueryException $e) {
            // Handle database-specific exceptions (e.g., integrity violations)
            return response()->json([
                'error' => 'Database error: ' . $e->getMessage()
            ], 500);

        } catch (\Exception $e) {
            // Catch any other general exceptions
            return response()->json([
                'error' => 'Something went wrong: ' . $e->getMessage()
            ], 500);
        }
    }

    public function show(Request $request, $country_id)
    {
        try {
            $request->merge(['general_country_id' => $country_id]);
            $validator = Validator::make($request->all(), [
                'general_country_id' => 'required|integer|exists:general_countries,general_country_id',
            ]);
            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()], 422);
            }        
            $country = GeneralCountry::findOrFail($country_id);
            $country->load(['states.cities']);
            return response()->json([
                'message' => 'Country Addeed SucessFully',
                'Country' => $country
            ], 200);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {

            return response()->json([
                'error' => 'Country not found with the provided ID.'
            ], 404);
        } catch (\Exception $e) {
            // Catch any other exceptions
            return response()->json([
                'error' => 'Something went wrong. Please try again later.'
            ], 500);
        }

    }

    public function update(Request $request, $country_id)
    {
        try {
            $request->merge(['general_country_id' => $country_id]);
            $validator = Validator::make($request->all(), [
                'general_country_id' => 'required|integer|exists:general_countries,general_country_id',
                'country_name' => 'sometimes|string|max:255',
                'country_phone_code' => 'sometimes|regex:/^\+?[0-9]{1,5}$/',
                'country_code' => 'sometimes|string|size:3',
                'country_subcode' => 'sometimes|string|size:3',
                'nationality' => 'sometimes|string|max:40',
                'capital' => 'sometimes|string|max:30',
              
            ]);
            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()], 422);
            }
            // Find the organization by ID
            $country = GeneralCountry::findOrFail($country_id);

            // Update the organization with the validated data
            $country->update($request->all());

            return response()->json([
                'message' => 'Country Updated SucessFully',
                'Country' => $country
            ], 200);

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            // If the organization is not found, return an error message
            return response()->json([
                'error' => 'Country not found with the provided ID.'
            ], 404);
        } catch (\Exception $e) {
            // Catch any other exceptions
            return response()->json([
                'error' => 'Something went wrong. Please try again later.'
            ], 500);
        }
    }

 
    public function destroy(Request $request, $country_id)
    {
        try {
            $request->merge(['general_country_id' => $country_id]);
            $validator = Validator::make($request->all(), [
                'general_country_id' => 'required|integer|exists:general_countries,general_country_id',
            ]);
            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()], 422);
            }
            $country = GeneralCountry::find($country_id);
            $country->delete();
    
            return response()->json(['message' => 'Country deleted successfully'], 200);
    
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'error' => 'Country not found with the provided ID.'
            ], 404);
    
        } catch (QueryException $e) {
            // Handles foreign key constraint error
            if ($e->getCode() == 23000) {
                return response()->json([
                    'error' => 'Cannot delete country because it is associated with other records.'
                ], 409); // 409 Conflict
            }
    
            return response()->json([
                'error' => 'A database error occurred.',
                'details' => $e->getMessage()
            ], 500);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Something went wrong. Please try again later.'
            ], 500);
        }
    }
}
