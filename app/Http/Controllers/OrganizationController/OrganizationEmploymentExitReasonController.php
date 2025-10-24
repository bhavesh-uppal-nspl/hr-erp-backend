<?php

namespace App\Http\Controllers\OrganizationController;

use Illuminate\Support\Facades\Validator;
use App\Http\Controllers\Controller;
use App\Models\OrganizationModel\Organization;
use App\Models\OrganizationModel\OrganizationEmployementExistReason;
use Exception;
use Illuminate\Http\Request;
use Auth;
use Illuminate\Validation\Rule;

class OrganizationEmploymentExitReasonController extends Controller
{


    public function index1(Request $request, $org_id, $exitreasonType_id)
    {
        try {
            $user = Auth::guard('applicationusers')->user();
            $organizationIds = $user->Client->Organization->pluck('organization_id')->toArray();
            if (!in_array($org_id, $organizationIds)) {
                return response()->json([
                    'messages' => 'unauthorized'
                ], 401);

            }
            $request->merge(['organization_id' => $org_id, 'organization_employment_exit_reason_type_id' => $exitreasonType_id]);
            $validator = Validator::make($request->all(), [
                'organization_id' => 'required|integer|exists:organizations,organization_id',
                'organization_employment_exit_reason_type_id' => 'required|integer|exists:organization_employment_exit_reason_types,organization_employment_exit_reason_type_id'
            ]);
            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()], 422);
            }

            $query = OrganizationEmployementExistReason::where('organization_id', $org_id)
                ->where('organization_employment_exit_reason_type_id', $exitreasonType_id);


            $data = $query->orderBy('created_at', 'desc')->get();
            return response()->json([
                'message' => 'OK',
                'exitreason' => $data,

            ]);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Something went wrong. Please try again later.',
                'details' => $e->getMessage()
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

            $query = OrganizationEmployementExistReason::with('ExitReasonType')->where('organization_id', $org_id);
            $per = $request->input('per_page', 45);
            $search = $request->input('search');
            if ($search) {
                $query->where(function ($q) use ($search) {
                    $q->where('employment_exit_reason_name', 'like', "%{$search}%")
                        ->orWhere('description', 'like', "%{$search}%");

                });
            }
            $data = $query->orderBy('created_at', 'desc')->paginate($per);
            return response()->json([
                'message' => 'OK',
                'exitreason' => $data,

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
                'employment_exit_reason_name' => 'required|string|max:255|unique:organization_employment_exit_reasons,employment_exit_reason_name',
                'organization_employment_exit_reason_type_id' => 'required|integer|exists:organization_employment_exit_reason_types,organization_employment_exit_reason_type_id',
                'description' => 'required|string|max:2005',
            ]);
            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()], 422);
            }
            $data = $request->all();
            $exitreason = OrganizationEmployementExistReason::create($data);
            return response()->json([
                'message' => 'Organization employment Exit Reason  Added SuccessFully.',
                'exitreason' => $exitreason
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
    public function show(Request $request, $org_id, $exit_reason_id)
    {
        try {
            $user = Auth::guard('applicationusers')->user();
            $organizationIds = $user->Client->Organization->pluck('organization_id')->toArray();
            if (!in_array($org_id, $organizationIds)) {
                return response()->json([
                    'messages' => 'unauthorized'
                ], 401);

            }
            $request->merge(['organization_id' => $org_id, 'organization_employment_exit_reason_id' => $exit_reason_id]);
            $validator = Validator::make($request->all(), [
                'organization_id' => 'required|integer|exists:organizations,organization_id',
                'organization_employment_exit_reason_id' => 'required|integer|exists:organization_employment_exit_reasons,organization_employment_exit_reason_id',
            ]);
            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()], 422);
            }
            $exitreason = OrganizationEmployementExistReason::find($exit_reason_id);
           $exitreason->load('ExitReasonType');
            return response()->json([
                'message' => "Employment Exit Reason  Found",
                'exitreason' => $exitreason
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Something went wrong. Please try again later.',
                'details' => $e->getMessage()
            ], 500);
        }
    }


    // update the orgaization 
    public function update(Request $request, $org_id, $exit_reason_id)
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
                'organization_employment_exit_reason_id' => $exit_reason_id
            ]);
            $rules = [
                'organization_id' => 'required|integer|exists:organizations,organization_id',
                'organization_employment_exit_reason_id' => 'required|integer|exists:organization_employment_exit_reasons,organization_employment_exit_reason_id',
                   'organization_employment_exit_reason_type_id' => 'nullable|integer|exists:organization_employment_exit_reason_types,organization_employment_exit_reason_type_id',
                'employment_exit_reason_name' => [
                    'sometimes',
                    'string',
                    'max:255',
                    Rule::unique('organization_employment_exit_reasons', 'employment_exit_reason_name')->ignore($exit_reason_id, 'organization_employment_exit_reason_id')
                ],

                'description' => 'sometimes|string|max:2005',
            ];
            $validator = Validator::make($request->all(), $rules);
            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()], 422);
            }
            $exitreason = OrganizationEmployementExistReason::find($exit_reason_id);
            $exitreason->update($request->only([
                'employment_exit_reason_name',
                'description',
                'organization_employment_exit_reason_type_id'
            ]));
            return response()->json([
                'message' => 'Employment Exit Reason Updated Successfully.',
                'exitreason' => $exitreason
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Something went wrong. Please try again later.',
                'details' => $e->getMessage()
            ], 500);
        }
    }


    // delete the orgaization  
    public function destroy(Request $request, $org_id, $exit_reason_id)
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
                'organization_employment_exit_reason_id' => $exit_reason_id
            ]);
            $rules = [
                'organization_id' => 'required|integer|exists:organizations,organization_id',
                'organization_employment_exit_reason_id' => 'required|integer|exists:organization_employment_exit_reasons,organization_employment_exit_reason_id',
            ];
            $validator = Validator::make($request->all(), $rules);
            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()], 422);
            }
            $exitreason = OrganizationEmployementExistReason::find($exit_reason_id);
            $exitreason->delete();
            return response()->json([
                'message' => 'Organization Employment Exit Reason  Deleted Successfully'
            ], 200); // or just remove 200 — it's the default
        } catch (Exception $e) {
            if ($e instanceof \Illuminate\Database\QueryException && $e->errorInfo[1] == 1451) {
                // Foreign key constraint violation
                return response()->json([
                    'error' => 'Cannot delete Business Unit type because it is linked with other records. Please delete dependent records first.'
                ], 409); // 409 Conflict
            }
            return response()->json([
                'error' => 'Failed to delete ownership type.',
                'exception' => $e->getMessage() // Optional: remove in production
            ], 500);
        }
    }
}
