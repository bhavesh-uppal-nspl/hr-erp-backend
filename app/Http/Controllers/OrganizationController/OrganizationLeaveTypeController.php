<?php

namespace App\Http\Controllers\OrganizationController;

use Illuminate\Support\Facades\Validator;
use App\Http\Controllers\Controller;
use App\Models\OrganizationModel\Organization;
use App\Models\OrganizationModel\OrganizationLeaveType;
use Exception;
use Illuminate\Http\Request;
use Auth;
use Illuminate\Validation\Rule;

class OrganizationLeaveTypeController extends Controller
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


            $query = OrganizationLeaveType::where('organization_id', $org_id);
            $per = $request->input('per_page', 10);
            $search = $request->input('search');
            if ($search) {
                $query->where(function ($q) use ($search) {
                    $q->where('leave_type_name', 'like', "%{$search}%")
                        ->orWhere('leave_type_code ', 'like', "%{$search}%")
                        ->orWhere('description', 'like', "%{$search}%");

                });
            }
            $data = $query->orderBy('created_at', 'desc')->paginate($per);
            return response()->json([
                'message' => 'OK',
                'leavetypes' => $data,

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
            $organizationIds =
                $user->Client->Organization->pluck('organization_id')->toArray();
            if (!in_array($org_id, $organizationIds)) {
                return response()->json([
                    'messages' => 'unauthorized'
                ], 401);

            }
            $request->merge(['organization_id' => $org_id]);
            $validator = Validator::make($request->all(), [
                'organization_id' =>
                    'required|integer|exists:organizations,organization_id',

                'leave_type_name' => [
                    'sometimes',
                    'nullable',
                    'string',
                    'max:255',
                    Rule::unique('organization_leave_types', 'leave_type_name')
                        ->where(function ($query) use ($org_id) {
                            return $query->where('organization_id', $org_id);
                        }),
                ],

                'business_unit_short_name' => [
                    'sometimes',
                    'nullable',
                    'string',
                    'max:100',
                    Rule::unique(
                        'organization_leave_types',
                        'business_unit_short_name'
                    )
                        ->where(function ($query) use ($org_id) {
                            return $query->where('organization_id', $org_id);
                        }),
                ],

                 'compensation_code'=> ['nullable','string','max:4'],
                'description' => ['nullable', 'string', 'max:255'],
                'max_days_allowed' => ['nullable', 'integer', 'min:1'],
                'carry_forward' => ['nullable', 'boolean'],
                'requires_approval' => ['nullable', 'boolean'],
                'is_active' => ['nullable', 'boolean'],
               'leave_compensation_type'=> ['nullable', 'in:paid,unpaid,any']
            ]);
            if ($validator->fails()) {
                return response()->json([
                    'errors' =>
                        $validator->errors()
                ], 422);
            }
            $data = $request->all();
            $leavetypes = OrganizationLeaveType::create($data);
            return response()->json([
                'message' => 'Organization Leave Type Added SuccessFully.',
                'leavetypes' => $leavetypes
            ], 201);

        } catch (\Exception $e) {
            // Handle any other errors and return a response
            return response()->json([
                'error' => 'Something went wrong. Please try again later.',
                'details' => $e->getMessage()
            ], 500);
        }
    }

    public function show(Request $request, $org_id, $leave_type_id)
    {
        try {
            $user = Auth::guard('applicationusers')->user();
            $organizationIds = $user->Client->Organization->pluck('organization_id')->toArray();
            if (!in_array($org_id, $organizationIds)) {
                return response()->json([
                    'messages' => 'unauthorized'
                ], 401);

            }
            $request->merge(['organization_id' => $org_id, 'organization_leave_type_id' => $leave_type_id]);
            $validator = Validator::make($request->all(), [
                'organization_id' => 'required|integer|exists:organizations,organization_id',
                'organization_leave_type_id' => 'required|integer|exists:organization_leave_types,organization_leave_type_id',
            ]);
            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()], 422);
            }
            $leavetypes = OrganizationLeaveType::find($leave_type_id);
            return response()->json([
                'message' => 'Organization Leave Type Found',
                'leavetypes' => $leavetypes
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Something went wrong. Please try again later.',
                'details' => $e->getMessage()
            ], 500);
        }
    }


    public function update(Request $request, $org_id, $leave_type_id)
    {
        try {
            $user = Auth::guard('applicationusers')->user();
            $organizationIds =
                $user->Client->Organization->pluck('organization_id')->toArray();
            if (!in_array($org_id, $organizationIds)) {
                return response()->json([
                    'messages' => 'unauthorized'
                ], 401);

            }
            $request->merge([
                'organization_id' => $org_id,
                'organization_leave_type_id' => $leave_type_id
            ]);
            $rules = [
                'organization_id' =>
                    'required|integer|exists:organizations,organization_id',
                'organization_leave_type_id' =>
                    'required|integer|exists:organization_leave_types,organization_leave_type_id',
                'leave_type_name' => [
                    'sometimes',
                    'string',
                    'max:255',
                    Rule::unique('organization_leave_types', 'leave_type_name')
                        ->ignore($leave_type_id, 'organization_leave_type_id')
                        ->where(function ($query) use ($request) {
                            return $query->where(
                                'organization_id',
                                $request->organization_id
                            );
                        }),
                ],

                 'leave_compensation_type'=> ['nullable', 'in:paid,unpaid,any'],

                'leave_type_code' => [
                    'sometimes',
                    'string',
                    'max:100',
                    Rule::unique('organization_leave_types', 'leave_type_code')
                        ->ignore($leave_type_id, 'organization_leave_type_id')
                        ->where(function ($query) use ($request) {
                            return $query->where(
                                'organization_id',
                                $request->organization_id
                            );
                        }),
                ],
                'compensation_code'=> ['nullable','string','max:4'],
                'description' => ['sometimes', 'nullable', 'string', 'max:255'],
                'max_days_allowed' => [
                    'sometimes',
                    'nullable',
                    'integer',
                    'min:0'
                ],
                'carry_forward' => ['sometimes', 'nullable', 'boolean'],
                'requires_approval' => ['sometimes', 'nullable', 'boolean'],
                'is_active' => ['nullable', 'sometimes', 'boolean'],

            ];
            $validator = Validator::make($request->all(), $rules);
            if ($validator->fails()) {
                return response()->json([
                    'errors' =>
                        $validator->errors()
                ], 422);
            }
            $leavetype = OrganizationLeaveType::find($leave_type_id);
            $leavetype->update($request->only([
                'leave_type_name',
                'description',
                'max_days_allowed',
                'carry_forward',
                'requires_approval',
                'is_active',
                'leave_type_code',
                'leave_compensation_type',
                'compensation_code'
            ]));

            return response()->json([
                'message' => 'Organization Leave Type updated successfully.',
                'leavetype' => $leavetype
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Something went wrong. Please try again later.',
                'details' => $e->getMessage()
            ], 500);
        }
    }


    public function destroy(Request $request, $org_id, $leave_type_id)
    {
        try {
            $user = Auth::guard('applicationusers')->user();
            $organizationIds = $user->Client->Organization->pluck('organization_id')->toArray();
            if (!in_array($org_id, $organizationIds)) {
                return response()->json([
                    'messages' => 'unauthorized'
                ], 401);

            }
            $request->merge(['organization_id' => $org_id, 'organization_leave_type_id' => $leave_type_id]);
            $validator = Validator::make($request->all(), [
                'organization_id' => 'required|integer|exists:organizations,organization_id',
                'organization_leave_type_id' => 'required|integer|exists:organization_leave_types,organization_leave_type_id',
            ]);
            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()], 422);
            }
            $leavetypes = OrganizationLeaveType::find($leave_type_id);
            $leavetypes->delete();
            return response()->json([
                'message' => 'Organization Leave Type Deleted Successfully'
            ], 200); // or just remove 200 — it's the default


        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Something went wrong. Please try again later.',
                'details' => $e->getMessage()
            ], 500);
        }
    }
}
