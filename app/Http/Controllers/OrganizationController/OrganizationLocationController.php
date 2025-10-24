<?php

namespace App\Http\Controllers\OrganizationController;
use App\Models\EmployeesModel\Employees;
use App\Models\GeneralModel\GeneralCities;
use App\Models\OrganizationModel\OrganizationDepartmentLocation;
use Auth;
use Illuminate\Support\Facades\Validator;
use App\Http\Controllers\Controller;
use App\Models\OrganizationModel\OrganizationLocation;
use Exception;
use Illuminate\Http\Request;

class OrganizationLocationController extends Controller
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
            $request->merge(['organization_id' => $org_id]);
            $validator = Validator::make($request->all(), [
                'organization_id' => 'required|integer|exists:organizations,organization_id',

            ]);
            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()], 422);
            }

            if ($request->input('mode') == 1) {
                $location = OrganizationLocation::with('locationOwnershiptype', 'city','country','state')->where('organization_id', $org_id)->get();

                if ($location->isEmpty()) {
                    return response()->json([
                        'message' => 'Location not found.'
                    ], 404);
                }
                $mappedLocation = $location->map(function ($dep) {
                    return [
                        'location_name'=>$dep->location_name,
                        'location_ownership_type' => $dep->location_ownershiptype->location_ownership_type_name ?? '',
                        'addressline1' => $dep->addressline1 ?? '',
                        'addressline2' => $dep->addressline2 ?? '',
                        'postal_code' => $dep->postal_code ?? '',
                        'number_of_floors' => $dep->number_of_floors  ?? '',
                        'country' => $dep->country->country_name ?? '',
                        'state' => $dep->state->state_name ?? '',
                        'city' => $dep->city->city_name ?? '',
                        'area_sq_ft' => $dep->area_sq_ft  ?? '',
                    ];
                });
                return response()->json($mappedLocation);
            }

             $locations = OrganizationLocation::with('locationOwnershiptype','city','country','state')->where('organization_id', $org_id)->get();
            return response()->json([
                'message' => "All Organization Location",
                'locations' => $locations
            ], 200);

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
                'organization_location_ownership_type_id' => 'nullable|integer|exists:organization_location_ownership_types,organization_location_ownership_type_id',
                'general_city_id' => 'nullable|integer|exists:general_cities,general_city_id',
                'general_state_id' => 'required|integer|exists:general_states,general_state_id',
                'addressline1' => 'nullable|string|max:255|different:addressline2',
                'addressline2' => 'nullable|string|max:255|different:addressline1',
                'postal_code' => 'nullable|string|max:20',
                'number_of_floors' => 'nullable|nullable|integer',
                'area_sq_ft' => 'nullable|nullable|numeric',
            ]);
            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()], 422);
            }

            $city = GeneralCities::find($request->general_city_id);
            $data = $request->all();
            $data['location_latitude'] = $city->city_latitude;
            $data['location_longitude'] = $city->city_longitude;

            $location = OrganizationLocation::create($data);

            return response()->json([
                'message' => 'Added Successfully.',
                'location' => $location
            ], 201);
        } catch (\Exception $e) {
            // Handle any other errors and return a response
            return response()->json([
                'error' => 'Something went wrong. Please try again later.',
                'details' => $e->getMessage()
            ], 500);
        }
    }

    public function show(Request $request, $org_id, $location_id)
    {
        try {
            $user = Auth::guard('applicationusers')->user();
            $organizationIds = $user->Client->Organization->pluck('organization_id')->toArray();
            if (!in_array($org_id, $organizationIds)) {
                return response()->json([
                    'messages' => 'unauthorized'
                ], 401);

            }
            $request->merge(['organization_id' => $org_id, 'organization_location_id' => $location_id]);
            $validator = Validator::make($request->all(), [
                'organization_id' => 'required|integer|exists:organizations,organization_id',
                'organization_location_id' => 'required|integer|exists:organization_locations,organization_location_id',
            ]);
            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()], 422);
            }
            $location = OrganizationLocation::find($location_id);
            $location->load(['locationOwnershiptype', 'city', 'country', 'state']);
            return response()->json([
                'message' => 'Organization Location Found ',
                'location' => $location
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Something went wrong. Please try again later.',
                'details' => $e->getMessage()
            ], 500);
        }
    }

    public function update(Request $request, $org_id, $location_id)
    {
        try {
            $user = Auth::guard('applicationusers')->user();
            $organizationIds = $user->Client->Organization->pluck('organization_id')->toArray();
            if (!in_array($org_id, $organizationIds)) {
                return response()->json([
                    'messages' => 'unauthorized'
                ], 401);

            }
            $request->merge(['organization_id' => $org_id, 'organization_location_id' => $location_id]);
            $rules = [
                'organization_id' => 'required|integer|exists:organizations,organization_id',
                'organization_location_id' => 'required|integer|exists:organization_locations,organization_location_id',
                'organization_location_ownership_type_id' => 'nullable|integer|exists:organization_location_ownership_types,organization_location_ownership_type_id',
                'location_latitude' => 'sometimes|nullable|numeric',
                'general_city_id' => 'sometimes|integer|exists:general_cities,general_city_id',
                'general_state_id' => 'sometimes|integer|exists:general_states,general_state_id',
                'location_longitude' => 'sometimes|nullable|numeric',
                'addressline1' => 'sometimes|nullable|string|max:255',
                'addressline2' => 'sometimes|nullable|string|max:255',
                'postal_code' => 'sometimes|nullable|string|max:20',
                'number_of_floors' => 'sometimes|nullable|integer',
                'area_sq_ft' => 'sometimes|nullable|numeric',
            ];
            $validator = Validator::make($request->all(), $rules);

            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()], 422);
            }

            if ($request->has('general_city_id')) {
                $city = GeneralCities::find($request->general_city_id);

                if ($city) {
                    $request->merge([
                        'location_latitude' => $city->city_latitude,
                        'location_longitude' => $city->city_longitude,
                    ]);
                }
            }

            $location = OrganizationLocation::find($location_id);
            $location->update($request->only([
                'location_name',
                'location_latitude',
                'location_longitude',
                'addressline1',
                'addressline2',
                'postal_code',
                'number_of_floors',
                'area_sq_ft',
                'general_city_id',
                'general_state_id',
                'organization_location_ownership_type_id'
            ]));
            return response()->json([
                'message' => 'Organization Location updated successfully.',
                'location' => $location
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Something went wrong. Please try again later.',
                'details' => $e->getMessage()
            ], 500);
        }
    }

    public function destroy($org_id, $location_id)
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
                'organization_location_id' => $location_id
            ]);

            $validator = Validator::make($request->all(), [
                'organization_id' => 'required|integer|exists:organizations,organization_id',
                'organization_location_id' => 'required|integer|exists:organization_locations,organization_location_id',
            ]);

            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()], 422);
            }

            // First get all department location IDs linked to this location
            $deptLocationIds = OrganizationDepartmentLocation::where('organization_location_id', $location_id)
                ->pluck('organization_department_location_id');

            // Check if any employees exist with these department locations
            $employeeCount = Employees::whereIn('organization_location_department_id', $deptLocationIds)->count();

            if ($employeeCount > 0) {
                return response()->json([
                    'error' => 'Cannot delete location because employees are assigned to departments in this location.'
                ], 409); // Conflict status code
            }

            // No employees found, safe to delete department locations
            OrganizationDepartmentLocation::where('organization_location_id', $location_id)->delete();

            // Now delete the location
            $location = OrganizationLocation::find($location_id);
            if ($location) {
                $location->delete();
                return response()->json(['message' => 'Location Deleted Successfully'], 200);
            } else {
                return response()->json(['error' => 'Location not found.'], 404);
            }

        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Something went wrong. Please try again later.',
                'details' => $e->getMessage()
            ], 500);
        }
    }


}
