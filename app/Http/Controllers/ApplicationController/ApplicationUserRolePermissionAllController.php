<?php

namespace App\Http\Controllers\ApplicationController;

use App\Models\ApplicationModels\ApplicationErrorLogs;
use App\Models\ApplicationModels\ApplicationUserRolePermisiion;
use App\Models\ApplicationModels\ApplicationUserRoles;
use DB;
use Illuminate\Support\Facades\Validator;
use App\Http\Controllers\Controller;
use Exception;
use Illuminate\Http\Request;
use Auth;
use Illuminate\Validation\Rule;

class ApplicationUserRolePermissionAllController extends Controller
{
    public function storeOrUpdatePermissions(Request $request)
    {
        $request->validate([
            'application_user_role_id' => 'required|integer|exists:application_user_roles,application_user_role_id',
            'permissions' => 'required|array',
            'permissions.*.application_module_action_id' => 'required|integer|exists:application_module_actions,application_module_action_id',
            'permissions.*.permission_allowed' => 'required|boolean',
        ]);

        $roleId = $request->application_user_role_id;
        $submittedPermissions = collect($request->permissions);

        DB::beginTransaction();

        try {
            // Fetch existing permissions for this role
            $existingPermissions = ApplicationUserRolePermisiion::where('application_user_role_id', $roleId)->get();

            // Index them by module action ID for fast lookup
            $existingByActionId = $existingPermissions->keyBy('application_module_action_id');

            // Track action IDs from the request
            $submittedActionIds = $submittedPermissions->pluck('application_module_action_id')->all();

            // 1. Create or Update submitted permissions
            foreach ($submittedPermissions as $permission) {
                $actionId = $permission['application_module_action_id'];
                $allowed = $permission['permission_allowed'];

                if ($existingByActionId->has($actionId)) {
                    // Update existing
                    $perm = $existingByActionId[$actionId];
                    $perm->permission_allowed = $allowed;
                    $perm->save();
                } else {
                    // Create new
                    ApplicationUserRolePermisiion::create([
                        'application_user_role_id' => $roleId,
                        'application_module_action_id' => $actionId,
                        'permission_allowed' => $allowed,
                    ]);
                }
            }

            // 2. Delete permissions that were not included in the current request
            $existingPermissions->each(function ($existing) use ($submittedActionIds) {
                if (!in_array($existing->application_module_action_id, $submittedActionIds)) {
                    $existing->delete();
                }
            });

            DB::commit();

            return response()->json(['message' => 'Permissions updated successfully.'], 200);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['error' => 'Something went wrong.', 'details' => $e->getMessage()], 500);
        }
    }


    public function getAllPermissionsForAllRoles()
{
    $permissions = ApplicationUserRolePermisiion::with([
        'applicationUserRole', // assuming relationship is defined
        'applicationModuleAction' // assuming relationship is defined
    ])->get();

    return response()->json(['permissions' => $permissions], 200);
}



public function getPermissionsByPermissionId($permissionId)
{
    // 1. Find the specific permission entry
    $permission = ApplicationUserRolePermisiion::with(['applicationUserRole', 'applicationModuleAction'])
        ->where('application_user_role_id', $permissionId)
        ->first();

    if (!$permission) {
        return response()->json(['error' => 'Permission not found.'], 404);
    }

    $roleId = $permission->application_user_role_id;

    // 2. Fetch all permissions associated with the same role
    $allPermissions = ApplicationUserRolePermisiion::with('applicationModuleAction')
        ->where('application_user_role_id', $roleId)
        ->get();

    return response()->json([
        'application_user_role_id' => $roleId,
        'user_role_name' => $permission->applicationUserRole->user_role_name ?? null,
        'description' => $permission->applicationUserRole->description ?? null,
        'permissions' => $allPermissions->map(function ($perm) {
            return [
                'application_module_action_id' => $perm->application_module_action_id,
                'permission_allowed' => $perm->permission_allowed,
                'module_action' => $perm->applicationModuleAction->action_name ?? null,
                'module_id' => $perm->applicationModuleAction->application_module_id ?? null,
            ];
        }),
    ]);
}



public function deletePermissionsByRoleId($roleId)
{
    try {
        // Validate that the role exists
        $exists = ApplicationUserRolePermisiion::where('application_user_role_id', $roleId)->exists();

        if (!$exists) {
            return response()->json(['error' => 'No permissions found for this role.'], 404);
        }

        // Delete all permissions for the role
        ApplicationUserRolePermisiion::where('application_user_role_id', $roleId)->delete();

        return response()->json([
            'message' => 'All permissions for the specified role have been deleted successfully.'
        ], 200);
    } catch (\Exception $e) {
        return response()->json([
            'error' => 'Something went wrong while deleting permissions.',
            'details' => $e->getMessage()
        ], 500);
    }
}





}


