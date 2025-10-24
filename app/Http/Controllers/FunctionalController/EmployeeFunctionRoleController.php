<?php

namespace App\Http\Controllers\FunctionalController;
use App\Models\FunctionalModels\EmployeeFunctionRole;
use Auth;
use Illuminate\Support\Facades\Validator;
use App\Http\Controllers\Controller;
use Exception;
use Illuminate\Http\Request;

class EmployeeFunctionRoleController extends Controller
{
    public function index(Request $request, $org_id)
    {
        try {
            $request->merge(['organization_id' => $org_id]);
            $validator = Validator::make($request->all(), [
                'organization_id' => 'required|integer|exists:organizations,organization_id',
            ]);
            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()], 422);
            }


             if ($request->input('mode') == 1) {
                $funrole = EmployeeFunctionRole::with('functionRole', 'RoleSpecialization','Employee')->where('organization_id', $org_id)->get();

            if ($funrole->isEmpty()) {
                return response()->json([
                    'message' => 'Functional Role not found.'
                ], 404);
            }
                $mappedfunrole = $funrole->map(function ($dep) {
                    return [
                        'employee_name'=>$dep->Employee->first_name.' '.$dep->Employee->last_name,
                        'functional_role' => $dep->functionRole->functional_role_name ?? '',
                        'functional_role_specialization' => $dep->RoleSpecialization->functional_role_specialization_name ?? '',
                        'is_primary' => $dep->is_primary ?? '',
                        'is_active' => $dep->is_active ?? '',
                        'assigned_on' => $dep->assigned_on  ?? '',
                       
                    ];
                });
                return response()->json($mappedfunrole);
            }
            
            $query = EmployeeFunctionRole::with('functionRole', 'RoleSpecialization','Employee')->where('organization_id', $org_id);
            $per = $request->input('per_page', 10);
            $search = $request->input('search');
            if ($search) {
                $query->where(function ($q) use ($search) {
                    $q->where('internship_type_name', 'like', "%{$search}%");
                });
            }
            $data = $query->orderBy('created_at', 'desc')->paginate($per);
            return response()->json([
                'message' => 'OK',
                'functional' => $data,
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
                    'message' => 'Unauthorized'
                ], 401);
            }

            $request->merge(['organization_id' => $org_id]);

            $validator = Validator::make($request->all(), [
                'organization_id' => 'required|integer|exists:organizations,organization_id',
                'organization_entity_id' => 'nullable|integer|exists:organization_entities,organization_entity_id',
                'employee_id' => 'nullable|exists:employees,employee_id',
                'organization_functional_role_id' => 'nullable|exists:organization_functional_roles,organization_functional_role_id',
                'organization_functional_role_specialization_id' => 'nullable|exists:organization_functional_role_specializations,organization_functional_role_specialization_id',
                'is_primary' => 'nullable|boolean',
                'is_active' => 'nullable|boolean',
                'assigned_on' => 'nullable|date',
                'unassigned_on' => 'nullable|date',

            ]);

            if ($validator->fails()) {
                return response()->json([
                    'errors' => $validator->errors()
                ], 422);
            }

            $data = $request->all();
            $functional = EmployeeFunctionRole::create($data);
            return response()->json([
                'message' => 'Employee function role Record Added Successfully.',
                'functional' => $functional
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Something went wrong. Please try again later.',
                'details' => $e->getMessage()
            ], 500);
        }
    }

    public function show(Request $request, $org_id, $role_id)
    {
        try {
            $user = Auth::guard('applicationusers')->user();
            $organizationIds = $user->Client->Organization->pluck('organization_id')->toArray();
            if (!in_array($org_id, $organizationIds)) {
                return response()->json([
                    'messages' => 'unauthorized'
                ], 401);

            }
            $request->merge(['organization_id' => $org_id, 'employee_functional_role_id' => $role_id]);
            $validator = Validator::make($request->all(), [
                'organization_id' => 'required|integer|exists:organizations,organization_id',
                'employee_functional_role_id' => 'required|integer|exists:employee_functional_roles,employee_functional_role_id',
            ]);
            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()], 422);
            }
            $functional = EmployeeFunctionRole::find($role_id);
            return response()->json([
                'functional' => $functional
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Something went wrong. Please try again later.',
                'details' => $e->getMessage()
            ], 500);
        }
    }

    public function update(Request $request, $org_id, $role_id)
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
                'employee_functional_role_id' => $role_id
            ]);
            $rules = [
                'organization_id' => 'required|integer|exists:organizations,organization_id',
                'employee_functional_role_id' => 'required|integer|exists:employee_functional_roles,employee_functional_role_id',
                'organization_entity_id' => 'nullable|integer|exists:organization_entities,organization_entity_id',
                'employee_id' => 'nullable|exists:employees,employee_id',
                'organization_functional_role_id' => 'nullable|exists:organization_functional_roles,organization_functional_role_id',
                'organization_functional_role_specialization_id' => 'nullable|exists:organization_functional_role_specializations,organization_functional_role_specialization_id',
                'is_primary' => 'nullable|boolean',
                'is_active' => 'nullable|boolean',
                'assigned_on' => 'nullable|date',
                'unassigned_on' => 'nullable|date',


            ];
            $validator = Validator::make($request->all(), $rules);
            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()], 422);
            }
            $functional = EmployeeFunctionRole::find($role_id);
            $functional->update(attributes: $request->only([
                'employee_id',
                'organization_functional_role_id',
                'organization_functional_role_specialization_id',
                'is_primary',
                'is_active',
                'assigned_on',
                'unassigned_on',
                'organization_id',
                'organization_entity_id'
            ]));
            return response()->json([
                'functional' => $functional
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Something went wrong. Please try again later.',
                'details' => $e->getMessage()
            ], 500);
        }
    }

    public function destroy(Request $request, $org_id, $role_id)
    {
        try {
            $user = Auth::guard('applicationusers')->user();
            $organizationIds = $user->Client->Organization->pluck('organization_id')->toArray();
            if (!in_array($org_id, $organizationIds)) {
                return response()->json([
                    'messages' => 'unauthorized'
                ], 401);
            }
            $request->merge(['organization_id' => $org_id, 'organization_functional_role_id' => $role_id]);
            $validator = Validator::make($request->all(), [
                'organization_id' => 'required|integer|exists:organizations,organization_id',
                'employee_functional_role_id' => 'required|integer|exists:employee_functional_roles,employee_functional_role_id',
            ]);
            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()], 422);
            }
            $functional = EmployeeFunctionRole::find($role_id);
            $functional->delete();
            return response()->json([
                'message' => 'Functional  Deleted SuccessFully !'
            ], 200); // or just remove 200 — it's the default

        } catch (Exception $e) {
            if ($e instanceof \Illuminate\Database\QueryException && $e->errorInfo[1] == 1451) {
                // Foreign key constraint violation
                return response()->json([
                    'error' => 'Cannot delete Business Unit type because it is linked with other records. Please delete dependent records first.'
                ], 409); // 409 Conflict
            }

            // For other exceptions
            return response()->json([
                'error' => 'Failed to delete ownership type.',
                'exception' => $e->getMessage() // Optional: remove in production
            ], 500);
        }
    }
}
