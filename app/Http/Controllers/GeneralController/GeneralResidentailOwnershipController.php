<?php

namespace App\Http\Controllers\GeneralController;

use Illuminate\Support\Facades\Validator;
use App\Http\Controllers\Controller;
use App\Models\GeneralModel\GeneralResidentailOwnershipType;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;

class GeneralResidentailOwnershipController extends Controller
{

    public function index()
    {
        try {
            $residentialownershiptype = GeneralResidentailOwnershipType::all();
            return response()->json([
                'message' => 'Residential ownership types',
                'residentialownershiptype' => $residentialownershiptype
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
            $request->validate([
                  'general_residential_ownership_type_name' => 'sometimes|string|max:255|unique:general_residential_ownership_types,general_residential_ownership_type_name',
            ]);
            $residentialownershiptype = GeneralResidentailOwnershipType::create([
                'general_residential_ownership_type_name ' => $request->general_residential_ownership_type_name,
            ]);
            return response()->json([
                'message' => 'Residentail Ownership Type Addedd SucessFully',
                'residentialownershiptype' => $residentialownershiptype
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

    public function show(Request $request, $residential_ownership_type_id)
    {
        try {
            $request->merge(['general_residential_ownership_type_id' => $residential_ownership_type_id]);
            $validator = Validator::make($request->all(), [
                'general_residential_ownership_type_id' => 'required|integer|exists:general_residential_ownership_types,general_residential_ownership_type_id',
            ]);
            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()], 422);
            }
            $residentialownershiptype = GeneralResidentailOwnershipController::findOrFail($residential_ownership_type_id);
            return response()->json([
                "message" => 'Residential Ownership Type Found',
                'residentialownershiptype' => $residentialownershiptype
            ], 200);

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {

            return response()->json([
                'error' => 'General Location Ownership Type  not found with the provided ID.'
            ], 404);
        } catch (\Exception $e) {
            // Catch any other exceptions
            return response()->json([
                'error' => 'Something went wrong. Please try again later.'
            ], 500);
        }

    }

    public function update(Request $request, $residentail_ownership_type_id)
    {
        try {
            // Merge route parameters into the request for validation
            $request->merge([
                'general_residential_ownership_type_id' => $residentail_ownership_type_id,
            ]);
            // Validation rules including existence checks
            $rules = [
                'general_residential_ownership_type_id' => 'required|integer|exists:general_residential_ownership_types,general_residential_ownership_type_id',
                'general_residential_ownership_type_name' => 'sometimes|string|max:255|unique:general_residential_ownership_types,general_residential_ownership_type_name',
            ];
            // Run validation
            $validator = Validator::make($request->all(), $rules);
            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()], 422);
            }
            $residentialownershiptype = GeneralResidentailOwnershipType::find($residentail_ownership_type_id);

            $residentialownershiptype->update($request->only([
                'general_residential_ownership_type_name'
            ]));

            return response()->json([
                'message' => 'Residential Ownership Type Updated Successfully.',
                'residentialownershiptype' => $residentialownershiptype
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Something went wrong. Please try again later.',
                'details' => $e->getMessage()
            ], 500);
        }
    }

    public function destroy(Request $request, $residentail_ownership_type_id)
    {
        try {
           $request->merge([
                'general_residential_ownership_type_id' => $residentail_ownership_type_id,
            ]);
            $validator = Validator::make($request->all(), [
                'general_residential_ownership_type_id' => 'required|integer|exists:general_residential_ownership_types,general_residential_ownership_type_id',
            ]);
            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()], 422);
            }
            $residentialownershiptype = GeneralResidentailOwnershipType::findOrFail($residentail_ownership_type_id);
            $residentialownershiptype->delete();
            return response()->json([
                'message' => 'Residential Ownership Type Deleted Successfully'
            ], 200);

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'error' => 'General Residential Ownership Type not found with the provided ID.'
            ], 404);

        } catch (QueryException $e) {
            // Foreign key constraint violation (MySQL error code 1451)
            if ($e->getCode() === '23000' && str_contains($e->getMessage(), '1451')) {
                return response()->json([
                    'error' => 'Cannot delete this Residential Ownership Type because it is linked to other records.'
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
