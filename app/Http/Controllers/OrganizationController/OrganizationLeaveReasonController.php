<?php

namespace App\Http\Controllers\OrganizationController;
use Illuminate\Support\Facades\Validator;
use App\Http\Controllers\Controller;
use App\Models\OrganizationModel\Organization;
use App\Models\OrganizationModel\OrganizationLeaveReason;
use Exception;
use Illuminate\Http\Request;
use Auth;
use Illuminate\Validation\Rule;

class OrganizationLeaveReasonController extends Controller
{


    public function index(Request $request, $org_id, )
    {
        try {
            $user = Auth::guard('applicationusers')->user();
            $organizationIds = $user->Client->Organization->pluck('organization_id')->toArray();
            if (!in_array($org_id, $organizationIds)) {
                return response()->json([
                    'messages' => 'unauthorized'
                ], 401);

            }
            $request->merge(['organization_id' => $org_id,]);
            $validator = Validator::make($request->all(), [
                'organization_id' => 'required|integer|exists:organizations,organization_id',

            ]);
            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()], 422);
            }


            $query = OrganizationLeaveReason::with('leavereasontype')->where('organization_id', $org_id);
            $per = $request->input('per_page', 200);
            $search = $request->input('search');
            if ($search) {
                $query->where(function ($q) use ($search) {
                    $q->where('leave_reason_name', 'like', "%{$search}%")
                        ->orWhere('description', 'like', "%{$search}%");

                });
            }
            $data = $query->orderBy('created_at', 'desc')->paginate($per);
            return response()->json([
                'message' => 'OK',
                'leavereasons' => $data,

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
                'organization_leave_reason_type_id' => 'required|integer|exists:organization_leave_reason_types,organization_leave_reason_type_id',
                'leave_reason_name' => [
                    'required',
                    'string',
                    'max:100',
                    Rule::unique('organization_leave_reasons', 'leave_reason_name')
                        ->where(function ($query) use ($org_id) {
                            return $query->where('organization_id', $org_id);
                        })
                ],
                'description' => ['nullable', 'string', 'max:255'],
            ]);
            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()], 422);
            }
            $data = $request->all();
            $leavereason = OrganizationLeaveReason::create($data);
            return response()->json([
                'message' => 'Organization leave reason added successfullly.',
                'leavereason' => $leavereason
            ], 201);
        } catch (\Exception $e) {
            // Handle any other errors and return a response
            return response()->json([
                'error' => 'Something went wrong. Please try again later.',
                'details' => $e->getMessage()
            ], 500);
        }
    }


    public function show(Request $request, $org_id, $leave_reason_id)
    {
        try {
            $user = Auth::guard('applicationusers')->user();
            $organizationIds = $user->Client->Organization->pluck('organization_id')->toArray();
            if (!in_array($org_id, $organizationIds)) {
                return response()->json([
                    'messages' => 'unauthorized'
                ], 401);

            }
            $request->merge(['organization_id' => $org_id, 'organization_leave_reason_id' => $leave_reason_id]);
            $validator = Validator::make($request->all(), [
                'organization_id' => 'required|integer|exists:organizations,organization_id',
                'organization_leave_reason_id' => 'required|integer|exists:organization_leave_reasons,organization_leave_reason_id',
            ]);
            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()], 422);
            }
            $leavereason = OrganizationLeaveReason::find($leave_reason_id);
            $leavereason->load('leavereasontype');

            return response()->json([
                'message' => "Orgaization leave reason  found",
                'leavereason' => $leavereason
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Something went wrong. Please try again later.',
                'details' => $e->getMessage()
            ], 500);
        }
    }


    public function update(Request $request, $org_id, $leave_reason_id)
    {
        try {
            $user = Auth::guard('applicationusers')->user();
            $organizationIds = $user->Client->Organization->pluck('organization_id')->toArray();
            if (!in_array($org_id, $organizationIds)) {
                return response()->json([
                    'messages' => 'unauthorized'
                ], 401);

            }
            $request->merge(['organization_id' => $org_id, 'organization_leave_reason_id' => $leave_reason_id]);
            $rules = [
                'organization_id' => 'required|integer|exists:organizations,organization_id',
                'organization_leave_reason_type_id' => 'nullable|required|integer|exists:organization_leave_reason_types,organization_leave_reason_type_id',
                'organization_leave_reason_id' => 'required|integer|exists:organization_leave_reasons,organization_leave_reason_id',
                'leave_reason_name' => [
                    'sometimes',
                    'string',
                    'max:255',
                    Rule::unique('organization_leave_reasons')
                        ->where(fn($query) => $query->where('organization_id', $org_id))
                        ->ignore($leave_reason_id, 'organization_leave_reason_id'),
                ],

                'description' => ['nullable', 'sometimes', 'string', 'max:255'],
            ];
            $validator = Validator::make($request->all(), $rules);
            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()], 422);
            }
            $leavereason = OrganizationLeaveReason::find($leave_reason_id);
            $leavereason->update($request->only([
                'leave_reason_name',
                'description',
                'organization_leave_reason_type_id'
            ]));
            return response()->json([
                'message' => 'Organization leave Reason updated successfully.',
                'leavereason' => $leavereason
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Something went wrong. Please try again later.',
                'details' => $e->getMessage()
            ], 500);
        }
    }

    public function destroy(Request $request, $org_id, $leave_reason_id)
    {
        try {
            $user = Auth::guard('applicationusers')->user();
            $organizationIds = $user->Client->Organization->pluck('organization_id')->toArray();
            if (!in_array($org_id, $organizationIds)) {
                return response()->json([
                    'messages' => 'unauthorized'
                ], 401);

            }
            $request->merge(['organization_id' => $org_id, 'organization_leave_reason_id' => $leave_reason_id]);
            $validator = Validator::make($request->all(), [
                'organization_id' => 'required|integer|exists:organizations,organization_id',

                'organization_leave_reason_id' => 'required|integer|exists:organization_leave_reasons,organization_leave_reason_id',
            ]);
            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()], 422);
            }
            $leavereason = OrganizationLeaveReason::find($leave_reason_id);
            $leavereason->delete();
            return response()->json([
                'message' => 'Organization leave reason Deleted Successfully'
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



    public function getByType(Request $request, $org_id, $type_id)
    {
        try {
            $validator = Validator::make([
                'organization_id' => $org_id,
                'organization_leave_reason_type_id' => $type_id,
            ], [
                'organization_id' => 'required|integer|exists:organizations,organization_id',
                'organization_leave_reason_type_id' => 'required|integer|exists:organization_leave_reason_types,organization_leave_reason_type_id',
            ]);

            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()], 422);
            }

            // Just fetch all leave reasons for this type and org
            $data = OrganizationLeaveReason::where('organization_id', $org_id)
                ->where('organization_leave_reason_type_id', $type_id)
                ->orderBy('created_at', 'desc')
                ->get();

            return response()->json([
                'message' => 'OK',
                'leavereasons' => $data,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Something went wrong. Please try again later.',
                'details' => $e->getMessage()
            ], 500);
        }
    }


}
