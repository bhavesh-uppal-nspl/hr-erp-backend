<?php

namespace App\Http\Controllers\EmployeeController;
use App\Models\EmployeesModel\EmployeeLeaveBalances;
use Carbon\Carbon;
use Illuminate\Support\Facades\Validator;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class EmployeeLeaveBalanceController extends Controller
{


    public function employeesWhoTookLeave(Request $request, $org_id)
    {


        if ($request->input('mode') == 1) {
            $balances = EmployeeLeaveBalances::with(['employee:employee_id,first_name,last_name,organization_id','leaveType:organization_leave_type_id,leave_type_name'])->where('organization_id', $org_id)
                ->where('leave_taken_days', '>', 0)
                ->get();


            if ($balances->isEmpty()) {
                return response()->json([
                    'message' => 'balances not found.'
                ], 404);
            }
            $mappedbalances = $balances->map(function ($dep) {
                return [
                 'employee_name' => trim(
    ($emp->employee?->first_name ?? '') . ' ' .
    ($emp->employee?->middle_name ?? '') . ' ' .
    ($emp->employee?->last_name ?? '')
),
                   'leave_type' => $dep->leaveType?->leave_type_name ?? '',
                    'leave_period_start_date' => $dep->leave_period_start_date ?? '',
                    'leave_period_end_date' => $dep->leave_period_end_date ?? '',
                    'entitled_days' => $dep->entitled_days ?? '',
                    'carry_forward_days' => $dep->carry_forward_days ?? '',
                    'leave_taken_days' => $dep->leave_taken_days ?? '',
                    'encashed_days' => $dep->encashed_days ?? '',
                    'adjusted_days' => $dep->adjusted_days ?? '',
                    'balance_days' => $dep->balance_days ?? '',
                ];
            });
            return response()->json($mappedbalances);
        }





        $balances = EmployeeLeaveBalances::with([
            'employee:employee_id,first_name,last_name,organization_id',
            'leaveType:organization_leave_type_id,leave_type_name'
        ])
            ->where('organization_id', $org_id)
            ->where('leave_taken_days', '>', 0)
            ->get();

        return response()->json([
            'success' => true,
            'data' => $balances
        ]);
    }


}

