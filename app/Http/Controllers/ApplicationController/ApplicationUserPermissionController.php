<?php

namespace App\Http\Controllers\ApplicationController;
use App\Models\ApplicationModels\ApplicationModules;
use App\Models\ApplicationModels\ApplicationUserPermission;
use Illuminate\Support\Facades\Validator;
use App\Http\Controllers\Controller;
use Exception;
use Illuminate\Http\Request;
use Auth;
use Illuminate\Validation\Rule;

class ApplicationUserPermissionController extends Controller
{

    public function index(Request $request)
    {
        try {
            $permission = ApplicationUserPermission::all();
            $permission->load('ModuleAction', 'User');
            return response()->json([
                'message' => 'Application Modules Actions',
                'moduleaction' => $permission
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
                'permission_allowed' => 'sometimes|nullable|boolean',
                'application_user_id ' => 'required|integer|exists:application_users,application_user_id',
            ]);

            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()], 422);
            }
            $data = $request->all();
            $permission = ApplicationModules::create($data);
            return response()->json([
                'message' => 'Application User Permission Added SuccessFully.',
                'userpermission' => $permission
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Something went wrong. Please try again later.',
                'details' => $e->getMessage()
            ], 500);
        }
    }

    public function show(Request $request, $permission_id)
    {
        try {
            $request->merge(['application_user_permission_id' => $permission_id]);
            $validator = Validator::make($request->all(), [
                'application_user_permission_id' => 'required|integer|exists:application_user_permission,application_user_permission_id',
            ]);
            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()], 422);
            }
            $permission = ApplicationUserPermission::find($permission_id);
            return response()->json([
                'message' => "Application User Permission Found",
                'userpermission' => $permission
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Something went wrong. Please try again later.',
                'details' => $e->getMessage()
            ], 500);
        }
    }

    public function update(Request $request, $permission_id)
    {
        try {
            $request->merge(['application_user_permission_id' => $permission_id]);
            $rules = [
                'application_user_permission_id' => 'required|integer|exists:application_user_permission,application_user_permission_id',
                'application_module_action_id' => 'sometimes|integer|exists:application_module_actions,application_module_action_id',
                'permission_allowed' => 'sometimes|nullable|boolean',
                'application_user_id' => 'sometimes|integer|exists:application_users,application_user_id',

            ];
            $validator = Validator::make($request->all(), $rules);
            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()], 422);
            }
            $module = ApplicationUserPermission::find($permission_id);
            $module->update($request->only([
                'application_module_action_id',
                'permission_allowed',
                'application_user_id',
                 'is_active'
             

            ]));

            return response()->json([
                'message' => 'Application User Permission updated successfully.',
                'moduleaction' => $module
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Something went wrong. Please try again later.',
                'details' => $e->getMessage()
            ], 500);
        }
    }

    public function destroy(Request $request, $permission_id)
    {
        try {
            $request->merge([
                'application_user_permission_id' => $permission_id
            ]);
            $validator = Validator::make($request->all(), [
                'application_user_permission_id' => 'required|integer|exists:application_user_permission,application_user_permission_id',
            ]);
            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()], 422);
            }
            $permission = ApplicationUserPermission::find($permission_id);
            $permission->delete();
            return response()->json([
                'message' => 'Application User Permission Deleted Successfully'
            ], 200);
        } catch (Exception $e) {
            if ($e instanceof \Illuminate\Database\QueryException && $e->errorInfo[1] == 1451) {
                // Foreign key constraint violation
                return response()->json([
                    'error' => 'Cannot delete Application modules  because it is linked with other records. Please delete dependent records first.'
                ], 409); // 409 Conflict
            }
            return response()->json([
                'error' => 'Failed to delete Application Role.',
                'exception' => $e->getMessage() // Optional: remove in production
            ], 500);
        }
    }
}
