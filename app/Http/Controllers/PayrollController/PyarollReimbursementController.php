<?php

namespace App\Http\Controllers\PayrollController;
use App\Http\Controllers\Controller;
use App\Models\PayrollModels\PayrollReimbursement;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Auth;
use Illuminate\Support\Facades\Validator;


class PyarollReimbursementController extends Controller
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
            $query = PayrollReimbursement::with('PayrollReimbursementType', 'Employee')->where('organization_id', $org_id);
            if (!empty($search)) {
                $query->where(function ($q) use ($search) {
                    $q->where('pay_frequency', 'like', '%' . $search . '%')
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
                'message' => 'Payroll Reimbursement fetched successfully',
                'payrollSlab' => $statusTypes
            ], 200);

        } catch (\Exception $e) {
            \Log::error('Error fetching payroll reimbursement: ' . $e->getMessage());
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
                'employee_id' => 'nullable|integer|exists:employees,employee_id',
                'organization_payroll_reimbursement_type_id' => 'required|integer|exists:organization_payroll_reimbursement_types,organization_payroll_reimbursement_type_id',
                'claim_amount' => 'required|numeric|min:0',
                'approved_amount' => 'required|numeric|min:0',
                'claim_date' => 'nullable|date',
                'status' => 'nullable|in:Requested,Approved,Rejected,Paid,Cancelled',
                'remarks' => 'nullable|string|max:255',
            ]);
            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()], 422);
            }
            $data = $request->all();
            PayrollReimbursement::create($data);
            return response()->json([
                'message' => 'Payroll Reimbursement added successfully.',
            ], 201);
        } catch (\Exception $e) {
            \Log::error('Error creating payroll Reimbursement: ' . $e->getMessage());
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
                'organization_payroll_reimbursement_id' => $id
            ]);
            $validator = Validator::make($request->all(), [
                'organization_id' => 'required|integer|exists:organizations,organization_id',
                'organization_payroll_reimbursement_id' => 'required|integer|exists:organization_payroll_reimbursements,organization_payroll_reimbursement_id',
            ]);
            if ($validator->fails()) {
                return response()->json([
                    'errors' => $validator->errors()
                ], 422);
            }
            $slab = PayrollReimbursement::find($id);
            $slab->load('ReimbursementType', 'Employee');
            return response()->json([
                'message' => 'Payroll slab',
                'payroll' => $slab
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
                'organization_payroll_reimbursement_id' => $id
            ]);
            $rules = [
                'organization_id' => 'required|integer|exists:organizations,organization_id',
                'organization_payroll_reimbursement_id' => 'required|integer|exists:organization_payroll_reimbursements,organization_payroll_reimbursement_id',
                'employee_id' => 'nullable|integer|exists:employees,employee_id',
                'organization_payroll_reimbursement_type_id' => 'required|integer|exists:organization_payroll_reimbursement_types,organization_payroll_reimbursement_type_id',
                'claim_amount' => 'required|numeric|min:0',
                'approved_amount' => 'required|numeric|min:0',
                'claim_date' => 'nullable|date',
                'status' => 'nullable|in:Requested,Approved,Rejected,Paid,Cancelled',
                'remarks' => 'nullable|string|max:255',
            ];
            $validator = Validator::make($request->all(), $rules);
            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()], 422);
            }
            $payrollcycle = PayrollReimbursement::find($id);
            $payrollcycle->update($request->only([
                'organization_entity_id',
                'organization_id',
                'employee_id',
                'organization_payroll_reimbursement_type_id',
                'claim_date',
                'claim_amount',
                'approved_amount',
                'status',
                'remarks'
            ]));
            return response()->json([
                'message' => 'Payroll reimbursemnt Updated Successfully.',
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
                'organization_payroll_reimbursement_id' => $id
            ]);
            $validator = Validator::make($request->all(), [
                'organization_id' => 'required|integer|exists:organizations,organization_id',
                'organization_payroll_reimbursement_id' => 'required|integer|exists:organization_payroll_reimbursements,organization_payroll_reimbursement_id',
            ]);
            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()], 422);
            }
            $payrollcycle = PayrollReimbursement::find($id);
            $payrollcycle->delete();
            return response()->json([
                'message' => 'Payroll reimbursement Deleted Successfully'
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Something went wrong. Please try again later.',
                'details' => $e->getMessage()
            ], 500);
        }
    }

}
