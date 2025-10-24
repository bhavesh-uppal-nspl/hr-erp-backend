<?php

namespace App\Http\Controllers\GeneralController;

use Illuminate\Support\Facades\Validator;
use App\Http\Controllers\Controller;
use App\Models\GeneralModel\GeneralLeaveDuration;
use Illuminate\Http\Request;

class GeneralLeaveDurationTypeController extends Controller
{

    public function index()
    {
        try {
            $leaveduration = GeneralLeaveDuration::all();
            return response()->json([
                'message' => 'All Leave Duartion',
                'duration' => $leaveduration
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
                'leave_duration_type_name' => 'required|string|max:25',
                'description'=>'required|string|max:2000'
            ]);

            // Create the new organization in the database
            $duration = GeneralLeaveDuration::create([
                'leave_duration_type_name' => $request->leave_duration_type_name,
                'description' => $request->description,
            ]);
            return response()->json([
                'message' => 'Leave Duration Addeed SucessFully',
                'duration' => $duration
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

    public function show(Request $request, $duration_id)
    {
        try {
            $request->merge(['general_leave_duration_type_id' => $duration_id]);
            $validator = Validator::make($request->all(), [
                'general_leave_duration_type_id' => 'required|integer|exists:general_leave_duration_types,general_leave_duration_type_id',
            ]);
            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()], 422);
            }
            $duration = GeneralLeaveDuration::findOrFail($duration_id);
            return response()->json([
                'message' => 'Leave Duration Type Addeed SucessFully',
                'duration' => $duration
            ], 200);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {

            return response()->json([
                'error' => 'Leave Duration Type  not found with the provided ID.'
            ], 404);
        } catch (\Exception $e) {
            // Catch any other exceptions
            return response()->json([
                'error' => 'Something went wrong. Please try again later.'
            ], 500);
        }

    }

    public function update(Request $request, $duration_id)
    {
        try {
            $request->merge(['general_leave_duration_type_id' => $duration_id]);
            $validator = Validator::make($request->all(), [
                'general_leave_duration_type_id' => 'required|integer|exists:general_leave_duration_types,general_leave_duration_type_id',
                'leave_duration_type_name' => 'sometimes|string|max:25',
                'description' => 'sometimes|string|max:255',
            ]);
            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()], 422);
            }
            // Find the organization by ID
            $duration = GeneralLeaveDuration::findOrFail($duration_id);
            // Update the organization with the validated data
            $duration->update($request->all());

            return response()->json([
                'message' => 'Leave Duration Updated SucessFully',
                'duration' => $duration
            ], 200);

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            // If the organization is not found, return an error message
            return response()->json([
                'error' => 'Leave Duration  not found with the provided ID.'
            ], 404);
        } catch (\Exception $e) {
            // Catch any other exceptions
            return response()->json([
                'error' => 'Something went wrong. Please try again later.'
            ], 500);
        }
    }

    public function destroy(Request $request, $duration_id)
    {
        try {
            $request->merge(['general_leave_duration_type_id' => $duration_id]);
            $validator = Validator::make($request->all(), [
               'general_leave_duration_type_id' => 'required|integer|exists:general_leave_duration_types,general_leave_duration_type_id',
            ]);
            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()], 422);
            }
            $duration = GeneralLeaveDuration::findOrFail($duration_id);
            $duration->delete();
            return response()->json(['message' => 'Leave Duration deleted successfully'], 200);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {

            return response()->json([
                'error' => 'Leave Duration not found with the provided ID.'
            ], 404);
        }
    }
}
