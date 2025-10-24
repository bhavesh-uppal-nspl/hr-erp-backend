<?php

namespace App\Http\Controllers\PayrollController;
use App\Http\Controllers\Controller;
use App\Models\PayrollModels\PayslipComponent;
use DB;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Auth;
use Illuminate\Support\Facades\Validator;

class EmployeePayslipComponentController extends Controller
{
    public function index(Request $request)
    {
        try {
            $perPage = $request->get('per_page');
            $page = (int) $request->get('page', 1);
            $search = $request->get('search');
            $query = PayslipComponent::with('Payslip', 'payrollComponent');
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
                'message' => 'Employee Payslip component fetched successfully',
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
                'component_name' => 'required|string|max:255|unique:employee_payslip_components,component_name',
                'component_type' => 'required|in:Earning,Deduction,EmployerContribution',
                'amount' => 'nullable|numeric',
                'remarks' => 'nullable|string|max:255'
            ]);

            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()], 422);
            }
            $data = $request->all();
            PayslipComponent::create($data);
            return response()->json([
                'message' => 'Payslip component generated successfully',
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
                'employee_payslip_component_id ' => $id
            ]);
            $validator = Validator::make($request->all(), [
                'employee_payslip_component_id' => 'required|integer|exists:employee_payslip_components,employee_payslip_component_id',
            ]);
            if ($validator->fails()) {
                return response()->json([
                    'errors' => $validator->errors()
                ], 422);
            }
            $payroll = PayslipComponent::find($id);
            return response()->json([
                'message' => 'Payslip component found',
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
                'employee_payslip_component_id' => $id
            ]);
            $rules = [
                'employee_payslip_component_id' => 'nullable|integer|exists:employee_payslip_components,employee_payslip_component_id',
                'employee_payslip_id' => 'nullable|integer|exists:employee_payslips,employee_payslip_id',
                'component_name' => 'nullable|string|max:255|unique:employee_payslip_components,component_name',
                'component_type' => 'required|in:Earning,Deduction,EmployerContribution',
                'amount' => 'nullable|numeric',
                'remarks' => 'nullable|string|max:255'
            ];
            $validator = Validator::make($request->all(), $rules);
            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()], 422);
            }
            $payroll = PayslipComponent::find($id);
            $payroll->update($request->only([
                'employee_payslip_id',
                'organization_payroll_component_id',
                'component_name',
                'component_type',
                'amount',
                'remarks',
            ]));
            return response()->json([
                'message' => 'Payslip component Updated Successfully.',
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Something went wrong. Please try again later.',
                'details' => $e->getMessage()
            ], 500);
        }
    }
}
