<?php

namespace App\Http\Controllers\PayrollController;
use App\Http\Controllers\Controller;
use App\Models\PayrollModels\EmployeePaySlip;
use DB;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Auth;
use Illuminate\Support\Facades\Validator;

class EmployeePaySlipController extends Controller
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
            $query = EmployeePaySlip::with('Employee', 'PayrollRunEmployee')->where('organization_id', $org_id);
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
                'message' => 'Payroll run employee fetched successfully',
                'payroll' => $statusTypes
            ], 200);
        } catch (\Exception $e) {
            \Log::error('Error fetching payroll run employee: ' . $e->getMessage());
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
                return response()->json(['message' => 'Unauthenticated'], 401);
            }

            $request->merge(['organization_id' => $org_id]);

            $validator = Validator::make($request->all(), [
                'organization_id' => 'required|integer|exists:organizations,organization_id',
                'organization_entity_id' => 'nullable|integer|exists:organization_entities,organization_entity_id',
                'payroll_run_employee_id' => 'nullable|integer|exists:payroll_run_employees,payroll_run_employee_id',
                'employee_id' => 'required|integer|exists:employees,employee_id',
                'period_start_date' => 'required|date',
                'period_end_date' => 'required|date',
                'net_pay' => 'nullable|numeric',
                'gross_pay' => 'nullable|numeric',
                'deductions_total' => 'nullable|numeric',
                'status' => 'nullable|in:Generated,Locked,Cancelled',
                'remarks' => 'nullable|string|max:255'
            ]);

            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()], 422);
            }

            $employeeId = $request->employee_id;
            $periodStart = $request->period_start_date;
            $periodEnd = $request->period_end_date;

            $runNo = DB::table('employee_payslip')
                ->where('employee_id', $employeeId)
                ->where('period_start_date', $periodStart)
                ->where('period_end_date', $periodEnd)
                ->count() + 1;

            $payslipNumber = date('Ym', strtotime($periodStart)) . '-' . $employeeId . '-' . str_pad($runNo, 3, '0', STR_PAD_LEFT);
            $data = $request->all();
            $data['payslip_number'] = $payslipNumber;
            $payslip = EmployeePayslip::create($data);
            return response()->json([
                'message' => 'Payslip generated successfully',
                'payslip_number' => $payslipNumber,
                'run_no' => $runNo,
                'employee_payslip_id' => $payslip->employee_payslip_id
            ], 201);
        } catch (\Exception $e) {
            \Log::error('Error creating payslip: ' . $e->getMessage());
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
                'employee_payslip_id' => $id
            ]);
            $validator = Validator::make($request->all(), [
                'organization_id' => 'required|integer|exists:organizations,organization_id',
                'employee_payslip_id' => 'required|integer|exists:employee_payslips,employee_payslip_id',
            ]);
            if ($validator->fails()) {
                return response()->json([
                    'errors' => $validator->errors()
                ], 422);
            }
            $payroll = EmployeePayslip::find($id);
            return response()->json([
                'message' => 'Payroll pay slip found',
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
                'employee_payslip_id' => $id
            ]);
            $rules = [
                'organization_id' => 'required|integer|exists:organizations,organization_id',
                'organization_payroll_loan_type_id' => 'required|integer|exists:organization_payroll_loan_types,organization_payroll_loan_type_id',
                'payroll_run_employee_id' => 'nullable|integer|exists:payroll_run_employees,payroll_run_employee_id',
                'employee_id' => 'required|integer|exists:employees,employee_id',
                'period_start_date' => 'required|date',
                'period_end_date' => 'required|date',
                'net_pay' => 'nullable|numeric',
                'gross_pay' => 'nullable|numeric',
                'deductions_total' => 'nullable|numeric',
                'status' => 'nullable|in:Generated,Locked,Cancelled',
                'remarks' => 'nullable|string|max:255'
            ];
            $validator = Validator::make($request->all(), $rules);
            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()], 422);
            }
            $payroll = EmployeePaySlip::find($id);
            $payroll->update($request->only([
                'organization_entity_id',
                'organization_id',
                'employee_id',
                'payroll_run_employee_id',
                'period_start_date',
                'period_end_date',
                'net_pay',
                'gross_pay',
                'deductions_total',
                'status',
                'remarks',
            ]));
            return response()->json([
                'message' => 'Payroll pay slip Updated Successfully.',
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Something went wrong. Please try again later.',
                'details' => $e->getMessage()
            ], 500);
        }
    }
}
