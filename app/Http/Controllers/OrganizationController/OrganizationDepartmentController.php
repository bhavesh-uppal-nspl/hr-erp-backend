<?php

namespace App\Http\Controllers\OrganizationController;

use App\Models\EmployeesModel\Employees;
use App\Models\OrganizationModel\OrganizationDepartmentLocation;
use App\Models\OrganizationModel\OrganizationDesignation;
use Illuminate\Support\Facades\Validator;
use App\Http\Controllers\Controller;
use App\Models\OrganizationModel\OrganizationDepartment;
use Exception;
use Illuminate\Http\Request;
use Auth;
use DB;
use Illuminate\Validation\Rule;

class OrganizationDepartmentController extends Controller
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

            // ✅ when mode = 1 → return mapped department (lower logic)
            if ($request->input('mode') == 1) {
                $department = OrganizationDepartment::where('organization_id', $org_id)->get();

                if ($department->isEmpty()) {
                    return response()->json([
                        'message' => 'Employee not found.'
                    ], 404);
                }

                $mappedDepartment = $department->map(function ($dep) {
                    return [
                        'Department_name' => $dep->department_name ?? '',
                        'Short_name' => $dep->department_short_name ?? '',
                        'mail_id' => $dep->department_mail_id ?? '',
                        'Employees_count' => $dep->department_employees_count ?? '',
                        'description' => $dep->description ?? '',
                    ];
                });

                return response()->json($mappedDepartment);
            }

            // ✅ when mode is not 1 → return searchable department list (upper logic)
            $query = OrganizationDepartment::where('organization_id', $org_id);
            $per = $request->input('per_page', 10);
            $search = $request->input('search');

            if ($search) {
                $query->where(function ($q) use ($search) {
                    $q->where('department_name', 'like', "%{$search}%")
                        ->orWhere('department_short_name', 'like', "%{$search}%")
                        ->orWhere('description', 'like', "%{$search}%")
                        ->orWhere('department_mail_id', 'like', "%{$search}%");
                });
            }

            $data = $query->orderBy('created_at', 'desc')->get();

            return response()->json([
                'message' => 'OK',
                'Departments' => $data,
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Something went wrong. Please try again later.',
                'details' => $e->getMessage(),
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
                'department_name' => [
                    'required',
                    'string',
                    'max:255',
                    Rule::unique('organization_departments', 'department_name')
                        ->where(fn($query) => $query->where('organization_id', $request->organization_id)),
                ],

                'department_short_name' => [
                    'nullable',
                    'string',
                    'max:100',
                    Rule::unique('organization_departments', 'department_short_name')
                        ->where(fn($query) => $query->where('organization_id', $request->organization_id)),
                ],
                'department_mail_id' => [
                    'nullable',
                    'string',
                    'max:100',

                ],
                'department_employees_count' => 'sometimes|nullable|integer|min:0',
                'description' => 'sometimes|nullable|string|max:1000',
            ]);
            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()], 422);
            }
            $department = OrganizationDepartment::create($request->all());



            // crearte department location 
            OrganizationDepartmentLocation::create(([
                'organization_id' => $org_id,
                'organization_location_id' => $request->organization_location_id,
                'organization_department_id' => $department->organization_department_id

            ]));

            return response()->json([
                'message' => 'Organization Department Added SuccessFully.',
                'department' => $department
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
    public function show(Request $request, $org_id, $department_id)
    {
        try {
            $user = Auth::guard('applicationusers')->user();
            $organizationIds = $user->Client->Organization->pluck('organization_id')->toArray();
            if (!in_array($org_id, $organizationIds)) {
                return response()->json([
                    'messages' => 'unauthorized'
                ], 401);

            }
            $request->merge(['organization_id' => $org_id, 'oragnization_department_id' => $department_id]);
            $validator = Validator::make($request->all(), [
                'organization_id' => 'required|integer|exists:organizations,organization_id',

                'oragnization_department_id' => 'required|integer|exists:organization_departments,organization_department_id',
            ]);
            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()], 422);
            }
            $department = OrganizationDepartment::find($department_id);
            $department->load('departmentlocation');
            return response()->json([
                'message' => 'Organization Department Found',
                'department' => $department
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Something went wrong. Please try again later.',
                'details' => $e->getMessage()
            ], 500);
        }
    }

    // update the orgaization 
    public function update(Request $request, $org_id, $department_id)
    {
        try {
            $user = Auth::guard('applicationusers')->user();
            $organizationIds = $user->Client->Organization->pluck('organization_id')->toArray();

            if (!in_array($org_id, $organizationIds)) {
                return response()->json([
                    'messages' => 'unauthorized'
                ], 401);
            }

            $request->merge([
                'organization_id' => $org_id,
                'organization_department_id' => $department_id,
            ]);

            $rules = [
                'organization_id' => 'required|integer|exists:organizations,organization_id',
                'organization_department_id' => 'required|integer|exists:organization_departments,organization_department_id',
                'department_name' => [
                    'nullable',
                    'string',
                    'max:255',
                    Rule::unique('organization_departments', 'department_name')
                        ->where('organization_id', $org_id)
                        ->ignore($department_id, 'organization_department_id')
                ],
                'department_short_name' => [
                    'nullable',
                    'string',
                    'max:100',
                    Rule::unique('organization_departments', 'department_short_name')
                        ->where('organization_id', $org_id)
                        ->ignore($department_id, 'organization_department_id')
                ],
                'department_mail_id' => 'sometimes|nullable|string|max:100',
                'department_employees_count' => 'sometimes|nullable|integer|min:0',
                'description' => 'sometimes|nullable|string|max:1000',
                'organization_location_id' => 'sometimes|nullable|integer|exists:organization_locations,organization_location_id'
            ];

            $validator = Validator::make($request->all(), $rules);
            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()], 422);
            }

            $department = OrganizationDepartment::find($department_id);

            // ✅ Update department data
            $department->update($request->only([
                'department_name',
                'department_mail_id',
                'department_short_name',
                'department_employees_count',
                'description',
            ]));

            // ✅ Update location mapping (if it exists)
            if ($request->has('organization_location_id')) {
                $existingLocation = OrganizationDepartmentLocation::where('organization_department_id', $department_id)->first();

                if ($existingLocation) {
                    $existingLocation->update([
                        'organization_location_id' => $request->organization_location_id
                    ]);
                }
            }

            return response()->json([
                'message' => 'Department updated successfully.',
                'department' => $department
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Something went wrong. Please try again later.',
                'details' => $e->getMessage()
            ], 500);
        }
    }


    // delete the orgaization  
    public function destroy(Request $request, $org_id, $department_id)
    {
        try {
            $user = Auth::guard('applicationusers')->user();
            $organizationIds = $user->Client->Organization->pluck('organization_id')->toArray();

            if (!in_array($org_id, $organizationIds)) {
                return response()->json([
                    'message' => 'Unauthorized access.'
                ], 401);
            }

            $request->merge([
                'organization_id' => $org_id,
                'organization_department_id' => $department_id
            ]);

            $validator = Validator::make($request->all(), [
                'organization_id' => 'required|integer|exists:organizations,organization_id',
                'organization_department_id' => 'required|integer|exists:organization_departments,organization_department_id',
            ]);

            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()], 422);
            }

            $department = OrganizationDepartment::find($department_id);
            if (!$department) {
                return response()->json([
                    'message' => 'Department not found.'
                ], 404);
            }

            DB::beginTransaction();

            // delette it from organization department location 

            // Step 1: Get all designations under this department
            $designationIds = OrganizationDesignation::where('organization_department_id', $department_id)
                ->pluck('organization_designation_id');

            // Step 2: Delete all employees assigned to those designations
            if ($designationIds->isNotEmpty()) {
                Employees::where('organization_designation_id', $designationIds)->delete();

                // Step 3: Delete all designations
                OrganizationDesignation::where('organization_designation_id', $designationIds)->delete();
            }


            OrganizationDepartmentLocation::where('organization_department_id', $department_id)->delete();

            // Step 4: Delete the department itself
            $department->forceDelete();

            DB::commit();

            return response()->json([
                'message' => 'Department, its designations, and related employees deleted successfully.'
            ], 200);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'error' => 'Something went wrong. Please try again later.',
                'details' => $e->getMessage()
            ], 500);
        }
    }


    public function getAll(Request $request, $org_id)
    {
        try {
            $data = OrganizationDepartment::where('organization_id', $org_id)->get();
            return response()->json($data);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Something went wrong. Please try again later.',
                'details' => $e->getMessage()
            ], 500);
        }

    }



}
