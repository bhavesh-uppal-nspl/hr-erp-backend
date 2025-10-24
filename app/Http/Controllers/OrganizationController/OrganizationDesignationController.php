<?php

namespace App\Http\Controllers\OrganizationController;
use App\Models\EmployeesModel\Employees;
use Illuminate\Support\Facades\Validator;
use App\Http\Controllers\Controller;
use App\Models\OrganizationModel\OrganizationDesignation;
use Exception;
use Illuminate\Http\Request;
use Auth;
use Illuminate\Validation\Rule;

class OrganizationDesignationController extends Controller
{
    public function indexV1(Request $request)
    {
        try {
            $perPage = $request->get('per_page', 10); // Keep as string for "all" comparison
            $page = (int) $request->get('page', 1);
            $search = $request->get('search');
            $organizationId = $request->get('organization_id');

            $query = OrganizationDesignation::with('department')->where('organization_id', $organizationId);

            // Search filter
            if (!empty($search)) {
                $query->where(function ($q) use ($search) {
                    $q->where('designation_name', 'like', '%' . $search . '%')
                        ->orWhere('designation_short_name', 'like', '%' . $search . '%');
                });
            }
            // Handle pagination or return all
            $designationns = ($perPage === 'all')
                ? $query->get()
                : $query->paginate((int) $perPage, ['*'], 'page', $page);

            return response()->json([
                'message' => 'Designations fetched successfully',
                'data' => $designationns
            ]);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Failed to fetch Designations'], 500);
        }
    }
    // get all designation of particlar department   
    public function getDesignationByDepartment(Request $request)
    {
        try {
            $departmentId = $request->get('department_id');
            $organizationId = $request->get('organization_id');

            if (!$departmentId || !$organizationId) {
                return response()->json([
                    'message' => 'Both department_id and organization_id are required'
                ], 400);
            }

            $designationall = OrganizationDesignation::with('department')
                ->where('organization_department_id', $departmentId)
                ->where('organization_id', $organizationId)
                ->get();

            return response()->json([
                'message' => 'Designation fetched successfully for the department and organization',
                'data' => $designationall->values()
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
                $designation = OrganizationDesignation::with('department')->where('organization_id', $org_id)->get();
                if ($designation->isEmpty()) {
                    return response()->json([
                        'message' => 'Employee not found.'
                    ], 404);
                }
                $mappedDesignation = $designation->map(function ($dep) {
                    return [
                     
                        'designation_name' => $dep->designation_name ?? '',
                        'designation_short_name' => $dep->designation_short_name ?? '',
                        'department' => $dep->department->department_name ?? '',
                    ];
                });
                return response()->json($mappedDesignation);
            }
            $query = OrganizationDesignation::with('department')->where('organization_id', $org_id);
            $search = $request->input('search');
            if ($search) {
                $query->where(function ($q) use ($search) {
                    $q->where('designation_name', 'like', "%{$search}%")
                        ->orWhere('designation_short_name', 'like', "%{$search}%");
                });
            }

            $data = $query->orderBy('created_at', 'desc')->get();

            return response()->json([
                'message' => 'OK',
                'organizationdesignation' => $data,
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
                'organization_department_id' => 'required|integer|exists:organization_departments,organization_department_id',
                'designation_name' => [
                    'required',
                    'string',
                    'max:255',
                    Rule::unique('organization_designations')->where(function ($query) use ($request) {
                        return $query->where('organization_id', $request->organization_id);
                    }),
                ],
                'designation_short_name' => [
                    'required',
                    'string',
                    'max:100',
                    Rule::unique('organization_designations')->where(function ($query) use ($request) {
                        return $query->where('organization_id', $request->organization_id);
                    }),
                ],
            ]);
            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()], 422);
            }
            $data = $request->all();
            $organizationdesignation = OrganizationDesignation::create(array_merge($data));
            return response()->json([
                'message' => 'Added SuccessFully.',
                'organizationdesignation' => $organizationdesignation
            ], 201);
        } catch (\Exception $e) {
            // Handle any other errors and return a response
            return response()->json([
                'error' => 'Something went wrong. Please try again later.',
                'details' => $e->getMessage()
            ], 500);
        }
    }

    public function show(Request $request, $org_id, $designation_id)
    {
        try {
            $user = Auth::guard('applicationusers')->user();
            $organizationIds = $user->Client->Organization->pluck('organization_id')->toArray();
            if (!in_array($org_id, $organizationIds)) {
                return response()->json([
                    'messages' => 'unauthorized'
                ], 401);

            }
            $request->merge(['organization_id' => $org_id, 'organization_designation_id' => $designation_id]);
            $validator = Validator::make($request->all(), [
                'organization_id' => 'required|integer|exists:organizations,organization_id',

                'organization_designation_id' => 'required|integer|exists:organization_designations,organization_designation_id',
            ]);
            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()], 422);
            }
            $organizationdesignation = OrganizationDesignation::find($designation_id);
            return response()->json([
                'message' => 'Organization Designation Found',
                'organizationdesignation' => $organizationdesignation
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Something went wrong. Please try again later.',
                'details' => $e->getMessage()
            ], 500);
        }
    }

    public function destroy(Request $request, $org_id, $designation_id)
    {
        try {
            $user = Auth::guard('applicationusers')->user();
            $organizationIds = $user->Client->Organization->pluck('organization_id')->toArray();

            if (!in_array($org_id, $organizationIds)) {
                return response()->json([
                    'message' => 'Unauthorized'
                ], 401);
            }

            $request->merge(['organization_id' => $org_id, 'organization_designation_id' => $designation_id]);
            $validator = Validator::make($request->all(), [
                'organization_id' => 'required|integer|exists:organizations,organization_id',
                'organization_designation_id' => 'required|integer|exists:organization_designations,organization_designation_id',
            ]);

            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()], 422);
            }

            $designation = OrganizationDesignation::find($designation_id);
            if (!$designation) {
                return response()->json([
                    'message' => 'Designation not found.'
                ], 404);
            }

            // Check if used in employees table
            $designationInUse = Employees::where('organization_designation_id', $designation_id)->exists();
            if ($designationInUse) {
                return response()->json([
                    'message' => 'This designation is assigned to one or more employees and cannot be deleted.'
                ], 409);
            }

            $designation->delete();
            return response()->json([
                'message' => 'Designation Deleted Successfully'
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Something went wrong. Please try again later.',
                'details' => $e->getMessage()
            ], 500);
        }
    }



    public function update(Request $request, $org_id, $designation_id)
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
                'organization_designation_id' => $designation_id
            ]);
            $rules = [
                'organization_id' => 'required|integer|exists:organizations,organization_id',
                'organization_department_id' => 'required|integer|exists:organization_departments,organization_department_id',
                'organization_designation_id' => 'required|integer|exists:organization_designations,organization_designation_id',
                'designation_name' => [
                    'sometimes',
                    'string',
                    'max:255',
                    Rule::unique('organization_designations', 'designation_name')
                        ->ignore($designation_id, 'organization_designation_id')
                        ->where(function ($query) use ($request) {
                            return $query->where('organization_id', $request->organization_id);
                        }),
                ],

                'designation_short_name' => [
                    'sometimes',
                    'string',
                    'max:100',
                    Rule::unique('organization_designations', 'designation_short_name')
                        ->ignore($designation_id, 'organization_designation_id')
                        ->where(function ($query) use ($request) {
                            return $query->where('organization_id', $request->organization_id);
                        }),
                ],

            ];
            $validator = Validator::make($request->all(), $rules);
            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()], 422);
            }
            $organizationresignation = OrganizationDesignation::find($designation_id);
            $organizationresignation->update($request->only([
                'designation_name',
                'designation_short_name',
                'organization_department_id'
            ]));
            return response()->json([
                'message' => 'Department  updated successfully.',
                'organizationresignation' => $organizationresignation
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Something went wrong. Please try again later.',
                'details' => $e->getMessage()
            ], 500);
        }

    }




    public function getAll(Request $request, $org_id)
    {
        try {
            $data = OrganizationDesignation::where('organization_id', $org_id)->get();

            return response()->json($data);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Something went wrong. Please try again later.',
                'details' => $e->getMessage()
            ], 500);
        }
    }

}

