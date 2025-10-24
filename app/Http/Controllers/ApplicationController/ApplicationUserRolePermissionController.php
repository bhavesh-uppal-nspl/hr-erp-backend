<?php

namespace App\Http\Controllers\ApplicationController;
use App\Models\ApplicationModels\ApplicationUserRolePermisiion;
use Illuminate\Support\Facades\Validator;
use App\Http\Controllers\Controller;
use Exception;
use Illuminate\Http\Request;
use Auth;

class ApplicationUserRolePermissionController extends Controller
{

    public function index()
    {
        try {
            $userrolespermission = ApplicationUserRolePermisiion::all();
            $userrolespermission->load('UserRole', 'ModuleAction');
            return response()->json([
                'message' => 'Application User Roles Permission',
                'userrolespermission' => $userrolespermission
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
                'application_user_role_id' => 'required|integer|exists:application_user_roles,application_user_role_id',
                'application_module_action_id' => 'required|integer|exists:application_module_actions,application_module_action_id',
                'permission_allowed' => 'required|boolean',
            ]);
            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()], 422);
            }
            $data = $request->all();
            $roleperission = ApplicationUserRolePermisiion::create($data);
            return response()->json([
                'message' => 'Application User Role Permission Added SuccessFully.',
                'roleperission' => $roleperission
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Something went wrong. Please try again later.',
                'details' => $e->getMessage()
            ], 500);
        }
    }

    public function show(Request $request, $user_role_permission_id)
    {
        try {
            $request->merge(['application_user_role_permission_id' => $user_role_permission_id]);
            $validator = Validator::make($request->all(), [
                'application_user_role_permission_id' => 'required|integer|exists:application_user_role_permissions,application_user_role_permission_id',
            ]);
            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()], 422);
            }
            $rolepermission = ApplicationUserRolePermisiion::find($user_role_permission_id);
            return response()->json([
                'message' => "Application User Role Permission Found",
                'rolepermission' => $rolepermission
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Something went wrong. Please try again later.',
                'details' => $e->getMessage()
            ], 500);
        }
    }

    public function update(Request $request, $user_role_permission_id)
    {
        try {
            $request->merge([
                'application_user_role_permission_id' => $user_role_permission_id
            ]);
            $rules = [
                'application_user_role_permission_id' => 'required|integer|exists:application_user_role_permissions,application_user_role_permission_id',
                'application_user_role_id' => 'required|integer|exists:application_user_roles,application_user_role_id',
                'application_module_action_id' => 'required|integer|exists:application_module_actions,application_module_action_id',
                'permission_allowed' => 'required|boolean',
            ];
            $validator = Validator::make($request->all(), $rules);
            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()], 422);
            }
            $userroles = ApplicationUserRolePermisiion::find($user_role_permission_id);
            $userroles->update($request->only([
                'application_user_role_id',
                'application_module_action_id',
                'permission_allowed'
            ]));

            return response()->json([
                'message' => 'Application User Role Permission updated successfully.',
                'userroles' => $userroles
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Something went wrong. Please try again later.',
                'details' => $e->getMessage()
            ], 500);
        }
    }

    public function destroy(Request $request, $user_role_permission_id)
    {
        try {
            $request->merge([
                'application_user_role_permission_id' => $user_role_permission_id
            ]);
            $validator = Validator::make($request->all(), [
                'application_user_role_permission_id' => 'required|integer|exists:application_user_role_permissions,application_user_role_permission_id',

            ]);
            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()], 422);
            }
            $rolepermission = ApplicationUserRolePermisiion::find($user_role_permission_id);
            $rolepermission->delete();
            return response()->json([
                'message' => 'Application User Role Permission Deleted Successfully'
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


    public function getPermissionsByRole(Request $request,$roleId)
{
    try {
        $permissions = ApplicationUserRolePermisiion::where('application_user_role_id', $roleId)
            ->with(['UserRole', 'ModuleAction']) // eager load relations
            ->get();

        return response()->json([
            'message' => 'Permissions fetched successfully for the given role.',
            'permissions' => $permissions
        ], 200);
    } catch (\Exception $e) {
        return response()->json([
            'error' => 'Something went wrong while fetching permissions.',
            'details' => $e->getMessage()
        ], 500);
    }
}



}
