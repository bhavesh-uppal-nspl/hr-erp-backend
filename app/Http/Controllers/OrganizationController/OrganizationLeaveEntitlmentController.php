<?php

namespace App\Http\Controllers\OrganizationController;
use App\Models\OrganizationModel\OrganizationLeaveEntitlement;
use Illuminate\Support\Facades\Validator;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class OrganizationLeaveEntitlmentController extends Controller
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
                $leave = OrganizationLeaveEntitlement::with('workshift','empstatus','emptype','location','workshifttype','leavetype','department','designation')->where('organization_id', $org_id)->get();

                if ($leave->isEmpty()) {
                    return response()->json([
                        'message' => 'workshift not found.'
                    ], 404);
                }
                $mappedLeave = $leave->map(function ($dep) {
                    return [
                        'location'=>$dep->location->location_name ?? '',
                        'department' => $dep->department->department_name?? '',
                        'designation' => $dep->designation->designation_name ?? '',
                        'employment_type' => $dep->emptype->employment_type_name ?? '',
                        'work_shift' => $dep->workshift->work_shift_name ?? '',
                        'employment_status' => $dep->empstatus->employment_status_name  ?? '',
                        'work_shift_type' => $dep->workshifttype->work_shift_type_name  ?? '',
                        'leave_type' => $dep->leavetype->leave_type_name  ?? '',
                        'entitled_days' => $dep->entitled_days  ?? '',
                        'entitlement_period' => $dep->entitlement_period  ?? '',
                        'carry_forward_days' => $dep->carry_forward_days  ?? '',
                        'max_accumulated_days' => $dep->max_accumulated_days  ?? '',
                        'encashment_allowed' => $dep->encashment_allowed ?? '',
                        'requires_approval' => $dep->requires_approval ?? '',
                        'is_active' => $dep->is_active ?? '',
                    ];
                });
                return response()->json($mappedLeave);
            }

            $query = OrganizationLeaveEntitlement::with('workshift','location','workshifttype','leavetype','department')->where('organization_id', $org_id);
            $per = $request->input('per_page', null); // null means "get all" unless specified
            $search = $request->input('search');
            if ($search) {
                $query->where(function ($q) use ($search) {
                    $q->where('leave_duration_type', 'like', "%{$search}%");
                });
            }
            if ($per) {
                $data = $query->orderBy('created_at', 'desc')->paginate($per);
            } else {
                $data = $query->orderBy('created_at', 'desc')->get();
            }
            return response()->json([
                'message' => 'OK',
                'orgleaveEntitle' => $data,
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
            $request->merge(['organization_id' => $org_id]);
            $validator = Validator::make($request->all(), [
                'organization_id' => 'required|integer|exists:organizations,organization_id',
                'organization_location_id' => 'nullable|integer|exists:organization_locations,organization_location_id',
                'organization_entity_id' => 'nullable|integer|exists:organization_entities,organization_entity_id',
                'organization_department_id' => 'nullable|integer|exists:organization_departments,organization_department_id',
                'organization_designation_id' => 'nullable|integer|exists:organization_designations,organization_designation_id',
                'organization_employment_type_id' => 'nullable|integer|exists:organization_employment_types,organization_employment_type_id',
                'organization_employment_status_id' => 'nullable|integer|exists:organization_employment_statuses,organization_employment_status_id',
                'organization_work_shift_id' => 'nullable|integer|exists:organization_work_shifts,organization_work_shift_id',
                'organization_work_shift_type_id' => 'nullable|integer|exists:organization_work_shift_types,organization_work_shift_type_id',
                'organization_business_registration_type_id' => 'nullable|integer|exists:organization_business_registration_types,organization_business_registration_type_id',
                'organization_business_ownership_type_id' => 'nullable|integer|exists:organization_business_ownership_types,organization_business_ownership_type_id',
                'organization_leave_type_id' => 'nullable|integer|exists:organization_leave_types,organization_leave_type_id',
                'entitled_days' => 'required|numeric|min:0|max:999.99',
                'entitlement_period' => 'nullable|in:annual,monthly,quarterly,custom',
                'carry_forward_days' => 'nullable|numeric|min:0|max:999.99',
                'max_accumulated_days' => 'nullable|numeric|min:0|max:999.99',
                'encashment_allowed' => 'nullable|boolean',
                'requires_approval' => 'nullable|boolean',
                'priority_level' => 'nullable|integer|min:0',
                'is_active' => 'nullable|boolean',
                'created_by' => 'nullable|in:template,user',
            ]);
            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()], 422);
            }
            $data = $request->all();
            $leaveEntitle = OrganizationLeaveEntitlement::create(array_merge($data));
            return response()->json([
                'message' => 'Employee Leave Entitle Added SuccessFully.',
                'leaveEntitle' => $leaveEntitle
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Something went wrong. Please try again later.',
                'details' => $e->getMessage()
            ], 500);
        }
    }
    public function show(Request $request, $org_id, $entitle_id)
    {
        try {
            $request->merge(['organization_id' => $org_id, 'organization_leave_entitlement_id' => $entitle_id]);
            $validator = Validator::make($request->all(), [
                'organization_id' => 'required|integer|exists:organizations,organization_id',
                'organization_leave_entitlement_id' => 'required|integer|exists:organization_leave_entitlements,organization_leave_entitlement_id',
            ]);
            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()], 422);
            }

            $leaveEntitle = OrganizationLeaveEntitlement::find($entitle_id);
           // $leaveEntitle->load('workshift','location','workshifttype','leavetype','department');
            if (!$leaveEntitle) {
                return response()->json(['error' => 'Employee leave Entitle not found.'], 404);
            }

            return response()->json([
                'message' => 'Employee Leave Found',
                'leaveEntitle' => $leaveEntitle
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Something went wrong. Please try again later.',
                'details' => $e->getMessage()
            ], 500);
        }
    }
    public function update(Request $request, $org_id, $entitle_id)
    {
        try {
            $request->merge([
                'organization_id' => $org_id,
                'organization_leave_entitlement_id' => $entitle_id
            ]);
            $rules = [
                'organization_id' => 'required|integer|exists:organizations,organization_id',
                'organization_leave_entitlement_id' => 'required|integer|exists:organization_leave_entitlements,organization_leave_entitlement_id',
                'organization_location_id' => 'nullable|integer|exists:organization_locations,organization_location_id',
                'organization_entity_id' => 'nullable|integer|exists:organization_entities,organization_entity_id',
                'organization_department_id' => 'nullable|integer|exists:organization_departments,organization_department_id',
                'organization_designation_id' => 'nullable|integer|exists:organization_designations,organization_designation_id',
                'organization_employment_type_id' => 'nullable|integer|exists:organization_employment_types,organization_employment_type_id',
                'organization_employment_status_id' => 'nullable|integer|exists:organization_employment_statuses,organization_employment_status_id',
                'organization_work_shift_id' => 'nullable|integer|exists:organization_work_shifts,organization_work_shift_id',
                'organization_work_shift_type_id' => 'nullable|integer|exists:organization_work_shift_types,organization_work_shift_type_id',
                'organization_business_registration_type_id' => 'nullable|integer|exists:organization_business_registration_types,organization_business_registration_type_id',
                'organization_business_ownership_type_id' => 'nullable|integer|exists:organization_business_ownership_types,organization_business_ownership_type_id',
                'organization_leave_type_id' => 'required|integer|exists:organization_leave_types,organization_leave_type_id',
                'entitled_days' => 'nullable|numeric|min:0|max:999.99',
                'entitlement_period' => 'nullable|in:annual,monthly,quarterly,custom',
                'carry_forward_days' => 'nullable|numeric|min:0|max:999.99',
                'max_accumulated_days' => 'nullable|numeric|min:0|max:999.99',
                'encashment_allowed' => 'nullable|boolean',
                'requires_approval' => 'nullable|boolean',
                'priority_level' => 'nullable|integer|min:0',
                'is_active' => 'nullable|boolean',
                'created_by' => 'nullable|in:template,user',
            ];
            $validator = Validator::make($request->all(), $rules);
            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()], 422);
            }
            $leaveEntitle = OrganizationLeaveEntitlement::find($entitle_id);
            $leaveEntitle->update($request->only([
                'organization_location_id',
                'organization_entity_id',
                'organization_department_id',
                'organization_designation_id',
                'organization_employment_type_id',
                'organization_employment_status_id',
                'organization_work_shift_id',
                'organization_work_shift_type_id',
                'organization_business_registration_type_id',
                'organization_business_ownership_type_id',
                'organization_leave_type_id',
                'entitled_days',
                'entitlement_period',
                'carry_forward_days',
                'max_accumulated_days',
                'encashment_allowed',
                'requires_approval',
                'priority_level',
                'is_active',
                'created_by',
            ]));

            return response()->json([
                'message' => 'Oraganization Leave Entitlement  Updated Successfully.',
                'leaveEntitle' => $leaveEntitle
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Something went wrong. Please try again later.',
                'details' => $e->getMessage()
            ], 500);
        }
    }
    public function destroy(Request $request, $org_id, $entitle_id)
    {
        try {
            $request->merge([
                'organization_id' => $org_id,
                'organization_leave_entitlement_id' => $entitle_id
            ]);
            $validator = Validator::make($request->all(), [
                'organization_id' => 'required|integer|exists:organizations,organization_id',
                'organization_leave_entitlement_id' => 'required|integer|exists:organization_leave_entitlements,organization_leave_entitlement_id',
            ]);
            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()], 422);
            }
            $leaveEntitle = OrganizationLeaveEntitlement::find($entitle_id);
            $leaveEntitle->delete();
            return response()->json([
                'message' => 'Employee Leave Entitlement Deleted Successfully'
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Something went wrong. Please try again later.',
                'details' => $e->getMessage()
            ], 500);
        }
    }
}
