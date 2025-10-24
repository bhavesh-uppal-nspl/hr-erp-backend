<?php

namespace App\Http\Controllers\OrganizationController;

use Auth;
use Illuminate\Support\Facades\Validator;
use App\Http\Controllers\Controller;
use App\Models\OrganizationModel\Organization;
use App\Models\OrganizationModel\OrganizationDepartmentLocation;
use Exception;
use Illuminate\Http\Request;
class OrganizationDepartmentLocationController extends Controller
{


    public function getLocationsByDepartment(Request $request)
    {
        try {
            $departmentId = $request->get('department_id');
            $organizationId = $request->get('organization_id');

            if (!$departmentId || !$organizationId) {
                return response()->json([
                    'message' => 'Both department_id and organization_id are required'
                ], 400);
            }

            $locations = OrganizationDepartmentLocation::with('location')
                ->where('organization_department_id', $departmentId)
                ->where('organization_id', $organizationId)
                ->get();

            return response()->json([
                'message' => 'Locations fetched successfully for the department and organization',
                'data' => $locations->values()
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to fetch locations',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function index(Request $request, $org_id)
    {
        try {
            $search = $request->get('search');
            $organizationId = $request->get('organization_id');

            $query = OrganizationDepartmentLocation::with(['department', 'locations'])
                ->where('organization_id', $organizationId);

            // Search filter
            if (!empty($search)) {
                $query->where(function ($q) use ($search) {
                    $q->where('department_name', 'like', '%' . $search . '%')
                        ->orWhere('location_name', 'like', '%' . $search . '%');
                });
            }

            $departmentLocation = $query->get(); // Always return all results

            return response()->json([
                'message' => 'Department fetched successfully',
                'data' => $departmentLocation
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to fetch Designations',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function store(Request $request, $org_id)
    {
        try {
            $request->merge(['organization_id' => $org_id]);
            $validator = Validator::make($request->all(), [
                'organization_id' => 'required|integer|exists:organizations,organization_id',
                'organization_location_id' => 'required|integer|exists:organization_locations,organization_location_id',
                'organization_department_id' => 'required|integer|exists:organization_departments,organization_department_id',
            ]);
            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()], 422);
            }
            $data = $request->all();
            $departmentlocation = OrganizationDepartmentLocation::create(array_merge($data));
            return response()->json([
                'message' => 'Added SuccessFully.',
                'location' => $departmentlocation
            ], 201);
        } catch (\Exception $e) {
            // Handle any other errors and return a response
            return response()->json([
                'error' => 'Something went wrong. Please try again later.',
                'details' => $e->getMessage()
            ], 500);
        }
    }

    // display specific organization 
    public function show(Request $request, $org_id, $department_location_id)
    {
        try {
            $request->merge(['organization_id' => $org_id, 'organization_department_location_id' => $department_location_id]);
            $validator = Validator::make($request->all(), [
                'organization_id' => 'required|integer|exists:organizations,organization_id',
                'organization_department_location_id' => 'required|integer|exists:organization_department_locations,organization_department_location_id',
            ]);
            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()], 422);
            }
            $departmentlocation = OrganizationDepartmentLocation::find($department_location_id);
            $departmentlocation->load('department', 'location');
            return response()->json([
                'message' => 'Organization Location Found',
                'departmentlocation' => $departmentlocation
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Something went wrong. Please try again later.',
                'details' => $e->getMessage()
            ], 500);
        }
    }

    public function update(Request $request, $org_id, $department_location_id)
    {
        try {
            $request->merge([
                'organization_id' => $org_id,
                'organization_department_location_id' => $department_location_id,
            ]);
            $rules = [
                'organization_id' => 'required|integer|exists:organizations,organization_id',
                'organization_department_location_id' => 'required|integer|exists:organization_department_locations,organization_department_location_id',
                'organization_location_id' => 'sometimes|integer|exists:organization_locations,organization_location_id',
                'organization_department_id' => 'sometimes|integer|exists:organization_departments,organization_department_id'
            ];
            $validator = Validator::make($request->all(), $rules);
            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()], 422);
            }
            $department = OrganizationDepartmentLocation::find($department_location_id);
            $department->update($request->only([

                'organization_location_id',
                'organization_department_id'
            ]));

            return response()->json([
                'message' => 'Department  updated successfully.',
                'department' => $department
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Something went wrong. Please try again later.',
                'details' => $e->getMessage()
            ], 500);
        }
    }

    public function destroy(Request $request, $org_id, $department_location_id)
    {
        try {
            $request->merge(['organization_id' => $org_id, 'organization_department_location_id' => $department_location_id]);
            $validator = Validator::make($request->all(), [
                'organization_id' => 'required|integer|exists:organizations,organization_id',
                'organization_department_location_id' => 'required|integer|exists:organization_department_locations,organization_department_location_id',
            ]);
            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()], 422);
            }
            $departmentlocation = OrganizationDepartmentLocation::find($department_location_id);
            $departmentlocation->delete();
            return response()->json([
                'message' => 'Department Location Deleted Successfully'
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Something went wrong. Please try again later.',
                'details' => $e->getMessage()
            ], 500);
        }
    }



}
