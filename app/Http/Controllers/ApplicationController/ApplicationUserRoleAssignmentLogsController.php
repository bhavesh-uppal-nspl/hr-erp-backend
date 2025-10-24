<?php

namespace App\Http\Controllers\ApplicationController;
use App\Models\ApplicationModels\ApplicationUserRoleAssignmentLog;
use Illuminate\Support\Facades\Validator;
use App\Http\Controllers\Controller;
use Exception;
use Illuminate\Http\Request;
use Auth;
use Illuminate\Validation\Rule;

class ApplicationUserRoleAssignmentLogsController extends Controller
{

    public function index()
    {
        try {
            $roleAssignment = ApplicationUserRoleAssignmentLog::all();
            $roleAssignment->load('User');
            return response()->json([
                'message' => 'Application User Roles Assignment Logs',
                'roleAssignmentlogs' => $roleAssignment
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Something went wrong. Please try again later.',
                'details' => $e->getMessage()
            ], 500);
        }

    }

    public function store(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'application_user_id' => 'required|integer|exists:application_users,application_user_id',
                'previous_role_name' => 'nullable|string|max:100',
                'new_role_id' => 'required|integer|integer|unique:application_user_role_assignment_logs,new_role_id',
                'previous_role_id' => 'required|integer|unique:application_user_role_assignment_logs,previous_role_id',
                'new_role_name' => 'required|string|max:100',
                'changed_by_user_id' => 'nullable|integer|unique:application_user_role_assignment_logs,changed_by_user_id',
                'change_reason' => 'nullable|string|max:255',
                'changed_at' => 'required|date|before_or_equal:now',
            ]);
            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()], 422);
            }
            $data = $request->all();
            $roleAssignment = ApplicationUserRoleAssignmentLog::create($data);
            return response()->json([
                'message' => 'Application User Role log Added SuccessFully.',
                'roleAssignment' => $roleAssignment
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Something went wrong. Please try again later.',
                'details' => $e->getMessage()
            ], 500);
        }
    }

    public function show(Request $request, $role_assignment_id)
    {
        try {
            $request->merge(['application_user_role_assignment_log_id' => $role_assignment_id]);
            $validator = Validator::make($request->all(), [
                'application_user_role_assignment_log_id' => 'required|integer|exists:application_user_role_assignment_logs,application_user_role_assignment_log_id',
            ]);
            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()], 422);
            }
            $roleAssignment = ApplicationUserRoleAssignmentLog::find($role_assignment_id);
            return response()->json([
                'message' => "Application User Role Assignment Logs Found",
                'roleAssignment' => $roleAssignment
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Something went wrong. Please try again later.',
                'details' => $e->getMessage()
            ], 500);
        }
    }

    public function update(Request $request, $role_assignment_id)
    {
        try {
            $request->merge([
                'application_user_role_assignment_log_id' => $role_assignment_id
            ]);
            $rules = [
                'application_user_role_assignment_log_id' => 'required|integer|exists:application_user_role_assignment_logs,application_user_role_assignment_log_id',
                'application_user_id' => 'sometimes|integer|exists:application_users,application_user_id',
                'previous_role_name' => 'sometimes|nullable|string|max:100',
                'new_role_id' => 'sometimes|required|integer',
                'previous_role_id' => 'sometimes|integer',
                'new_role_name' => 'sometimes|string|max:100',
                'changed_by_user_id' => 'sometimes|nullable|integer',
                'change_reason' => 'sometimes|nullable|string|max:255',
                'changed_at' => 'sometimes|date|before_or_equal:now',
            ];
            $validator = Validator::make($request->all(), $rules);
            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()], 422);
            }
            $roleAssignment = ApplicationUserRoleAssignmentLog::find($role_assignment_id);
            $roleAssignment->update($request->only([
                'application_user_id',
                'previous_role_name',
                'new_role_id',
                'previous_role_id',
                'new_role_name',
                'changed_by_user_id',
                'change_reason',
                'changed_at',
            ]));

            return response()->json([
                'message' => 'Application User Role Assignment Log updated successfully.',
                'roleAssignment' => $roleAssignment
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Something went wrong. Please try again later.',
                'details' => $e->getMessage()
            ], 500);
        }
    }

    public function destroy(Request $request, $role_assignment_id)
    {
        try {
            $request->merge([
                'application_user_role_assignment_log_id' => $role_assignment_id
            ]);
            $validator = Validator::make($request->all(), [
                'application_user_role_assignment_log_id' => 'required|integer|exists:application_user_role_assignment_logs,application_user_role_assignment_log_id',

            ]);
            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()], 422);
            }
            $roleAssignment = ApplicationUserRoleAssignmentLog::find($role_assignment_id);
            $roleAssignment->delete();
            return response()->json([
                'message' => 'Application User Role Assignment Deleted Successfully'
            ], 200);
        } catch (Exception $e) {
            if ($e instanceof \Illuminate\Database\QueryException && $e->errorInfo[1] == 1451) {
                // Foreign key constraint violation
                return response()->json([
                    'error' => 'Cannot delete User Role Permission  because it is linked with other records. Please delete dependent records first.'
                ], 409); // 409 Conflict
            }
            return response()->json([
                'error' => 'Failed to delete Application Role.',
                'exception' => $e->getMessage() // Optional: remove in production
            ], 500);
        }
    }
}
