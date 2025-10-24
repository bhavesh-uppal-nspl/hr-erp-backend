<?php

namespace App\Http\Controllers\GeneralController;

use Illuminate\Support\Facades\Validator;
use App\Http\Controllers\Controller;
use App\Models\GeneralModel\GeneralLocationOwnershipType;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;

class GeneralLocationOwnershipTypeController extends Controller
{

    public function index()
    {
        try {
            $locationnownershiptype = GeneralLocationOwnershipType::all();
            return response()->json([
                'message'=>'all location ownership types',
                'locationnownershiptypes' => $locationnownershiptype
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
                'location_ownership_type_name' => 'required|string|max:255',
            ]);
            // Create the new organization in the database
            $locationownershiptype = GeneralLocationOwnershipType::create([
                'location_ownership_type_name' => $request->location_ownership_type_name,
            ]);

            // Return the created organization as a JSON response
            return response()->json([
                'message'=>'Location Ownership Type Addedd SucessFully',
                'locationownershiptype' => $locationownershiptype
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

    public function show(Request $request,$location_ownership_type_id )
    {
        try {  
            $request->merge(['general_location_ownership_type_id' => $location_ownership_type_id]);
            $validator = Validator::make($request->all(), [
                'general_location_ownership_type_id' => 'required|integer|exists:general_location_ownership_types,general_location_ownership_type_id',
            ]);
            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()], 422);
            }
            $locationownershiptype = GeneralLocationOwnershipType::findOrFail($location_ownership_type_id);
            return response()->json([
                "message"=>'Location Ownership Type',
                'locationownershiptype' => $locationownershiptype
            ], 200);

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {

            return response()->json([
                'error' => 'General Location Ownership Type  not found with the provided ID.'
            ], 404);
        }
        catch (\Exception $e) {
            // Catch any other exceptions
            return response()->json([
                'error' => 'Something went wrong. Please try again later.'
            ], 500);
        }

    }

    public function update(Request $request, $location_ownership_type_id)
    {
        try {
            // Merge route parameters into the request for validation
            $request->merge([
                'general_location_ownership_type_id' => $location_ownership_type_id,
            ]);
            // Validation rules including existence checks
            $rules = [
                'general_location_ownership_type_id' => 'required|integer|exists:general_location_ownership_types,general_location_ownership_type_id',
                'location_ownership_type_name' => 'sometimes|string|max:255',

            ];
            // Run validation
            $validator = Validator::make($request->all(), $rules);
            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()], 422);
            }
            // Find the location
            $locationownershiptype = GeneralLocationOwnershipType::find($location_ownership_type_id);

            $locationownershiptype->update($request->only([
                'location_ownership_type_name'
            ]));

            return response()->json([
                'message' => 'Location Ownership Type Updated Successfully.',
                'locationownershiptype' => $locationownershiptype
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Something went wrong. Please try again later.',
                'details' => $e->getMessage()
            ], 500);
        }
    }
 
    public function destroy(Request $request, $location_ownership_type_id)
    {
        try {
            $request->merge(['general_location_ownership_type_id' => $location_ownership_type_id]);
            // Validate input
            $validator = Validator::make($request->all(), [
                'general_location_ownership_type_id' => 'required|integer|exists:general_location_ownership_types,general_location_ownership_type_id',
            ]);
            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()], 422);
            }
            $locationownershiptype = GeneralLocationOwnershipType::findOrFail($location_ownership_type_id);
            $locationownershiptype->delete();
    
            return response()->json([
                'message' => 'Location ownership type deleted successfully'
            ], 200);
    
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'error' => 'General Location Ownership Type not found with the provided ID.'
            ], 404);
    
        } catch (QueryException $e) {
            // Foreign key constraint violation (MySQL error code 1451)
            if ($e->getCode() === '23000' && str_contains($e->getMessage(), '1451')) {
                return response()->json([
                    'error' => 'Cannot delete this Location Ownership Type because it is linked to other records.'
                ], 409); // Conflict
            }
    
            return response()->json([
                'error' => 'Database error occurred.',
                'details' => $e->getMessage()
            ], 500);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'An unexpected error occurred.',
                'details' => $e->getMessage()
            ], 500);
        }
    }
}
