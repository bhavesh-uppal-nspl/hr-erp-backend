<?php

namespace App\Http\Controllers\GeneralController;

use Illuminate\Support\Facades\Validator;
use App\Http\Controllers\Controller;
use App\Models\GeneralModel\GeneralBusinessOwnershipType;
use Illuminate\Http\Request;

class GeneralBusinessOwnershipTypeController extends Controller
{

    public function index()
    {
        try {
            $organizationownershiptype = GeneralBusinessOwnershipType::all();
            return response()->json([
                "message" => "All Business Ownership Type",
                'businessownershiptypes' => $organizationownershiptype
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Something went wrong. Please try again later.'
            ], 500);
        }
    }


    // store new organizartion 
    public function store(Request $request)
    {
        try {
            // Validate the incoming request data
            $validated = $request->validate([
                'business_ownership_type_name' => 'required|string|max:255',
            ]);

            // Create the new ownership type
            $ownershipType = GeneralBusinessOwnershipType::create($validated);

            // Return the created record
            return response()->json([
                'ownershipType' => $ownershipType,
                'message' => 'Business ownership type created successfully.'
            ], 201);

        } catch (\Illuminate\Database\QueryException $e) {
            return response()->json([
                'error' => 'A database error occurred.',
                'details' => $e->getMessage()
            ], 500);

        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Something went wrong.',
                'details' => $e->getMessage()
            ], 500);
        }
    }


    // display specific organization 
    public function show(Request $request, $organization_ownership_type_id)
    {
        try {
            $request->merge(['general_business_ownership_type_id' => $organization_ownership_type_id]);
            $validator = Validator::make($request->all(), [
                'general_business_ownership_type_id' => 'required|integer|exists:general_business_ownership_types,general_business_ownership_type_id',

            ]);
            // Check if validation fails
            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()], 422);
            }

            $organizationownershiptype = GeneralBusinessOwnershipType::findOrFail($organization_ownership_type_id);
            return response()->json([
                'message' => 'Business Ownership Type',
                'businessownershiptype' => $organizationownershiptype
            ], 200);

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {

            return response()->json([
                'error' => 'General Business Ownership Type  not found with the provided ID.'
            ], 404);
        } catch (\Exception $e) {
            // Catch any other exceptions
            return response()->json([
                'error' => 'Something went wrong. Please try again later.'
            ], 500);
        }

    }


    // update the orgaization 
    public function update(Request $request, $organization_ownership_type_id)
    {
        try {
            // Merge route parameters into the request for validation
            $request->merge([
                'general_business_ownership_type_id' => $organization_ownership_type_id,
            ]);

            // Validation rules including existence checks
            $rules = [
                'general_business_ownership_type_id' => 'required|integer|exists:general_business_ownership_types,general_business_ownership_type_id',
                'business_ownership_type_name' => 'sometimes|string|max:255',

            ];

            // Run validation
            $validator = Validator::make($request->all(), $rules);

            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()], 422);
            }


            // Find the location
            $organizationownershiptype = GeneralBusinessOwnershipType::find($organization_ownership_type_id);

            $organizationownershiptype->update($request->only([
                'business_ownership_type_name'
            ]));

            return response()->json([
                'message' => 'Business Ownership Type updated successfully.',
                'businessownershiptype' => $organizationownershiptype
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Something went wrong. Please try again later.',
                'details' => $e->getMessage()
            ], 500);
        }
    }


    // delete the orgaization  
    public function destroy(Request $request, $organization_ownership_type_id)
    {
        try {
            $request->merge(['general_business_ownership_type_id' => $organization_ownership_type_id]);

            $validator = Validator::make($request->all(), [
                'general_business_ownership_type_id' => 'required|integer|exists:general_business_ownership_types,general_business_ownership_type_id',
            ]);

            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()], 422);
            }
            $organizationownershiptype = GeneralBusinessOwnershipType::findOrFail($organization_ownership_type_id);
            $organizationownershiptype->delete();

            return response()->json([
                'message' => 'Business ownership type deleted successfully'
            ], 200);

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'error' => 'Business ownership type not found with the provided ID.'
            ], 404);

        } catch (\Illuminate\Database\QueryException $e) {
            if ($e->getCode() == 23000) { // Foreign key constraint violation
                return response()->json([
                    'error' => 'Cannot delete this organization ownership type because it is linked with other records.'
                ], 409); // Conflict
            }

            return response()->json([
                'error' => 'Database error: ' . $e->getMessage()
            ], 500);

        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Something went wrong. Please try again later.',
                'details' => $e->getMessage()
            ], 500);
        }
    }

}
