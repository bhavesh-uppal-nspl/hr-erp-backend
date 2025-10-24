<?php

namespace App\Http\Controllers\PayrollController;
use App\Http\Controllers\Controller;
use App\Models\PayrollModels\LoanTypes;
use App\Models\PayrollModels\PayrollAdvances;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class LoanTypeController extends Controller
{
    public function index(Request $request, $org_id)
    {
        try {
            // Get the authenticated user
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
            $query = LoanTypes::where('organization_id', $org_id);
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
            // Return success response
            return response()->json([
                'message' => 'Payroll Loan Types fetched successfully',
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
                'loan_type_name' => [
                    'required',
                    'string',
                    'max:255',
                    Rule::unique('organization_payroll_loan_types', 'loan_type_name')
                        ->where(function ($query) use ($request) {
                            return $query->where('organization_id', $request->organization_id);
                        }),
                ],
                'max_amount' => 'nullable|integer',
                'max_installments' => 'nullable|numeric',
                'interest_rate' => 'nullable|numeric',
                'description' => 'nullable|string|max:1000',
            ]);

            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()], 422);
            }
            $data = $request->all();
            LoanTypes::create($data);

            return response()->json([
                'message' => 'Payroll LoanTypes added successfully.',
            ], 201);

        } catch (\Exception $e) {
            \Log::error('Error creating payroll loan types : ' . $e->getMessage());
            return response()->json([
                'error' => 'Something went wrong. Please try again later.',
                'details' => $e->getMessage()
            ], 500);
        }
    }

    public function show(Request $request, $org_id, $type_id)
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
                'organization_payroll_advance_id' => $type_id
            ]);
            $validator = Validator::make($request->all(), [
                'organization_id' => 'required|integer|exists:organizations,organization_id',
                'organization_payroll_loan_type_id' => 'required|integer|exists:organization_payroll_loan_types,organization_payroll_loan_type_id',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'errors' => $validator->errors()
                ], 422);
            }
            $payroll = LoanTypes::find($type_id);
            return response()->json([
                'message' => 'Payroll type found',
                'payroll' => $payroll
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Something went wrong. Please try again later.',
                'details' => $e->getMessage()
            ], 500);
        }
    }

    public function update(Request $request, $org_id, $type_id)
    {
        try {
            $request->merge([
                'organization_id' => $org_id,
                'organization_payroll_loan_type_id' => $type_id
            ]);
            $rules = [
                'organization_id' => 'required|integer|exists:organizations,organization_id',
                'organization_payroll_loan_type_id' => 'required|integer|exists:organization_payroll_loan_types,organization_payroll_loan_type_id',
                'organization_entity_id' => 'nullable|integer|exists:organization_entities,organization_entity_id',
                'loan_type_name' => [
                    'sometimes',
                    'string',
                    'max:255',
                    Rule::unique('organization_payroll_loan_types', 'loan_type_name')->ignore($type_id, 'organization_payroll_loan_type_id')
                ],
                'max_amount' => 'nullable|integer',
                'max_installments' => 'nullable|numeric',
                'interest_rate' => 'nullable|numeric',
                'description' => 'nullable|string|max:1000',
            ];
            $validator = Validator::make($request->all(), $rules);
            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()], 422);
            }
            $payroll = LoanTypes::find($type_id);
            $payroll->update($request->only([
                'organization_entity_id',
                'organization_id',
                'loan_type_name',
                'description',
                'max_amount',
                'max_installments',
                'daily_salary_amount',
                'interest_rate',
                'is_active',
            ]));
            return response()->json([
                'message' => 'Payroll Loan Type Updated Successfully.',
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Something went wrong. Please try again later.',
                'details' => $e->getMessage()
            ], 500);
        }
    }

    public function destroy(Request $request, $org_id, $type_id)
    {
        try {
            $request->merge([
                'organization_id' => $org_id,
                'organization_payroll_advance_id' => $type_id
            ]);
            $validator = Validator::make($request->all(), [
               'organization_id' => 'required|integer|exists:organizations,organization_id',
                'organization_payroll_loan_type_id' => 'required|integer|exists:organization_payroll_loan_types,organization_payroll_loan_type_id',
                
            ]);
            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()], 422);
            }
            $payroll = PayrollAdvances::find($type_id);
            $payroll->delete();
            return response()->json([
                'message' => 'payroll Loan Types Deleted Successfully'
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Something went wrong. Please try again later.',
                'details' => $e->getMessage()
            ], 500);
        }
    }


}
