<?php

namespace App\Http\Controllers\OrganizationController;
use App\Models\OrganizationModel\OrganizationLeavePolicy;
use Illuminate\Support\Facades\Validator;
use App\Http\Controllers\Controller;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Auth;

class OrganizationLeavePolicyController extends Controller
{

    public function index(Request $request, $org_id)
    {
        try {
            $request->merge(['organization_id' => $org_id]);
            $user = Auth::guard('applicationusers')->user();
            // Get all organization IDs linked to the user
            $organizationIds = $user->Client->Organization->pluck('organization_id')->toArray();

            if (!in_array((int) $org_id, $organizationIds)) {
                return response()->json([
                    'message' => 'Unauthenticated'
                ], 401);
            }


            $perPage = $request->get('per_page');
            $page = (int) $request->get('page', 1);
            $search = $request->get('search');
            $organizationId = $request->get('organization_id');


             if ($request->input('mode') == 1) {
                $policy = OrganizationLeavePolicy::with( 'LeaveEntitlement.leavetype')->where('organization_id', $org_id)->get();

                if ($policy->isEmpty()) {
                    return response()->json([
                        'message' => 'Policy not found.'
                    ], 404);
                }
                $mappedPolicy = $policy->map(function ($dep) {
                    return [
                        'leave_entitlement'=>$dep->LeaveEntitlement->leavetype->leave_type_name ?? '',
                        'policy_name' => $dep->policy_name ?? '',
                        'policy_description' => $dep->policy_description ?? '',
                        'usage_period' => $dep->usage_period ?? '',
                        'custom_period_days' => $dep->custom_period_days ?? '',
                        'max_leaves_per_period' => $dep->max_leaves_per_period ?? '',
                        'is_active' => $dep->is_active  ?? '',
                        
                    ];
                });
                return response()->json($mappedPolicy);
            }






            $query = OrganizationLeavePolicy::with('LeaveEntitlement.leavetype');
            if (!empty($organizationId)) {
                $query->where('organization_id', $organizationId);
            }
            if (!empty($search)) {
                $query->where(function ($q) use ($search) {
                    $q->where('policy_name', 'like', '%' . $search . '%')
                        ->orWhere('policy_description', 'like', '%' . $search . '%');
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
                'message' => 'Organization Leave policy fetched successfully',
                'Policydata' => $statusTypes
            ]);
        } catch (\Exception $e) {
            \Log::error('Error fetching leave policy: ' . $e->getMessage());
            return response()->json([
                'message' => 'Failed to fetch leave policies'
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
                'organization_leave_entitlement_id' => 'required|integer|exists:organization_leave_entitlements,organization_leave_entitlement_id',
                'policy_name' => [
                    'sometimes',
                    'nullable',
                    'string',
                    'max:255',
                    Rule::unique('organization_leave_policies', 'policy_name')
                        ->where(function ($query) use ($org_id) {
                            return $query->where('organization_id', $org_id);
                        }),
                ],
                'usage_period' => 'nullable|in:weekly,monthly,quarterly,annual,custom',
                'max_leaves_per_period' => 'nullable|integer|min:1',
                'custom_period_days' => 'nullable|integer|min:1',
                'policy_description' => 'nullable|string|max:255',
                'is_active' => 'nullable|boolean',
            ]);
            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()], 422);
            }
            $data = $request->all();
            OrganizationLeavePolicy::create($data);
            return response()->json([
                'message' => 'Organization Leave Policy added!.',
            ], 201);
        } catch (\Exception $e) {

            return response()->json([
                'error' => 'Something went wrong. Please try again later.',
                'details' => $e->getMessage()
            ], 500);
        }
    }


    public function show(Request $request, $org_id, $policy_id)
    {
        try {
            $user = Auth::guard('applicationusers')->user();
            $organizationIds = $user->Client->Organization->pluck('organization_id')->toArray();
            if (!in_array($org_id, $organizationIds)) {
                return response()->json([
                    'messages' => 'unauthorized'
                ], 401);
            }
            $request->merge(['organization_id' => $org_id, 'organization_leave_policy_id' => $policy_id]);
            $validator = Validator::make($request->all(), [
                'organization_id' => 'required|integer|exists:organizations,organization_id',
                'organization_leave_policy_id' => 'required|integer|exists:organization_leave_policies,organization_leave_policy_id',
            ]);
            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()], 422);
            }
            $policy = OrganizationLeavePolicy::find($policy_id);
            $policy->load('LeaveEntitlement.leavetype');
            return response()->json([
                'message' => "Orgaization Leave Policy Found",
                'policies' => $policy
            ], status: 200);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Something went wrong. Please try again later.',
                'details' => $e->getMessage()
            ], 500);
        }
    }

    public function update(Request $request, $org_id, $policy_id)
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
            $request->merge(['organization_id' => $org_id, 'organization_leave_policy_id' => $policy_id]);

            $rules = [
                'organization_id' => 'required|integer|exists:organizations,organization_id',
                'organization_leave_policy_id' => 'required|integer|exists:organization_leave_policies,organization_leave_policy_id',
                'organization_leave_entitlement_id' => 'nullable|integer|exists:organization_leave_entitlements,organization_leave_entitlement_id',
               'policy_name' => [
                    'sometimes',
                    'string',
                    'max:255',
                    Rule::unique('organization_leave_policies', 'policy_name')
                        ->ignore($policy_id, 'organization_leave_policy_id')
                        ->where(function ($query) use ($request) {
                            return $query->where(
                                'organization_id',
                                $request->organization_id
                            );
                        }),
                ],
                'usage_period' => 'nullable|in:weekly,monthly,quarterly,annual,custom',
                'max_leaves_per_period' => 'nullable|integer|min:1',
                'custom_period_days' => 'nullable|integer|min:1',
                'policy_description' => 'nullable|string|max:255',
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
            $policy = OrganizationLeavePolicy::find($policy_id);

            $policy->update($request->only([
                'organization_id',
                'organization_entity_id',
                'organization_leave_entitlement_id',
                'policy_name',
                'policy_description',
                'usage_period',
                'custom_period_days',
                'max_leaves_per_period',
                'is_active'
            ]));
            return response()->json([
                'message' => 'Organization Eleave policy updated successfully.',

            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Something went wrong. Please try again later.',
                'details' => $e->getMessage()
            ], 500);
        }
    }

    public function destroy(Request $request, $org_id, $policy_id)
    {
        try {
            $user = Auth::guard('applicationusers')->user();
            $organizationIds = $user->Client->Organization->pluck('organization_id')->toArray();
            if (!in_array($org_id, $organizationIds)) {
                return response()->json([
                    'messages' => 'unauthorized'
                ], 401);
            }
            $request->merge(['organization_id' => $org_id, 'organization_leave_policy_id' => $policy_id]);
            $validator = Validator::make($request->all(), [
                'organization_id' => 'required|integer|exists:organizations,organization_id',
                  'organization_leave_policy_id' => 'required|integer|exists:organization_leave_policies,organization_leave_policy_id',
            ]);
            if ($validator->fails()) {
                return response()->json([
                    'error' => 'Validation failed',
                    'message' => $validator->errors()
                ], 422);
            }
            $policy = OrganizationLeavePolicy::find($policy_id);
            $policy->delete();
            return response()->json([
                'message' => 'Organization Leave Policy Deleted Successfully'
            ], 200);
        } catch (Exception $e) {
            if ($e instanceof \Illuminate\Database\QueryException && $e->errorInfo[1] == 1451) {
                return response()->json([
                    'error' => 'Cannot employment leave policy because it is linked with other records. Please delete dependent records first.'
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
