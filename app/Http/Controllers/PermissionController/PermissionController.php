<?php
namespace App\Http\Controllers\PermissionController;
use DB;
use App\Http\Controllers\Controller;


class PermissionController extends Controller
{
    public function checkUserAccess($userId)
{
    // Step 1: Get all actions (id => code)
    $allActions = DB::table('application_module_actions')
        ->pluck('module_action_code', 'application_module_action_id');

    // Step 2: Get active role IDs for the user
    $roleIds = DB::table('organization_user_role_assignments')
        ->where('organization_user_id', $userId)
        ->where('is_active', 1)
        ->pluck('application_user_role_id');

    if ($roleIds->isEmpty()) {
        return response()->json([
            'user_id' => $userId,
            'allowed_actions' => [],
            'message' => 'User has no active roles'
        ]);
    }

    // Step 3: Get all allowed permission action IDs for those roles
    $allowedActionIds = DB::table('application_user_role_permissions')
        ->whereIn('application_user_role_id', $roleIds)
        ->where('permission_allowed', 1)
        ->pluck('application_module_action_id')
        ->unique();

    // Step 4: Map IDs to action codes
    $allowedActionCodes = $allowedActionIds
        ->map(function ($actionId) use ($allActions) {
            return $allActions[$actionId] ?? null;
        })
        ->filter() // remove nulls
        ->values(); // reset keys

    return response()->json([
        'user_id' => $userId,
        'allowed_actions' => $allowedActionCodes
    ]);
}

}


   