<?php

namespace App\Http\Controllers\PayrollController;
use App\Http\Controllers\Controller;
use App\Models\PayrollModels\EmployeePaySlipPayments;
use DB;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Auth;
use Illuminate\Support\Facades\Validator;

class EmployeePayslipPaymentController extends Controller
{
    public function index(Request $request)
    {
        try {
            $perPage = $request->get('per_page');
            $page = (int) $request->get('page', 1);
            $search = $request->get('search');
            $query = EmployeePaySlipPayments::with('Payslip');
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
                'message' => 'Employee Payslip Payments fetched successfully',
                'payroll' => $statusTypes
            ], 200);
        } catch (\Exception $e) {
            \Log::error('Error fetching payslip payments: ' . $e->getMessage());
            return response()->json([
                'error' => 'Something went wrong. Please try again later.',
                'details' => $e->getMessage()
            ], 500);
        }
    }

    public function store(Request $request, $org_id)
    {
        try {

            $validator = Validator::make($request->all(), [
                'employee_payslip_id' => 'nullable|integer|exists:employee_payslips,employee_payslip_id',
                'payment_date' => 'required|date',
                'payment_mode' => 'required|in:Cash,Cheque,BankTransfer UPI,Other',
                'status' => 'required|in:Pending,Processing,Paid,Failed,Cancelled',
                'amount' => 'nullable|numeric',
                'reference_no' => 'nullable|string|max:100',
                'remarks' => 'nullable|string|max:255'
            ]);

            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()], 422);
            }

            $data = $request->all();
            EmployeePaySlipPayments::create($data);
            return response()->json([
                'message' => 'Payslip payment generated successfully',
            ], 201);
        } catch (\Exception $e) {
            \Log::error('Error creating payslip: ' . $e->getMessage());
            return response()->json([
                'error' => 'Something went wrong. Please try again later.',
                'details' => $e->getMessage()
            ], 500);
        }
    }

    public function show(Request $request, $id)
    {
        try {
            $request->merge([
                'employee_payslip_payment_id ' => $id
            ]);
            $validator = Validator::make($request->all(), [
                'employee_payslip_payment_id' => 'required|integer|exists:employee_payslip_payments,employee_payslip_payment_id',
            ]);
            if ($validator->fails()) {
                return response()->json([
                    'errors' => $validator->errors()
                ], 422);
            }
            $payroll = EmployeePaySlipPayments::find($id);
            return response()->json([
                'message' => 'Payroll pay slip payment found',
                'payroll' => $payroll
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Something went wrong. Please try again later.',
                'details' => $e->getMessage()
            ], 500);
        }
    }

    public function update(Request $request, $id)
    {
        try {
            $request->merge([
                'employee_payslip_id' => $id
            ]);
            $rules = [
                'employee_payslip_payment_id' => 'required|integer|exists:employee_payslip_payments,employee_payslip_payment_id',
                'employee_payslip_id' => 'nullable|integer|exists:employee_payslips,employee_payslip_id',
                'payment_date' => 'nullable|date',
                'payment_mode' => 'nullable|in:Cash,Cheque,BankTransfer UPI,Other',
                'status' => 'nullable|in:Pending,Processing,Paid,Failed,Cancelled',
                'amount' => 'nullable|numeric',
                'reference_no' => 'nullable|string|max:100',
                'remarks' => 'nullable|string|max:255'
            ];
            $validator = Validator::make($request->all(), $rules);
            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()], 422);
            }
            $payroll = EmployeePaySlipPayments::find($id);
            $payroll->update($request->only([
                'employee_payslip_id',
                'payment_date',
                'payment_mode',
                'amount',
                'reference_no',
                'status',
                'remarks',
            ]));
            return response()->json([
                'message' => 'Payroll pay slip payments Updated Successfully.',
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Something went wrong. Please try again later.',
                'details' => $e->getMessage()
            ], 500);
        }
    }
}
