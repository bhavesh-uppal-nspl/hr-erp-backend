<?php

namespace App\Http\Controllers\OrganizationController;

use App\Models\OrganizationModel\OrganizationEmploymentIncrementTypes;
use Illuminate\Support\Facades\Validator;
use App\Http\Controllers\Controller;
use App\Models\OrganizationModel\OrganizationEmpAddType;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Auth;

class OrganizationEmployeeIncrementTypeController extends Controller
{

    public function index(Request $request, $org_id)
    {
        try {

            $request->merge(['organization_id' => $org_id]);
            $user = Auth::guard('applicationusers')->user();
            $organizationIds = $user->Client->Organization->pluck('organization_id')->toArray();
            if (!in_array((int) $org_id, $organizationIds)) {
                return response()->json([
                    'message' => 'Unauthenticated'
                ], 401);
            }
            
            if ($request->input('mode') == 1) {
                $increments = OrganizationEmploymentIncrementTypes::where('organization_id', $org_id)->get();

              
                $mappedIncrements = $increments->map(function ($dep) {
                    return [
                        'increment_type'=>$dep->employee_increment_type_name,
                        'description' => $dep->description ?? '',
                     
                    ];
                });
                return response()->json($mappedIncrements);
            }



            $perPage = $request->get('per_page');
            $page = (int) $request->get('page', 1);
            $search = $request->get('search');
            $organizationId = $request->get('organization_id');
            $query = OrganizationEmploymentIncrementTypes::query();
            if (!empty($organizationId)) {
                $query->where('organization_id', $organizationId);
            }
            if (!empty($search)) {
                $query->where(function ($q) use ($search) {
                    $q->where('employee_increment_type_name', 'like', '%' . $search . '%')
                        ->orWhere('description', 'like', '%' . $search . '%');
                });
            }
            if ($perPage === 'all') {
                $statusTypes = $query->get();
            } elseif (!empty($perPage) && is_numeric($perPage)) {
                $perPage = (int) $perPage;
                $statusTypes = $query->paginate($perPage, ['*'], 'page', $page);
            } else {
                $statusTypes = $query->get();
            }
            return response()->json([
                'message' => 'Organization Increment Types fetched successfully',
                'increments' => $statusTypes
            ]);
        } catch (\Exception $e) {
            \Log::error('Error fetching attendance status types: ' . $e->getMessage());
            return response()->json([
                'message' => 'Failed to fetch attendance status types'
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
                'employee_increment_type_name' => [
                    'sometimes',
                    'nullable',
                    'string',
                    'max:255',
                    Rule::unique('organization_employee_increment_types', 'employee_increment_type_name')
                        ->where(function ($query) use ($org_id) {
                            return $query->where('organization_id', $org_id);
                        }),
                ],
                'description' => 'nullable|string|max:255',
                'is_active' => 'nullable|boolean',
            ]);
            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()], 422);
            }
            $data = $request->all();
            OrganizationEmploymentIncrementTypes::create($data);
            return response()->json([
                'message' => 'Organization Employement Increment Types added!.',

            ], 201);
        } catch (\Exception $e) {

            return response()->json([
                'error' => 'Something went wrong. Please try again later.',
                'details' => $e->getMessage()
            ], 500);
        }
    }


    public function show(Request $request, $org_id, $increment_id)
    {
        try {
            $user = Auth::guard('applicationusers')->user();
            $organizationIds = $user->Client->Organization->pluck('organization_id')->toArray();
            if (!in_array($org_id, $organizationIds)) {
                return response()->json([
                    'messages' => 'unauthorized'
                ], 401);

            }
            $request->merge(['organization_id' => $org_id, 'organization_employee_increment_type_id' => $increment_id]);
            $validator = Validator::make($request->all(), [
                'organization_id' => 'required|integer|exists:organizations,organization_id',
                'organization_employee_increment_type_id' => 'required|integer|exists:organization_employee_increment_types,organization_employee_increment_type_id',
            ]);
            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()], 422);
            }
            $increments = OrganizationEmploymentIncrementTypes::find($increment_id);
            return response()->json([
                'message' => "Orgaization Employement increments Types Found",
                'increments' => $increments
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Something went wrong. Please try again later.',
                'details' => $e->getMessage()
            ], 500);
        }
    }

    public function update(Request $request, $org_id, $increment_id)
    {
        try {
            $user = Auth::guard('applicationusers')->user();
            $organizationIds = $user->Client->Organization->pluck('organization_id')->toArray();
            if (!in_array($org_id, $organizationIds)) {
                return response()->json([
                    'messages' => 'unauthorized'
                ], 401);

            }
            // Merge route parameters into the request for validation
            $request->merge(['organization_id' => $org_id, 'organization_employee_increment_type_id' => $increment_id]);

            $rules = [
                'organization_id' => 'required|integer|exists:organizations,organization_id',
                'organization_employee_increment_type_id' => 'required|integer|exists:organization_employee_increment_types,organization_employee_increment_type_id',
                'employee_increment_type_name' => [
                        'sometimes',
                        'string',
                        'max:255',
                        Rule::unique('organization_employee_increment_types', 'employee_increment_type_name')
                            ->ignore($increment_id, 'organization_employee_increment_type_id')
                            ->where(function ($query) use ($request) {
                                return $query->where('organization_id', $request->organization_id);
                            }),
                    ],
                'description' => 'nullable|string|max:255',
                'is_active' => 'nullable|boolean',
            ];

            // Run validation
            $validator = Validator::make($request->all(), $rules);

            if ($validator->fails()) {
                return response()->json([
                    'error' => 'Validation failed',
                    'message' => $validator->errors()
                ], 422);
            }

            // Find the location
            $increments = OrganizationEmploymentIncrementTypes::find($increment_id);

            $increments->update($request->only([
                'employee_increment_type_name',
                'description',
                'is_active'

            ]));

            return response()->json([
                'message' => 'Organization Employement Increment Type updated successfully.',

            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Something went wrong. Please try again later.',
                'details' => $e->getMessage()
            ], 500);
        }
    }

    public function destroy(Request $request, $org_id, $increment_id)
    {
        try {
            $user = Auth::guard('applicationusers')->user();
            $organizationIds = $user->Client->Organization->pluck('organization_id')->toArray();
            if (!in_array($org_id, $organizationIds)) {
                return response()->json([
                    'messages' => 'unauthorized'
                ], 401);
            }
            $request->merge(['organization_id' => $org_id, 'organization_employee_increment_type_id' => $increment_id]);
            $validator = Validator::make($request->all(), [
                'organization_id' => 'required|integer|exists:organizations,organization_id',
                   'organization_employee_increment_type_id' => 'required|integer|exists:organization_employee_increment_types,organization_employee_increment_type_id',
               
            ]);
            if ($validator->fails()) {
                return response()->json([
                    'error' => 'Validation failed',
                    'message' => $validator->errors()
                ], 422);
            }
            $increment = OrganizationEmploymentIncrementTypes::find($increment_id);
            $increment->delete();
            return response()->json([
                'message' => 'Organization Employement increment Type Deleted Successfully'
            ], 200);
        } catch (Exception $e) {
            if ($e instanceof \Illuminate\Database\QueryException && $e->errorInfo[1] == 1451) {
                return response()->json([
                    'error' => 'Cannot employment increment type because it is linked with other records. Please delete dependent records first.'
                ], 409);
            }

            // For other exceptions
            return response()->json([
                'error' => 'Failed to delete Employement Increment type.',
                'exception' => $e->getMessage()
            ], 500);
        }
    }
}
