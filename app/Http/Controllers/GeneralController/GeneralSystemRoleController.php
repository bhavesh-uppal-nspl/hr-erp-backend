<?php

namespace App\Http\Controllers\GeneralController;

use Illuminate\Support\Facades\Validator;
use App\Http\Controllers\Controller;
use App\Models\GeneralModel\GeneralSystemRole;
use Illuminate\Http\Request;

class GeneralSystemRoleController extends Controller
{

    public function index()
    {
        try {
            $systemrole = GeneralSystemRole::all();
            return response()->json([
                'message' => 'All System Roles',
                'systemrole' => $systemrole
            ], status: 201);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Something went wrong. Please try again later.'
            ], 500);
        }
    }

    public function store(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'system_role_name' => 'required|string|max:255',
            ]);
            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()], 422);
            }
            // Create the new system role in the database
            $systemrole = GeneralSystemRole::create([
                'system_role_name' => $request->system_role_name,
            ]);

            return response()->json([
                'message' => 'System role added successfully',
                'systemrole' => $systemrole
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

    public function show(Request $request, $system_role_id)
    {
        try {
            $request->merge(['general_system_role_id' => $system_role_id]);
            $validator = Validator::make($request->all(), [
                'general_system_role_id' => 'required|integer|exists:general_system_roles,general_system_role_id',
            ]);
            // Check if validation fails
            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()], 422);
            }
            $systemrole = GeneralSystemRole::findOrFail($system_role_id);
            return response()->json([
                'message' => 'System Role Addeed SucessFully',
                'systemrole' => $systemrole
            ], 200);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {

            return response()->json([
                'error' => 'System Role not found with the provided ID.'
            ], 404);
        } catch (\Exception $e) {
            // Catch any other exceptions
            return response()->json([
                'error' => 'Something went wrong. Please try again later.'
            ], 500);
        }

    }

    public function update(Request $request, $system_role_id)
    {
        try {
            $request->merge(['general_system_role_id' => $system_role_id]);
            $validator = Validator::make($request->all(), [
                'general_system_role_id' => 'required|integer|exists:general_system_roles,general_system_role_id',
                'system_role_name' => 'sometimes|string|max:255',
            ]);
            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()], 422);
            }
            // Find the organization by ID
            $systemrole = GeneralSystemRole::findOrFail($system_role_id);
            // Update the organization with the validated data
            $systemrole->update($request->all());

            return response()->json([
                'message' => 'System Role Updated SucessFully',
                'systemrole' => $systemrole
            ], 200);

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            // If the organization is not found, return an error message
            return response()->json([
                'error' => 'systemrole not found with the provided ID.'
            ], 404);
        } catch (\Exception $e) {
            // Catch any other exceptions
            return response()->json([
                'error' => 'Something went wrong. Please try again later.'
            ], 500);
        }
    }
  
    public function destroy(Request $request, $system_role_id)
    {
        try {
            $request->merge(['general_system_role_id' => $system_role_id]);
            $validator = Validator::make($request->all(), [
                'general_system_role_id' => 'required|integer|exists:general_system_roles,general_system_role_id',
            ]);
            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()], 422);
            }
            $systemrole = GeneralSystemRole::findOrFail($system_role_id);
            $systemrole->delete();
            return response()->json([
                'message' => 'System role deleted successfully'
            ], 200);

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'error' => 'System role not found with the provided ID.'
            ], 404);

        } catch (\Illuminate\Database\QueryException $e) {
            if ($e->getCode() == 23000) { // Foreign key constraint violation
                return response()->json([
                    'error' => 'Cannot delete this system role because it is linked with other records (e.g., users).'
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
