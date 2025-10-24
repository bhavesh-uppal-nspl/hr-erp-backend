<?php

namespace App\Http\Controllers\EmployeeController;
use App\Models\EmployeesModel\EmployeeBankAccount;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Validator;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class EmployeeBankAccountController extends Controller
{
    public function index(Request $request, $org_id)
    {
        try {
            $request->merge(['organization_id' => $org_id]);
            $validator = Validator::make($request->all(), [
                'organization_id' => 'required|integer|exists:organizations,organization_id',
            ]);
            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()], 422);
            }

            $query = EmployeeBankAccount::with('employee')->where('organization_id', $org_id);
            $per = $request->input('per_page', 10);
            $search = $request->input('search');
            if ($search) {
                $query->where(function ($q) use ($search) {
                    $q->where('account_holder_name', 'like', "%{$search}%");
                    $q->where('bank_name', 'like', "%{$search}%");
                    $q->where('account_type', 'like', "%{$search}%");
                    $q->where('qr_code_url', 'like', "%{$search}%");
                    $q->where('remarks', 'like', "%{$search}%");
                });
            }
            $data = $query->orderBy('created_at', 'desc')->paginate($per);
            return response()->json([
                'message' => 'OK',
                'employeeBankAccount' => $data,

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
            $request->merge(['organization_id' => $org_id]);
            $validator = Validator::make($request->all(), [
                'organization_id' => 'required|integer|exists:organizations,organization_id',
                'employee_id' => 'required|integer|exists:employees,employee_id',
                'account_holder_name' => 'required|string|max:255',
                'bank_name' => 'required|string|max:100',
                'ifsc_code' => [
                    'required',
                    'string',
                    'max:20',
                    'regex:/^[A-Z]{4}0[A-Z0-9]{6}$/i',
                ],
           'upi_id' => [
    'required',
    'string',
    'max:50',
    'regex:/^[\w.\-]{2,50}@[a-zA-Z]{2,50}$/',
    Rule::unique('employee_bank_accounts')->where(function ($query) use ($org_id) {
        return $query->where('organization_id', $org_id);
    }),
],
                'account_number' => 'required|string|max:20',
                'account_type' => 'nullable|in:Savings,Current,Salary',
                'is_primary' => 'nullable|boolean',
                'qr_code_url' => 'nullable|url|max:512',
                'remarks' => 'nullable|string|max:255',
            ]);
            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()], 422);
            }
            $data = $request->all();
            $employeeBankAccount = EmployeeBankAccount::create(array_merge($data));
            return response()->json([
                'message' => 'Employee Bank Details  Added SuccessFully.',
                'employeeBankAccount' => $employeeBankAccount
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Something went wrong. Please try again later.',
                'details' => $e->getMessage()
            ], 500);
        }
    }
    public function show(Request $request, $org_id, $bank_id)
    {
        try {
            $request->merge(['organization_id' => $org_id, 'employee_bank_account_id' => $bank_id]);
            $validator = Validator::make($request->all(), [
                'organization_id' => 'required|integer|exists:organizations,organization_id',
                'employee_bank_account_id' => 'required|integer|exists:employee_bank_accounts,employee_bank_account_id',
            ]);
            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()], 422);
            }

            $employeeBank = EmployeeBankAccount::find($bank_id);
            if (!$employeeBank) {
                return response()->json(['error' => 'Employee bank Details not found.'], 404);
            }

            return response()->json([
                'message' => 'Employee Bank Detail Found',
                'employeeBank' => $employeeBank
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Something went wrong. Please try again later.',
                'details' => $e->getMessage()
            ], 500);
        }
    }

    public function update(Request $request, $org_id, $bank_id)
    {
        try {
            $request->merge([
                'organization_id' => $org_id,
                'employee_bank_account_id' => $bank_id
            ]);
            $rules = [
                'organization_id' => 'required|integer|exists:organizations,organization_id',
                'employee_id' => 'sometimes|integer|exists:employees,employee_id',
                'employee_bank_account_id' => 'required|integer|exists:employee_bank_accounts,employee_bank_account_id',
                'account_holder_name' => 'nullable|string|max:100',
                'bank_name' => 'nullable|string|max:100',
                'ifsc_code' => [
                    'nullable',
                    'string',
                    'max:20',
                    'regex:/^[A-Z]{4}0[A-Z0-9]{6}$/i',
                ],
                     'upi_id' => [
    'nullable',
    'string',
    'max:50',
    'regex:/^[\w.\-]{2,50}@[a-zA-Z]{2,50}$/',
    Rule::unique('employee_bank_accounts')->where(function ($query) use ($org_id) {
        return $query->where('organization_id', $org_id);
    }),
],
                
                'account_number' => 'nullable|string|max:20',
                'account_type' => 'nullable|in:savings,current,salary',
                'is_primary' => 'nullable|boolean',
                'qr_code_url' => 'nullable|url|max:512',
                'remarks' => 'nullable|string|max:255',
            ];
            $validator = Validator::make($request->all(), $rules);
            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()], 422);
            }
            $employeeBank = EmployeeBankAccount::find($bank_id);
            $employeeBank->update($request->only([
                'employee_id',
                'account_holder_name',
                'bank_name',
                'ifsc_code',
                'account_number',
                'account_type',
                'upi_id',
                'is_primary',
                'qr_code_url',
                'remarks',
            ]));

            return response()->json([
                'message' => 'Employee Bank Details  Updated Successfully.',
                'employeeBank' => $employeeBank
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Something went wrong. Please try again later.',
                'details' => $e->getMessage()
            ], 500);
        }
    }

    public function destroy(Request $request, $org_id, $bank_id)
    {
        try {
            $request->merge([
                'organization_id' => $org_id,
                'employee_bank_account_id' => $bank_id
            ]);
            $validator = Validator::make($request->all(), [
                'organization_id' => 'required|integer|exists:organizations,organization_id',
                'employee_bank_account_id' => 'required|integer|exists:employee_bank_accounts,employee_bank_account_id',
            ]);
            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()], 422);
            }
            $employeeBankAccount = EmployeeBankAccount::find($bank_id);
            $employeeBankAccount->delete();
            return response()->json([
                'message' => 'Employee Bank Account Deleted Successfully'
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Something went wrong. Please try again later.',
                'details' => $e->getMessage()
            ], 500);
        }
    }
}
