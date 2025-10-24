<?php

namespace App\Http\Controllers\EmployeeController;
use App\Models\EmployeesModel\EmployeeLeaveMonthlySummary;
use App\Models\EmployeesModel\EmployeeLeaves;
use Auth;
use Carbon\Carbon;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class EmployeeLeaveSummaryController extends Controller
{

    public function getMonthlySummary(Request $request, $org_id)
    {
        if (!$org_id) {
            return response()->json(['error' => 'organization_id is required'], 422);
        }


         if ($request->input('mode') == 1) {
                $summary = EmployeeLeaveMonthlySummary::with(['employee:employee_id,first_name,last_name,employee_code'])->where('organization_id', $org_id)->get();

                if ($summary->isEmpty()) {
                    return response()->json([
                        'message' => 'summary not found.'
                    ], 404);
                }
                $mappedsummary = $summary->map(function ($dep) {


                    $employee_name = null;
                if ($dep->employee) {
                    $name_parts = array_filter([
                        $dep->employee->first_name,
                        $dep->employee->middle_name,
                        $dep->employee->last_name
                    ]);
                    $employee_name = implode(' ', $name_parts);
                }



                    return [
                        'employee_name'=>$employee_name,
                        'employee_code' => $dep->employee->employee_code ?? '',
                        'year' => $dep->year ?? '',
                        'month' => $dep->month ?? '',
                        'total_leave_days' => $dep->total_leave_days ?? '',
                        'approved_leave_days' => $dep->approved_leave_days ?? '',
                        'unapproved_leave_days' => $dep->unapproved_leave_days  ?? '',
                        'casual_leaves' => $dep->casual_leaves  ?? '',
                        'medical_leaves' => $dep->medical_leaves  ?? '',
                        'earned_leaves' => $dep->earned_leaves  ?? '',
                        'compensatory_off_leaves' => $dep->compensatory_off_leaves  ?? '',
                        'leave_without_pay' => $dep->leave_without_pay  ?? '',
                        'leave_with_pay' => $dep->leave_with_pay  ?? '',
                       
                    ];
                });
                return response()->json($mappedsummary);
            }



        // Fetch summaries with employee name
        $summaries = EmployeeLeaveMonthlySummary::with(['employee:employee_id,first_name,last_name,employee_code'])
            ->where('organization_id', $org_id)
            ->get()
            ->map(function ($summary) {
                return [

                     'employee_leave_monthly_summary_id' => $summary->employee_leave_monthly_summary_id,
                    'employee_id' => $summary->employee_id,
                    'employee_code' => $summary->employee ? $summary->employee->employee_code : null,
                    'employee_name' => $summary->employee ? $summary->employee->first_name . ' ' . $summary->employee->last_name : null,
                    'year' => $summary->year,
                    'month' => $summary->month,
                    'total_leave_days' => $summary->total_leave_days,
                    'approved_leave_days' => $summary->approved_leave_days,
                    'unapproved_leave_days' => $summary->unapproved_leave_days,
                     'casual_leaves' => $summary->casual_leaves,
                    'medical_leaves' => $summary->medical_leaves,
                    'earned_leaves' => $summary->earned_leaves,
                    'compensatory_off_leaves' => $summary->compensatory_off_leaves,
                    'leave_without_pay' => $summary->leave_without_pay,
                    'leave_with_pay' => $summary->leave_with_pay,
                ];
            });

       return response()->json([
                'summary' => $summaries
               
            ], 200);
    }

}
