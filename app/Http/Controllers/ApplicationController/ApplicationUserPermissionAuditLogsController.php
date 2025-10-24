<?php

namespace App\Http\Controllers\ApplicationController;
use App\Models\ApplicationModels\ApplicationUserPermissionAuditLogs;
use Illuminate\Support\Facades\Validator;
use App\Http\Controllers\Controller;
use Exception;
use Illuminate\Http\Request;
class ApplicationUserPermissionAuditLogsController extends Controller
{

    public function index(Request $request)
    {
        try {
            $permissionauditlogs = ApplicationUserPermissionAuditLogs::all();
            $permissionauditlogs->load('ModuleAction', 'User', 'Module', 'UserPermission');
            return response()->json([
                'message' => 'Application User Permission Audit Logs',
                'permissionauditlogs' => $permissionauditlogs
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
                'application_module_action_id' => 'required|integer|exists:application_module_actions,application_module_action_id',
                'application_user_id ' => 'required|integer|exists:application_users,application_user_id',
                'application_user_permission_id ' => 'required|integer|exists:application_user_permissions,application_user_permission_id',
                'application_module_id ' => 'required|integer|exists:application_modules,application_module_id',
                'module_name_snapshot' => 'required|string|max:100',
                'module_action_name_snapshot' => 'required|string|max:100',
                'user_name_snapshot' => 'required|string|max:100',
                'previous_permission_status' => 'nullable|in:allowed,denied',
                'new_permission_status' => 'required|in:allowed,denied',
                'change_source' => 'required|in:role,manual',
                'modified_by_user_id' => 'nullable|integer|unique:application_user_permission_audit_logs,modified_by_user_id',
                'changed_at' => 'required|date|before_or_equal:now',
            ]);

            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()], 422);
            }
            $data = $request->all();
            $permissionauditlogs = ApplicationUserPermissionAuditLogs::create($data);
            return response()->json([
                'message' => 'Application User Permission Audit Logs Added SuccessFully.',
                'permissionauditlogs' => $permissionauditlogs
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Something went wrong. Please try again later.',
                'details' => $e->getMessage()
            ], 500);
        }
    }

    public function show(Request $request, $permission_audit_log_id)
    {
        try {
            $request->merge(['application_user_permission_id' => $permission_audit_log_id]);
            $validator = Validator::make($request->all(), [
                'application_user_permission_audit_log_id' => 'required|integer|exists:application_user_permission,application_user_permission_id',
            ]);
            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()], 422);
            }
            $permissionauditlogs = ApplicationUserPermissionAuditLogs::find($permission_audit_log_id);
            return response()->json([
                'message' => "Application User Permission Audit Log Found",
                'permissionauditlogs' => $permissionauditlogs
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Something went wrong. Please try again later.',
                'details' => $e->getMessage()
            ], 500);
        }
    }

    public function update(Request $request, $permission_audit_log_id)
    {
        try {
            $request->merge(['application_user_permission_audit_log_id' => $permission_audit_log_id]);
            $rules = [
                'application_user_permission_audit_log_id' => 'required|integer|exists:application_user_permission,application_user_permission_id',
                'application_user_permission_id' => 'required|integer|exists:application_user_permission,application_user_permission_id',
                'application_module_action_id' => 'sometimes|integer|exists:application_module_actions,application_module_action_id',
                'application_module_id ' => 'required|integer|exists:application_modules,application_module_id',
                'module_name_snapshot' => 'required|string|max:100',
                'module_action_name_snapshot' => 'required|string|max:100',
                'user_name_snapshot' => 'required|string|max:100',
                'previous_permission_status' => 'nullable|in:allowed,denied',
                'new_permission_status' => 'required|in:allowed,denied',
                'change_source' => 'required|in:role,manual',
                'modified_by_user_id' => 'nullable|integer|unique:application_user_permission_audit_logs,modified_by_user_id',
                'changed_at' => 'required|date|before_or_equal:now',
                'application_user_id ' => 'sometimes|integer|exists:application_users,application_user_id',
            ];
            $validator = Validator::make($request->all(), $rules);
            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()], 422);
            }
            $permissionlogs = ApplicationUserPermissionAuditLogs::find($permission_audit_log_id);
            $permissionlogs->update($request->only([

                'application_user_permission_id',
                'application_module_action_id',
                'application_module_id',
                'module_name_snapshot',
                'module_action_name_snapshot',
                'user_name_snapshot',
                'previous_permission_status',
                'new_permission_status',
                'change_source',
                'modified_by_user_id',
                'changed_at',
                'application_user_id'

            ]));

            return response()->json([
                'message' => 'Application User Permission Audit updated successfully.',
                'permissionauditlogs' => $permissionlogs
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Something went wrong. Please try again later.',
                'details' => $e->getMessage()
            ], 500);
        }
    }

    public function destroy(Request $request, $permission_audit_log_id)
    {
        try {
            $request->merge([
                'application_user_permission_audit_log_id' => $permission_audit_log_id
            ]);
            $validator = Validator::make($request->all(), [
                'application_user_permission_audit_log_id' => 'required|integer|exists:application_user_permission,application_user_permission_id',
            ]);
            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()], 422);
            }
            $permissionauditlogs = ApplicationUserPermissionAuditLogs::find($permission_audit_log_id);
            $permissionauditlogs->delete();
            return response()->json([
                'message' => 'Application User Permission Audit Log Deleted Successfully'
            ], 200);
        } catch (Exception $e) {
            if ($e instanceof \Illuminate\Database\QueryException && $e->errorInfo[1] == 1451) {
                // Foreign key constraint violation
                return response()->json([
                    'error' => 'Cannot delete Application Audit Log  because it is linked with other records. Please delete dependent records first.'
                ], 409); // 409 Conflict
            }
            return response()->json([
                'error' => 'Failed to delete Application Role.',
                'exception' => $e->getMessage() // Optional: remove in production
            ], 500);
        }
    }
}
