<?php

namespace App\Http\Controllers\PayrollController;
use App\Http\Controllers\Controller;
use App\Models\PayrollModels\LoanTypes;
use App\Models\PayrollModels\PayrollAdjustmentType;
use App\Models\PayrollModels\PayrollJournalEntries;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class PayrollJournalEntriesController extends Controller
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
            $query = PayrollJournalEntries::where('organization_id', $org_id);
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
                'message' => 'Payroll Journal Entries fetched successfully',
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
                'organization_payroll_run_id' => 'nullable|integer|exists:organization_payroll_runs,organization_payroll_run_id',
                'journal_date' => 'nullable|data',
                'employee_id' => 'nullable|integer|exists:organization_payroll_journal_entries,organization_payroll_journal_entry_id',
                'account_name' => [
                    'required',
                    'string',
                    'max:255',
                    Rule::unique('organization_payroll_journal_entries', 'account_name')
                        ->where(function ($query) use ($request) {
                            return $query->where('organization_id', $request->organization_id);
                        }),
                ],
                'account_code' => [
                    'required',
                    'string',
                    'max:255',
                    Rule::unique('organization_payroll_journal_entries', 'account_code')
                        ->where(function ($query) use ($request) {
                            return $query->where('organization_id', $request->organization_id);
                        }),
                ],
                'debit_amount' => 'nullable|numeric',
                'credit_amount' => 'nullable|numeric',
                'reference_type' => 'nullable|in:Component,Advance,Loan,Security,Reimbursement,Adjustment,Adjustment',
                'remarks' => 'nullable|string|max:1000',

                'reference_id' => 'nullable|integer'
            ]);


            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()], 422);
            }
            $data = $request->all();
            PayrollJournalEntries::create($data);

            return response()->json([
                'message' => 'Payroll journal Entries added successfully.',
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
                'organization_payroll_journal_entry_id' => $type_id
            ]);
            $validator = Validator::make($request->all(), [
                'organization_id' => 'required|integer|exists:organizations,organization_id',
                'organization_payroll_adjustment_type_id' => 'required|integer|exists:organization_payroll_adjustment_types,organization_payroll_adjustment_id',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'errors' => $validator->errors()
                ], 422);
            }
            $payroll = PayrollJournalEntries::find($type_id);
            return response()->json([
                'message' => 'Payroll adjsutment type found',
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
                'organization_payroll_adjustment_type_id' => $type_id
            ]);
            $rules = [
                'organization_id' => 'required|integer|exists:organizations,organization_id',
                'organization_payroll_journal_entry_id' => 'required|integer|exists:organization_payroll_journal_entries,organization_payroll_journal_entry_id',
                'organization_entity_id' => 'nullable|integer|exists:organization_entities,organization_entity_id',
                'account_name' => [
                    'sometimes',
                    'string',
                    'max:255',
                    Rule::unique('organization_payroll_journal_entries', '	account_name')
                        ->where(function ($query) use ($org_id) {
                            return $query->where('organization_id', $org_id);
                        })
                        ->ignore($type_id, 'organization_payroll_journal_entry_id'),
                ],

                'account_code' => [
                    'sometimes',
                    'string',
                    'max:255',
                    Rule::unique('organization_payroll_journal_entries', '	account_code')
                        ->where(function ($query) use ($org_id) {
                            return $query->where('organization_id', $org_id);
                        })
                        ->ignore($type_id, 'organization_payroll_journal_entry_id'),
                ],

                'organization_payroll_run_id' => 'nullable|integer|exists:organization_payroll_runs,organization_payroll_run_id',
                'journal_date' => 'nullable|data',
                'employee_id' => 'nullable|integer|exists:organization_payroll_journal_entries,organization_payroll_journal_entry_id',
                'debit_amount' => 'nullable|numeric',
                'credit_amount' => 'nullable|numeric',
                'reference_type' => 'nullable|in:Component,Advance,Loan,Security,Reimbursement,Adjustment,Adjustment',
                'remarks' => 'nullable|string|max:1000',
                'reference_id' => 'nullable|integer'


            ];
            $validator = Validator::make($request->all(), $rules);
            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()], 422);
            }
            $payroll = PayrollJournalEntries::find($type_id);
            $payroll->update($request->only([
                'organization_payroll_run_id',
                'employee_id',
                'journal_date',
                'account_code',
                'account_name',
                'debit_amount',
                'credit_amount',
                'reference_type',
                'reference_id',
                'remarks'

            ]));
            return response()->json([
                'message' => 'Payroll journal Entries Updated Successfully.',
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
                'organization_payroll_journal_entry_id' => $type_id
            ]);
            $validator = Validator::make($request->all(), [
                'organization_id' => 'required|integer|exists:organizations,organization_id',
                'organization_payroll_journal_entry_id' => 'required|integer|exists:organization_payroll_journal_entries,organization_payroll_journal_entry_id',
            ]);
            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()], 422);
            }
            $payroll = PayrollJournalEntries::find($type_id);
            $payroll->delete();
            return response()->json([
                'message' => 'payroll journal entries  Deleted Successfully'
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Something went wrong. Please try again later.',
                'details' => $e->getMessage()
            ], 500);
        }
    }


}
