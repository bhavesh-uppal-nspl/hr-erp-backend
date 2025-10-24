<?php

namespace App\Http\Controllers\OrganizationController;
use App\Models\OrganizationModel\OrganizationLocationOwnershipType;
use Auth;
use Illuminate\Support\Facades\Validator;
use App\Http\Controllers\Controller;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class OrganizationLocationOwnershipTypeController extends Controller
{
    public function index(Request $request, $org_id)
    {
        try {
            $user = Auth::guard('applicationusers')->user();
            $organizationIds = $user->Client->Organization->pluck('organization_id')->toArray();
            if (!in_array($org_id, $organizationIds)) {
                return response()->json([
                    'messages' => 'unauthorized'
                ], 401);

            }
            $request->merge(['organization_id' => $org_id,]);
            $validator = Validator::make($request->all(), [
                'organization_id' => 'required|integer|exists:organizations,organization_id',

            ]);
            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()], 422);
            }


            $query = OrganizationLocationOwnershipType::where('organization_id', $org_id);
            $per = $request->input('per_page', 10);
            $search = $request->input('search');
            if ($search) {
                $query->where(function ($q) use ($search) {
                    $q->where('location_ownership_type_name', 'like', "%{$search}%")
                        ->orWhere('description', 'like', "%{$search}%");
                });
            }
             $data = $query->orderBy('created_at', 'desc')->paginate($per);
            return response()->json([
                'message' => 'OK',
                'ownershiptypes' => $data,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Something went wrong. Please try again later.',
                'details' => $e->getMessage()
            ], 500);
        }
    }

    public function store(Request $request, $org_id)
    {
        try {
            $user = Auth::guard('applicationusers')->user();
            $organizationIds = $user->Client->Organization->pluck('organization_id')->toArray();
            if (!in_array($org_id, $organizationIds)) {
                return response()->json([
                    'messages' => 'unauthorized'
                ], 401);

            }
            $request->merge(['organization_id' => $org_id]);
            $validator = Validator::make($request->all(), [
                'organization_id' => 'required|integer|exists:organizations,organization_id',
                'location_ownership_type_name' => [
                    'required',
                    'string',
                    'max:255',
                    Rule::unique('organization_location_ownership_types', 'location_ownership_type_name')
                        ->where(function ($query) use ($request) {
                            return $query->where('organization_id', $request->organization_id);
                        }),
                ],
                'description' => 'required|string|max:2555',
            ]);
            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()], 422);
            }
            $data = $request->all();
            $location = OrganizationLocationOwnershipType::create(array_merge($data));
            return response()->json([
                'message' => 'Added SuccessFully.',
                'ownershiptypes' => $location
            ], 201);

        } catch (\Exception $e) {
            // Handle any other errors and return a response
            return response()->json([
                'error' => 'Something went wrong. Please try again later.',
                'details' => $e->getMessage()
            ], 500);
        }
    }

    public function show(Request $request, $org_id, $ownershiptype_id)
    {
        try {
            $user = Auth::guard('applicationusers')->user();
            $organizationIds = $user->Client->Organization->pluck('organization_id')->toArray();
            if (!in_array($org_id, $organizationIds)) {
                return response()->json([
                    'messages' => 'unauthorized'
                ], 401);

            }
            $request->merge(['organization_id' => $org_id, 'organization_location_ownership_type_id' => $ownershiptype_id]);
            $validator = Validator::make($request->all(), [
                'organization_id' => 'required|integer|exists:organizations,organization_id',
                'organization_location_ownership_type_id' => 'required|integer|exists:organization_location_ownership_types,organization_location_ownership_type_id',

            ]);
            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()], 422);
            }
            $ownershiptype = OrganizationLocationOwnershipType::find($ownershiptype_id);

            return response()->json([
                'message' => 'Organization Location ownership type Found ',
                'ownershiptype' => $ownershiptype
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Something went wrong. Please try again later.',
                'details' => $e->getMessage()
            ], 500);
        }
    }


    public function update(Request $request, $org_id, $ownershiptype_id)
    {
        try {
            $user = Auth::guard('applicationusers')->user();
            $organizationIds = $user->Client->Organization->pluck('organization_id')->toArray();
            if (!in_array($org_id, $organizationIds)) {
                return response()->json([
                    'messages' => 'unauthorized'
                ], 401);

            }
            $request->merge(['organization_id' => $org_id, 'organization_location_ownership_type_id' => $ownershiptype_id]);
            $rules = [
                'organization_id' => 'required|integer|exists:organizations,organization_id',
                'organization_location_ownership_type_id' => 'required|integer|exists:organization_location_ownership_types,organization_location_ownership_type_id',
                'location_ownership_type_name' => [
                    'nullable',
                    'string',
                    'max:255',
                    Rule::unique('organization_location_ownership_types', 'location_ownership_type_name')
                        ->where(function ($query) use ($request) {
                            return $query->where('organization_id', $request->organization_id);
                        }),
                ],
                'description' => 'nullable|string|max:2555',

            ];
            $validator = Validator::make($request->all(), $rules);

            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()], 422);
            }
            $location = OrganizationLocationOwnershipType::find($ownershiptype_id);

            $location->update($request->only([
                'location_ownership_type_name',
                'description',

            ]));
            return response()->json([
                'message' => 'Organization Location updated successfully.',
                'location' => $location
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Something went wrong. Please try again later.',
                'details' => $e->getMessage()
            ], 500);
        }
    }


    public function destroy($org_id, $ownershiptype_id)
    {
        try {
            $user = Auth::guard('applicationusers')->user();
            $organizationIds = $user->Client->Organization->pluck('organization_id')->toArray();
            if (!in_array($org_id, $organizationIds)) {
                return response()->json([
                    'messages' => 'unauthorized'
                ], 401);
            }

            $request = new Request();
            $request->merge([
                'organization_id' => $org_id,
                'organization_location_ownership_type_id' => $ownershiptype_id
            ]);

            $validator = Validator::make($request->all(), [
                'organization_id' => 'required|integer|exists:organizations,organization_id',
               'organization_location_ownership_type_id' => 'required|integer|exists:organization_location_ownership_types,organization_location_ownership_type_id',
            ]);

            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()], 422);
            }

            // // First get all department location IDs linked to this location
            // $deptLocationIds = OrganizationDepartmentLocation::where('organization_location_id', $ownershiptype_id)
            //     ->pluck('organization_department_location_id');

            // // Check if any employees exist with these department locations
            // $employeeCount = Employees::whereIn('organization_location_department_id', $deptLocationIds)->count();

            // if ($employeeCount > 0) {
            //     return response()->json([
            //         'error' => 'Cannot delete location because employees are assigned to departments in this location.'
            //     ], 409); // Conflict status code
            // }

            // // No employees found, safe to delete department locations
            // OrganizationDepartmentLocation::where('organization_location_id', $location_id)->delete();

            // Now delete the location
            $location = OrganizationLocationOwnershipType::find($ownershiptype_id);
            if ($location) {
                $location->delete();
                return response()->json(['message' => 'Location Ownership Deleted Successfully'], 200);
            } else {
                return response()->json(['error' => 'Location Ownership not found.'], 404);
            }

        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Something went wrong. Please try again later.',
                'details' => $e->getMessage()
            ], 500);
        }
    }


}
