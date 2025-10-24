<?php

namespace App\Http\Controllers\AttendenceController;
use App\Http\Controllers\Controller;
use App\Models\AttendenceModels\AttendenceMonthlySummary;
use App\Models\EmployeesModel\Employees;
use Auth;
use Carbon\Carbon;
use DB;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class AttendenceMonthlySummaryController extends Controller
{

    public function generateMonthlySummary()
    {
        try {
            DB::beginTransaction();

            $currentYear = Carbon::now()->year;
            $currentMonth = Carbon::now()->month;
            $daysInMonth = Carbon::now()->daysInMonth;

            $employees = Employees::all();

            if ($employees->isEmpty()) {
                return response()->json([
                    'status' => false,
                    'message' => 'No employees found.'
                ], 404);
            }

            $inserted = 0;
            $skipped = 0;

            foreach ($employees as $employee) {
                $exists = AttendenceMonthlySummary::where('employee_id', $employee->employee_id)
                    ->where('year', $currentYear)
                    ->where('month', $currentMonth)
                    ->exists();

                if ($exists) {
                    $skipped++;
                    continue;
                }
                AttendenceMonthlySummary::create([
                    'employee_id' => $employee->employee_id,
                    'organization_id' => $employee->organization_id,
                    'organization_entity_id' => $employee->organization_entity_id ?? null,
                    'year' => $currentYear,
                    'month' => $currentMonth,
                    'total_days_in_month' => $daysInMonth,
                    'workdays_in_month' => null,
                    'off_days_in_month' => null,
                    'holidays_in_month' => null,
                    'weekoff_in_month' => null,
                    'working_days' => null,
                    'absent_days' => null,
                    'leave_days' => null,
                    'casual_leaves' => null,
                    'medical_leaves' => null,
                    'earned_leaves' => null,
                    'compensatory_off_leaves' => null,
                    'approved_leave_days' => null,
                    'unapproved_leave_days' => null,
                    'compensatory_off_earned' => null,
                    'late_entries' => null,
                    'break_time_exceed_entries' => null,
                    'early_exits' => null,
                    'total_overtime_minutes' => null,
                    'expected_shift_minutes' => null,
                    'actual_work_minutes' => null,
                ]);

                $inserted++;
            }

            DB::commit();

            return response()->json([
                'status' => true,
                'message' => 'Monthly summaries generated successfully.',
                'inserted' => $inserted,
                'skipped' => $skipped
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status' => false,
                'message' => 'Something went wrong.',
                'error' => $e->getMessage()
            ], 500);
        }
    }


    

}
