<?php

namespace App\Http\Controllers\OrganizationController;
use App\Http\Controllers\Controller;
use App\Models\OrganizationModel\OrganizationRoles;
use Illuminate\Http\Request;    use Auth;
use Illuminate\Validation\ValidationException;
use Exception;

class OrganizationRoleController extends Controller
{

    public function index($org_id)
  {
        try {    $user = Auth::guard('applicationusers')->user();
             $organizationIds = $user->Client->Organization->pluck('organization_id')->toArray();
            if (!in_array($org_id, $organizationIds)) {
                return response()->json([
                    'messages' => 'unauthorized'
                ], 401);

            }
            $roles = OrganizationRoles::all();
            return response()->json([
                'roles' => $roles,
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Something went wrong. Please try again later.',
                'details' => $e->getMessage()
            ], 500);
        }
    }

    public function store(Request $request,$org_id)
  {
        try {    $user = Auth::guard('applicationusers')->user();
             $organizationIds = $user->Client->Organization->pluck('organization_id')->toArray();
            if (!in_array($org_id, $organizationIds)) {
                return response()->json([
                    'messages' => 'unauthorized'
                ], 401);

            }
            $validated = $request->validate([
                'role_name' => 'required|string|max:50',
                'role_description' => 'required|nullable|string',
            ]);
            $role = OrganizationRoles::create([
                'role_name' => $validated['role_name'],
                'role_description' => $validated['role_description'],
            ]);
            return response()->json([
                'message' => 'Added Successfully',
                'role' => $role
            ], 201); // 201 Created
        } 
        catch (ValidationException $e) {
            return response()->json([
                'message' => 'Validation error',
                'errors' => $e->errors()
            ], 422); // 422 Unprocessable Entity
        } catch (Exception $e) {
            return response()->json([
                'message' => 'An error occurred while creating the role.',
                'error' => $e->getMessage()
            ], 500); // 500 Internal Server Error
        }
    }

    public function show(Request $request,$org_id, $role_id)
  {
        try {    $user = Auth::guard('applicationusers')->user();
             $organizationIds = $user->Client->Organization->pluck('organization_id')->toArray();
            if (!in_array($org_id, $organizationIds)) {
                return response()->json([
                    'messages' => 'unauthorized'
                ], 401);

            }
            $request->merge(['role_id' => $role_id,]);
            $request->validate([
                'role_id' => 'required|exists:organization_roles,role_id',
            ]);
            $role = OrganizationRoles::find($role_id);
            return response()->json($role, 200); // 200 OK
        } catch (\Illuminate\Validation\ValidationException $e) {
            // Handle validation errors
            return response()->json([
                'error' => 'Validation failed',
                'messages' => $e->errors()
            ], 422);

        } catch (\Exception $e) {
            // Catch any other exceptions
            return response()->json([
                'error' => 'Something went wrong: ' . $e->getMessage()
            ], 500);
        }
    }
 
    public function destroy(Request $request,$org_id, $role_id)
  {
        try {    $user = Auth::guard('applicationusers')->user();
             $organizationIds = $user->Client->Organization->pluck('organization_id')->toArray();
            if (!in_array($org_id, $organizationIds)) {
                return response()->json([
                    'messages' => 'unauthorized'
                ], 401);

            }
            $request->merge(['role_id' => $role_id]);
            // Validate the incoming request to ensure 'organization_id' and 'registration_id' are valid

            $request->validate([
                'role_id' => 'required|exists:organization_roles,role_id',
            ]);


            $organizationrole = OrganizationRoles::findOrFail($role_id);

            $organizationrole->delete(); // Eager load related documents
            return response()->json([
                'message' => 'Organization Roles deleted successfully.'
            ], 200);



        } catch (\Illuminate\Validation\ValidationException $e) {
            // Handle validation errors
            return response()->json([
                'error' => 'Validation failed',
                'messages' => $e->errors()
            ], 422);

        } catch (\Exception $e) {
            // Catch any other exceptions
            return response()->json([
                'error' => 'Something went wrong: ' . $e->getMessage()
            ], 500);
        }
    }

    public function update(Request $request, $org_id, $role_id)
  {
        try {    $user = Auth::guard('applicationusers')->user();
             $organizationIds = $user->Client->Organization->pluck('organization_id')->toArray();
            if (!in_array($org_id, $organizationIds)) {
                return response()->json([
                    'messages' => 'unauthorized'
                ], 401);

            }
            // Merge role_id into the request for validation
            $request->merge(['role_id' => $role_id]);
    
            // Validate the incoming request to ensure the role_id exists in the database
            $request->validate([
                'role_id' => 'required|exists:organization_roles,role_id',
            ]);
    
            // Validate the optional fields
            $validated = $request->validate([
                'role_name' => 'sometimes|string|max:50',
                'role_description' => 'sometimes|string',
            ]);
    
            // Retrieve the role by its role_id
            $role = OrganizationRoles::findOrFail($role_id);
    
            // Only update the fields that are provided in the request
            if (isset($validated['role_name'])) {
                $role->role_name = $validated['role_name'];
            }
            if (isset($validated['role_description'])) {
                $role->role_description = $validated['role_description'];
            }
    
            // Save the updated role
            $role->save();
    
            // Return success response
            return response()->json([
                'message' => 'Updated successfully',
                'role' => $role
            ], 200);
    
        } catch (ValidationException $e) {
            return response()->json([
                'message' => 'Validation error',
                'errors' => $e->errors()
            ], 422); // 422 Unprocessable Entity
        } catch (Exception $e) {
            return response()->json([
                'message' => 'An error occurred while updating the role.',
                'error' => $e->getMessage()
            ], 500); // 500 Internal Server Error
        }
    }
    

}
