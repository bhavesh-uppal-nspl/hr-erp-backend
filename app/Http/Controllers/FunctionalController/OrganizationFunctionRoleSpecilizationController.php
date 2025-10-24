<?php

namespace App\Http\Controllers\FunctionalController;
use App\Models\FunctionalModels\FunctionalRoleSpecifization;
use Auth;
use Illuminate\Support\Facades\Validator;
use App\Http\Controllers\Controller;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class OrganizationFunctionRoleSpecilizationController extends Controller
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
                $role = FunctionalRoleSpecifization::with('functionRole')->where('organization_id', $org_id)->get();

                if ($role->isEmpty()) {
                    return response()->json([
                        'message' => 'Role not found.'
                    ], 404);
                }
                $mappedRole = $role->map(function ($dep) {
                    return [
                        'functional_role' => $dep->functionRole->organization_functional_role_name ?? '',
                        'functional_role_specialization_name' => $dep->functional_role_specialization_name ?? '',
                        'functional_role_specialization_code' => $dep->functional_role_specialization_code ?? '',
                        'description' => $dep->description ?? '',
                        'is_active' => $dep->is_active ?? '',
                        'work_duration_minutes' => $dep->work_duration_minutes ?? '',
                        'location' => $dep->location->location_name ?? '',
                        'work_shift_type' => $dep->shiftType->work_shift_type_name ?? '',
                    ];
                });
                return response()->json($mappedRole);
            }








            $query = FunctionalRoleSpecifization::with('functionRole')->where('organization_id', $org_id);
            $per = $request->input('per_page', 999);
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
                'organization_functional_role_id' => 'nullable|exists:organization_functional_roles,organization_functional_role_id',

                'functional_role_specialization_name' => [
                    'required',
                    'string',
                    'max:255',
                    Rule::unique('organization_functional_role_specializations', 'functional_role_specialization_name')
                        ->where(function ($query) use ($request) {
                            return $query->where('organization_id', $request->organization_id);
                        }),
                ],
                'functional_role_specialization_code' => [
                    'nullable',
                    'string',
                    'max:255',
                    Rule::unique('organization_functional_role_specializations', 'functional_role_specialization_code')
                        ->where(function ($query) use ($request) {
                            return $query->where('organization_id', $request->organization_id);
                        }),
                ],



                'description' => 'nullable|string|max:255'

            ]);

            if ($validator->fails()) {
                return response()->json([
                    'errors' => $validator->errors()
                ], 422);
            }

            $data = $request->all();
            $functional = FunctionalRoleSpecifization::create($data);
            return response()->json([
                'message' => 'Role Specfizations Added Successfully.',
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
            $request->merge(['organization_id' => $org_id, 'organization_functional_role_specialization_id' => $role_id]);
            $validator = Validator::make($request->all(), [
                'organization_id' => 'required|integer|exists:organizations,organization_id',
                'organization_functional_role_specialization_id' => 'required|integer|exists:organization_functional_role_specializations,organization_functional_role_specialization_id',
            ]);
            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()], 422);
            }
            $functional = FunctionalRoleSpecifization::find($role_id);
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
                'organization_functional_role_id' => $role_id
            ]);
            $rules = [
                'organization_id' => 'required|integer|exists:organizations,organization_id',
                'organization_functional_role_specialization_id' => 'required|integer|exists:organization_functional_role_specializations,organization_functional_role_specialization_id',
            ];
            $validator = Validator::make($request->all(), $rules);
            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()], 422);
            }
            $functional = FunctionalRoleSpecifization::find($role_id);
            $functional->update($request->only([
                'organization_functional_role_id',
                'organization_id',
                'functional_role_specialization_code',
                'functional_role_specialization_name',
                'description',
                'is_active',
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
                'organization_functional_role_specialization_id' => 'required|integer|exists:organization_functional_role_specializations,organization_functional_role_specialization_id',
            ]);
            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()], 422);
            }
            $functional = FunctionalRoleSpecifization::find($role_id);
            $functional->delete();
            return response()->json([
                'message' => 'Functional Role Specifications Deleted SuccessFully !'
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
