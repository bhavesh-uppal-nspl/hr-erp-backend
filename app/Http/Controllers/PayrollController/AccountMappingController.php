<?php

namespace App\Http\Controllers\PayrollController;
use App\Http\Controllers\Controller;
use App\Models\PayrollModels\AccountMapping;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class AccountMappingController extends Controller
{
    public function index(Request $request, $org_id)
    {
        try {
            $user = Auth::guard('applicationusers')->user();
            $organizationIds = $user->Client->Organization->pluck('organization_id')->toArray();
            if (!in_array((int) $org_id, $organizationIds)) {
                return response()->json([
                    'message' => 'Unauthenticated'
                ], 401);
            }
            $request->merge(['organization_id' => $org_id]);
            $validator = Validator::make($request->all(), [
                'organization_id' => 'required|integer|exists:organizations,organization_id',
            ]);
            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()], 422);
            }
            $perPage = $request->get('per_page');
            $page = (int) $request->get('page', 1);
            $search = $request->get('search');
            $query = AccountMapping::where('organization_id', $org_id);
            if (!empty($search)) {
                $query->where(function ($q) use ($search) {
                    $q->where('payroll_component_type_name', 'like', '%' . $search . '%')
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
                'message' => 'Payroll account mapping fetched successfully',
                'payroll' => $statusTypes
            ], 200);

        } catch (\Exception $e) {
            \Log::error('Error fetching attendance break types: ' . $e->getMessage());
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
            if (!in_array((int) $org_id, $organizationIds)) {
                return response()->json([
                    'message' => 'Unauthenticated'
                ], 401);
            }
            $request->merge(['organization_id' => $org_id]);
            $validator = Validator::make($request->all(), [
                'organization_id' => 'required|integer|exists:organizations,organization_id',
                'organization_entity_id' => 'nullable|integer|exists:organization_entities,organization_entity_id',
                'organization_payroll_component_id' => 'nullable|integer|exists:organization_payroll_components,organization_payroll_component_id',

                'account_name' => [
                    'required',
                    'string',
                    'max:255',
                    Rule::unique('organization_payroll_account_mappings', 'account_name')
                        ->where(function ($query) use ($request) {
                            return $query->where('organization_id', $request->organization_id);
                        }),
                ],
                'account_code' => [
                    'required',
                    'string',
                    'max:255',
                    Rule::unique('organization_payroll_account_mappings', 'account_code')
                        ->where(function ($query) use ($request) {
                            return $query->where('organization_id', $request->organization_id);
                        }),
                ],
                'posting_type' => 'nullable|in:Debit,Credit',
                'remarks' => 'nullable|string|max:1000',
            ]);

            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()], 422);
            }
            $data = $request->all();
            AccountMapping::create($data);

            return response()->json([
                'message' => 'Payroll Account Mapping added successfully.',
            ], 201);

        } catch (\Exception $e) {
            \Log::error('Error creating payroll loan types : ' . $e->getMessage());
            return response()->json([
                'error' => 'Something went wrong. Please try again later.',
                'details' => $e->getMessage()
            ], 500);
        }
    }

    public function show(Request $request, $org_id, $id)
    {
        try {
            $user = Auth::guard('applicationusers')->user();
            $organizationIds = $user->Client->Organization->pluck('organization_id')->toArray();

            if (!in_array((int) $org_id, $organizationIds)) {
                return response()->json([
                    'message' => 'Unauthenticated'
                ], 401);
            }
            $request->merge([
                'organization_id' => $org_id,
                'organization_payroll_account_mapping_id' => $id
            ]);
            $validator = Validator::make($request->all(), [
                'organization_id' => 'required|integer|exists:organizations,organization_id',
                'organization_payroll_account_mapping_id' => 'required|integer|exists:organization_payroll_account_mappings,organization_payroll_account_mapping_id',
            ]);
            if ($validator->fails()) {
                return response()->json([
                    'errors' => $validator->errors()
                ], 422);
            }
            $payroll = AccountMapping::find($id);
            return response()->json([
                'message' => 'Payroll Account Mapping found',
                'payroll' => $payroll
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Something went wrong. Please try again later.',
                'details' => $e->getMessage()
            ], 500);
        }
    }

    public function update(Request $request, $org_id, $id)
    {
        try {
            $request->merge([
                'organization_id' => $org_id,
                'organization_payroll_adjustment_type_id' => $id
            ]);
            $rules = [
                'organization_id' => 'required|integer|exists:organizations,organization_id',
                'organization_payroll_account_mapping_id' => 'required|integer|exists:organization_payroll_account_mappings,organization_payroll_account_mapping_id',
                'organization_entity_id' => 'nullable|integer|exists:organization_entities,organization_entity_id',
                'organization_payroll_component_id' => 'nullable|integer|exists:organization_payroll_components,organization_payroll_component_id',
                'account_name' => [
                    'sometimes',
                    'string',
                    'max:255',
                    Rule::unique('organization_payroll_account_mappings', '	account_name')
                        ->where(function ($query) use ($org_id) {
                            return $query->where('organization_id', $org_id);
                        })
                        ->ignore($id, 'organization_payroll_account_mapping_id'),
                ],

                'account_code' => [
                    'sometimes',
                    'string',
                    'max:255',
                    Rule::unique('organization_payroll_account_mappings', '	account_code')
                        ->where(function ($query) use ($org_id) {
                            return $query->where('organization_id', $org_id);
                        })
                        ->ignore($id, 'organization_payroll_account_mapping_id'),
                ],


                'posting_type' => 'nullable|in:Debit,Credit',
                'remarks' => 'nullable|string|max:1000',
            ];
            $validator = Validator::make($request->all(), $rules);
            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()], 422);
            }
            $payroll = AccountMapping::find($id);
            $payroll->update($request->only([
                'organization_entity_id',
                'organization_id',
                'organization_payroll_component_id',
                'account_code',
                'account_name',
                'posting_type',
                'remarks',

            ]));
            return response()->json([
                'message' => 'Account Mapping Updated Successfully.',
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Something went wrong. Please try again later.',
                'details' => $e->getMessage()
            ], 500);
        }
    }

    public function destroy(Request $request, $org_id, $id)
    {
        try {
            $request->merge([
                'organization_id' => $org_id,
                'organization_payroll_account_mapping_id' => $id
            ]);
            $validator = Validator::make($request->all(), [
                'organization_id' => 'required|integer|exists:organizations,organization_id',
                'organization_payroll_account_mapping_id' => 'required|integer|exists:organization_payroll_account_mappings,organization_payroll_account_mapping_id',
            ]);
            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()], 422);
            }
            $payroll = AccountMapping::find($id);
            $payroll->delete();
            return response()->json([
                'message' => 'payroll Account Mapping  Deleted Successfully'
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Something went wrong. Please try again later.',
                'details' => $e->getMessage()
            ], 500);
        }
    }


}
