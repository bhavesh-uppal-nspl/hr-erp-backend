<?php

namespace App\Http\Controllers\OrganizationController;
use App\Models\OrganizationModel\OrganizationLeaveReasonType;
use Illuminate\Support\Facades\Validator;
use App\Http\Controllers\Controller;
use App\Models\OrganizationModel\Organization;
use App\Models\OrganizationModel\OrganizationLeaveReason;
use Exception;
use Illuminate\Http\Request;
use Auth;
use Illuminate\Validation\Rule;

class OrganizationLeaveReasonTypeController extends Controller
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

        $query = OrganizationLeaveReasonType::with('leavetype')->where('organization_id', $org_id);

        $search = $request->input('search');
        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('leave_reason_name', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }

        if ($request->has('per_page')) {
            $per = (int) $request->input('per_page', 10);
            $data = $query->orderBy('created_at', 'desc')->paginate($per);
        } else {
            $data = $query->orderBy('created_at', 'desc')->get();
        }

        return response()->json([
            'message' => 'OK',
            'leavereasontype' => $data,
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
                'organization_leave_type_id' => 'required|integer|exists:organization_leave_types,organization_leave_type_id',
                'leave_reason_type_name' => [
                    'required',
                    'string',
                    'max:100',
                    Rule::unique('organization_leave_reason_types', 'leave_reason_type_name')
                        ->where(function ($query) use ($org_id) {
                            return $query->where('organization_id', $org_id);
                        }),
                ],
                'description' => ['nullable', 'string', 'max:255'],
            ]);
            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()], 422);
            }
            $data = $request->all();
            $leavereasontype = OrganizationLeaveReasonType::create($data);
            return response()->json([
                'message' => 'Organization leave reason type added successfullly.',
                'leavereasontype' => $leavereasontype
            ], 201);
        } catch (\Exception $e) {
            // Handle any other errors and return a response
            return response()->json([
                'error' => 'Something went wrong. Please try again later.',
                'details' => $e->getMessage()
            ], 500);
        }
    }


    public function show(Request $request, $org_id, $leave_reason_type_id)
    {
        try {
            $user = Auth::guard('applicationusers')->user();
            $organizationIds = $user->Client->Organization->pluck('organization_id')->toArray();
            if (!in_array($org_id, $organizationIds)) {
                return response()->json([
                    'messages' => 'unauthorized'
                ], 401);

            }
            $request->merge(['organization_id' => $org_id, 'organization_leave_reason_type_id' => $leave_reason_type_id]);
            $validator = Validator::make($request->all(), [
                'organization_id' => 'required|integer|exists:organizations,organization_id',
                'organization_leave_reason_type_id' => 'required|integer|exists:organization_leave_reason_types,organization_leave_reason_type_id',
            ]);
            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()], 422);
            }
            $leavereasontype = OrganizationLeaveReasonType::find($leave_reason_type_id);
             $leavereasontype->load('leavetype');

            return response()->json([
                'message' => "Orgaization leave reason type  found",
                'leavereasontype' => $leavereasontype
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Something went wrong. Please try again later.',
                'details' => $e->getMessage()
            ], 500);
        }
    }


    public function update(Request $request, $org_id, $leave_reason_type_id)
    {
        try {
            $user = Auth::guard('applicationusers')->user();
            $organizationIds = $user->Client->Organization->pluck('organization_id')->toArray();
            if (!in_array($org_id, $organizationIds)) {
                return response()->json([
                    'messages' => 'unauthorized'
                ], 401);

            }
            $request->merge(['organization_id' => $org_id, 'organization_leave_reason_type_id' => $leave_reason_type_id]);
            $rules = [
                'organization_id' => 'required|integer|exists:organizations,organization_id',
                 'organization_leave_reason_type_id' => 'required|integer|exists:organization_leave_reason_types,organization_leave_reason_type_id',
                'organization_leave_type_id' => 'nullable|required|integer|exists:organization_leave_types,organization_leave_type_id',
                'leave_reason_type_name' => [
                    'sometimes',
                    'string',
                    'max:255',
                    Rule::unique('organization_leave_reason_types', 'leave_reason_type_name')
                        ->where(function ($query) use ($org_id, $request) {
                            return $query
                                ->where('organization_id', $org_id)
                                ->where('organization_leave_type_id', $request->organization_leave_type_id);
                        })
                        ->ignore($leave_reason_type_id, 'organization_leave_reason_type_id')
                ],

                'description' => ['nullable', 'sometimes', 'string', 'max:255'],
            ];
            $validator = Validator::make($request->all(), $rules);
            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()], 422);
            }
            $leavereason = OrganizationLeaveReasonType::find($leave_reason_type_id);
            $leavereason->update($request->only([
                'leave_reason_type_name',
                'description',
                'organization_leave_type_id'
            ]));
            return response()->json([
                'message' => 'Organization leave Reason type updated successfully.',
                'leavereason' => $leavereason
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Something went wrong. Please try again later.',
                'details' => $e->getMessage()
            ], 500);
        }
    }

    public function destroy(Request $request, $org_id, $leave_reason_type_id)
    {
        try {
            $user = Auth::guard('applicationusers')->user();
            $organizationIds = $user->Client->Organization->pluck('organization_id')->toArray();
            if (!in_array($org_id, $organizationIds)) {
                return response()->json([
                    'messages' => 'unauthorized'
                ], 401);

            }
            $request->merge(['organization_id' => $org_id, 'organization_leave_reason_type_id' => $leave_reason_type_id]);
            $validator = Validator::make($request->all(), [
                'organization_id' => 'required|integer|exists:organizations,organization_id',

                'organization_leave_reason_type_id' => 'required|integer|exists:organization_leave_reason_types,organization_leave_reason_type_id',
            ]);
            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()], 422);
            }
            $leavereason = OrganizationLeaveReasonType::find($leave_reason_type_id);
            $leavereason->delete();
            return response()->json([
                'message' => 'Organization leave reason type Deleted Successfully'
            ], 200);


        } catch (Exception $e) {
            if ($e instanceof \Illuminate\Database\QueryException && $e->errorInfo[1] == 1451) {
                // Foreign key constraint violation
                return response()->json([
                    'error' => 'Cannot delete Business Unit type because it is linked with other records. Please delete dependent records first.'
                ], 409); // 409 Conflict
            }

            // For other exceptions
            return response()->json([
                'error' => 'Failed to delete Address Type type.',
                'exception' => $e->getMessage() // Optional: remove in production
            ], 500);
        }
    }

}
