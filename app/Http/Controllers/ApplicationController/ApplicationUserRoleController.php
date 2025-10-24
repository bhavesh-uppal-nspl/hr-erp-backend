<?php

namespace App\Http\Controllers\ApplicationController;

use App\Models\ApplicationModels\ApplicationUserRoles;
use Illuminate\Support\Facades\Validator;
use App\Http\Controllers\Controller;
use Exception;
use Illuminate\Http\Request;
use Auth;
use Illuminate\Validation\Rule;

class ApplicationUserRoleController extends Controller
{

    public function index(Request $request)
    {
        try {
            if ($request->input('mode') == 1) {
                $role =  ApplicationUserRoles::all();

                if ($role->isEmpty()) {
                    return response()->json([
                        'message' => 'workshift not found.'
                    ], 404);
                }
                $mappedRole = $role->map(function ($dep) {
                    return [
                        'user_role_name'=>$dep->user_role_name ,
                        'description' => $dep->description ?? '',
                    ];
                });
                return response()->json($mappedRole);
            }

            $userroles = ApplicationUserRoles::all();
            return response()->json([
                'message' => 'Application User Roles',
                'userroles' => $userroles
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
                'user_role_name' => 'required|string|max:30|unique:application_user_roles,user_role_name',
                'description' => 'nullable|string|max:255',
            ]);
            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()], 422);
            }
            $data = $request->all();
            $userroles = ApplicationUserRoles::create($data);
            return response()->json([
                'message' => 'Application User Role Added SuccessFully.',
                'userroles' => $userroles
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Something went wrong. Please try again later.',
                'details' => $e->getMessage()
            ], 500);
        }
    }

    public function show(Request $request, $user_role_id)
    {
        try {
            $request->merge(['application_user_role_id' => $user_role_id]);
            $validator = Validator::make($request->all(), [
                'application_user_role_id' => 'required|integer|exists:application_user_roles,application_user_role_id',
            ]);
            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()], 422);
            }
            $userroles = ApplicationUserRoles::find($user_role_id);
            return response()->json([
                'message' => "Application User Role Found",
                'userroles' => $userroles
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Something went wrong. Please try again later.',
                'details' => $e->getMessage()
            ], 500);
        }
    }

    public function update(Request $request, $user_role_id)
    {
        try {
            $request->merge([
                'application_user_role_id' => $user_role_id
            ]);
            $rules = [
                'application_user_role_id' => 'required|integer|exists:application_user_roles,application_user_role_id',
                'user_role_name' => [
                    'sometimes',
                    'string',
                    'max:30',
                    Rule::unique('application_user_roles', 'user_role_name')->ignore($user_role_id, 'application_user_role_id')
                ],
                  'description ' => 'nullable|string|max:255',
            ];
            $validator = Validator::make($request->all(), $rules);
            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()], 422);
            }
            $userroles = ApplicationUserRoles::find($user_role_id);
            $userroles->update($request->only([
                'description',
                'user_role_name'
            ]));

            return response()->json([
                'message' => 'Application User Role updated successfully.',
                'userroles' => $userroles
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Something went wrong. Please try again later.',
                'details' => $e->getMessage()
            ], 500);
        }
    }

    public function destroy(Request $request, $user_role_id)
    {
        try {
            $request->merge([
                'application_user_role_id' => $user_role_id
            ]);
            $validator = Validator::make($request->all(), [
                'application_user_role_id' => 'required|integer|exists:application_user_roles,application_user_role_id',
              
            ]);
            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()], 422);
            }
            $userroles = ApplicationUserRoles::find($user_role_id);
            $userroles->delete();
            return response()->json([
                'message' => 'Application User Role Deleted Successfully'
            ], 200); 
        } catch (Exception $e) {
            if ($e instanceof \Illuminate\Database\QueryException && $e->errorInfo[1] == 1451) {
                // Foreign key constraint violation
                return response()->json([
                    'error' => 'Cannot delete Application Role  because it is linked with other records. Please delete dependent records first.'
                ], 409); // 409 Conflict
            }
            return response()->json([
                'error' => 'Failed to delete Application Role.',
                'exception' => $e->getMessage() // Optional: remove in production
            ], 500);
        }
    }
}
