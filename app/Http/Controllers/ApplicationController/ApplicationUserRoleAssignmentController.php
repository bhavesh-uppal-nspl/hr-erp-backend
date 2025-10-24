<?php

namespace App\Http\Controllers\ApplicationController;

use App\Models\ApplicationModels\ApplicationUserRoleAssignment;
use App\Models\ApplicationModels\ApplicationUserRoles;
use App\Models\OrganizationModel\OrganizationUser;
use App\Models\OrganizationModel\OrganizationUserRoleAssignment;
use Illuminate\Support\Facades\Validator;
use App\Http\Controllers\Controller;
use Exception;
use Illuminate\Http\Request;
use Auth;
use Illuminate\Validation\Rule;

class ApplicationUserRoleAssignmentController extends Controller
{

    public function index(Request $request)
    {
        try {
            $roleAssignment = ApplicationUserRoleAssignment::all();
            $roleAssignment->load('ApplicationUser', 'ApplicationUserRoles');
            return response()->json([
                'message' => 'Application User Roles',
                'roleAssignment' => $roleAssignment
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Something went wrong. Please try again later.',
                'details' => $e->getMessage()
            ], 500);
        }

    }

    // public function store(Request $request)
    // {
    //     try {
    //         $validator = Validator::make($request->all(), [
    //               'application_user_role_id' => 'required|integer|exists:application_user_roles,application_user_role_id',
    //               'application_user_id' => 'required|integer|exists:application_users,application_user_id',
    //               'is_active' => 'nullable|boolean',

    //         ]);
    //         if ($validator->fails()) {
    //             return response()->json(['errors' => $validator->errors()], 422);
    //         }


    //         if($request->organization_id)
    //         {
    //             // add data in organization user role assignment 
    //             $data=$request->all();
    //             $organization_user_id= OrganizationUser::find($request->organization_id);
    //             $roleAssignment=OrganizationUserRoleAssignment::create([
    //                 'application_user_role_id'=>$request->application_user_role_id,
    //                 'organization_user_id'->organization_user_id,
    //                 'organization_id'=>$request->organization_id,
    //                 'assigned_at'=>add purrsent date 
    //             ])




    //         }
    //         else{
    //               $data = $request->all();
    //              $roleAssignment = ApplicationUserRoleAssignment::create($data);

    //         }
          
    //         return response()->json([
    //             'message' => 'Application User Role Assignment Added SuccessFully.',
    //             'roleAssignment' => $roleAssignment
    //         ], 201);
    //     } catch (\Exception $e) {
    //         return response()->json([
    //             'error' => 'Something went wrong. Please try again later.',
    //             'details' => $e->getMessage()
    //         ], 500);
    //     }
    // }

   
   
    
    public function store(Request $request)
{
    try {
        $validator = Validator::make($request->all(), [
            'application_user_role_id' => 'required|integer|exists:application_user_roles,application_user_role_id',
            'application_user_id' => 'required|integer|exists:application_users,application_user_id',
            'organization_id' => 'nullable|integer|exists:organization_users,organization_id', // assuming org ID maps to org users
           
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        if ($request->filled('organization_id')) {
            // Find the organization user by organization_id and application_user_id
            $organizationUser = OrganizationUser::where('organization_id', $request->organization_id)
                ->where('application_user_id', $request->application_user_id)
                ->first();

            if (!$organizationUser) {
                return response()->json([
                    'error' => 'Organization user not found for the given application_user_id and organization_id.'
                ], 404);
            }

            // Create record in OrganizationUserRoleAssignment
            $roleAssignment = OrganizationUserRoleAssignment::create([
                'application_user_role_id' => $request->application_user_role_id,
                'organization_user_id' => $organizationUser->organization_user_id,
                'organization_id' => $request->organization_id,
                'assigned_at' => now(),
               
            ]);
        } else {
            // Create record in ApplicationUserRoleAssignment
            $roleAssignment = ApplicationUserRoleAssignment::create([
                'application_user_role_id' => $request->application_user_role_id,
                'application_user_id' => $request->application_user_id,
                'is_active' => $request->is_active ?? true,
            ]);
        }

        return response()->json([
            'message' => 'Application User Role Assignment Added Successfully.',
            'roleAssignment' => $roleAssignment
        ], 201);

    } catch (\Exception $e) {
        return response()->json([
            'error' => 'Something went wrong. Please try again later.',
            'details' => $e->getMessage()
        ], 500);
    }
}

   
   
    public function show(Request $request, $user_role_assignment_id)
    {
        try {
            $request->merge(['application_user_role_assignment_id' => $user_role_assignment_id]);
            $validator = Validator::make($request->all(), [
                'application_user_role_assignment_id' => 'required|integer|exists:application_user_role_assignments,application_user_role_assignment_id',
            ]);
            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()], 422);
            }
            $roleAssignment = ApplicationUserRoleAssignment::find($user_role_assignment_id);
            $roleAssignment->load('ApplicationUser', 'ApplicationUserRoles');
            return response()->json([
                'message' => "Application User Role Assignment Found",
                'roleAssignment' => $roleAssignment
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Something went wrong. Please try again later.',
                'details' => $e->getMessage()
            ], 500);
        }
    }

    public function update(Request $request, $user_role_assignment_id)
    {
        try {
            $request->merge([
                'application_user_role_assignment_id' => $user_role_assignment_id
            ]);
            $rules = [
                'application_user_role_assignment_id' => 'required|integer|exists:application_user_role_assignments,application_user_role_assignment_id',
                'application_user_role_id' => 'sometimes|integer|exists:application_user_roles,application_user_role_id',
                'application_user_id' => 'sometimes|integer|exists:application_users,application_user_id',
                'is_active' => 'sometimes|boolean',

            ];
            $validator = Validator::make($request->all(), $rules);
            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()], 422);
            }
            $roleAssignment = ApplicationUserRoleAssignment::find($user_role_assignment_id);
            $roleAssignment->update($request->only([
                'application_user_role_id',
                'application_user_id',
                'is_active'
            ]));

            return response()->json([
                'message' => 'Application User Role Assignment updated successfully.',
                'roleAssignment' => $roleAssignment
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Something went wrong. Please try again later.',
                'details' => $e->getMessage()
            ], 500);
        }
    }

    public function destroy(Request $request, $user_role_assignment_id)
    {
        try {
            $request->merge([
                'application_user_role_assignment_id' => $user_role_assignment_id
            ]);
            $validator = Validator::make($request->all(), [
                'application_user_role_assignment_id' => 'required|integer|exists:application_user_role_assignments,application_user_role_assignment_id',

            ]);
            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()], 422);
            }
            $roleAssignment = ApplicationUserRoleAssignment::find($user_role_assignment_id);
            $roleAssignment->delete();
            return response()->json([
                'message' => 'Application User Role Assignment Deleted Successfully'
            ], 200);
        } catch (Exception $e) {
            if ($e instanceof \Illuminate\Database\QueryException && $e->errorInfo[1] == 1451) {
                // Foreign key constraint violation
                return response()->json([
                    'error' => 'Cannot delete Application Role Assignment because it is linked with other records. Please delete dependent records first.'
                ], 409); // 409 Conflict
            }
            return response()->json([
                'error' => 'Failed to delete Application Role.',
                'exception' => $e->getMessage() // Optional: remove in production
            ], 500);
        }
    }
}
