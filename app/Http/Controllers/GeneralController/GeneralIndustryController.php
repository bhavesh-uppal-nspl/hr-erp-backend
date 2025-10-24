<?php

namespace App\Http\Controllers\GeneralController;

use Illuminate\Support\Facades\Validator;
use App\Http\Controllers\Controller;
use App\Models\GeneralModel\GeneralIndustry;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;

class GeneralIndustryController extends Controller
{

    public function index()
    {
        try {
            $industry = GeneralIndustry::all();
            return response()->json([
                'message' => 'General Industries',
                'industries' => $industry
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Something went wrong. Please try again later.'
            ], 500);
        }
    }

    public function store(Request $request)
    {
        try {
            // Manual validator to catch and customize validation error responses
            $validator = Validator::make($request->all(), [
                'industry_name' => 'required|string|max:255',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'message' => 'Validation errors',
                    'errors' => $validator->errors()
                ], 422);
            }

            // Create the new industry in the database
            $industry = GeneralIndustry::create([
                'industry_name' => $request->industry_name,
            ]);

            // Return the created industry as a JSON response
            return response()->json([
                'message' => 'General Industry Added Successfully',
                'industry' => $industry
            ], 201);

        } catch (\Illuminate\Database\QueryException $e) {
            return response()->json([
                'error' => 'Database error: ' . $e->getMessage()
            ], 500);

        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Something went wrong: ' . $e->getMessage()
            ], 500);
        }
    }

    public function show(Request $request, $industry_id)
    {
        try {

            $request->merge(['general_industry_id' => $industry_id]);
            $validator = Validator::make($request->all(), [
                'general_industry_id' => 'required|integer|exists:general_industries,general_industry_id',

            ]);
            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()], 422);
            }
            $industry = GeneralIndustry::findOrFail($industry_id);
            return response()->json(
                [
                    'message' => 'General Industry',
                    'industry' => $industry
                ],
                200
            );

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {

            return response()->json([
                'error' => 'Industry not found with the provided ID.'
            ], 404);
        } catch (\Exception $e) {
            // Catch any other exceptions
            return response()->json([
                'error' => 'Something went wrong. Please try again later.'
            ], 500);
        }

    }

    public function update(Request $request, $industry_id)
    {
        try {
            $request->merge(['general_industry_id' => $industry_id]);
            $validator = Validator::make($request->all(), [
                'general_industry_id' => 'required|integer|exists:general_industries,general_industry_id',
                'industry_name' => 'sometimes|string|max:255'

            ]);
            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()], 422);
            }
            $industry = GeneralIndustry::findOrFail($industry_id);
            $industry->update($request->all());
            return response()->json([
                'message' => 'General Industry Updated Successfully',
                'industry' => $industry,

            ], 200);


        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            // If the organization is not found, return an error message
            return response()->json([
                'error' => 'Industry not found with the provided ID.'
            ], 404);
        } catch (\Exception $e) {
            // Catch any other exceptions
            return response()->json([
                'error' => 'Something went wrong. Please try again later.'
            ], 500);
        }
    }

    public function destroy(Request $request, $industry_id)
    {
        try {
            $request->merge(['general_industry_id' => $industry_id]);

            // Validate the input
            $validator = Validator::make($request->all(), [
                'general_industry_id' => 'required|integer|exists:general_industries,general_industry_id',
            ]);

            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()], 422);
            }
            $industry = GeneralIndustry::findOrFail($industry_id);
            $industry->delete();

            return response()->json([
                'message' => 'Industry deleted successfully'
            ], 200);

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'error' => 'Industry not found with the provided ID.'
            ], 404);

        } catch (QueryException $e) {
            // Check for foreign key constraint violation (SQL error code 1451)
            if ($e->getCode() === '23000' && str_contains($e->getMessage(), '1451')) {
                return response()->json([
                    'error' => 'Cannot delete this industry because it is linked to other organization records.'
                ], 409); // 409 Conflict
            }

            // Optional fallback for other SQL errors
            return response()->json([
                'error' => 'A database error occurred.',
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
