<?php

namespace App\Observers;

use App\Models\EmployeesModel\EmployeeLeaves;
use App\Models\EmployeesModel\EmployeeLeaveMonthlySummary;
use Carbon\Carbon;

class EmployeeLeavesObserver
{
    public function saved(EmployeeLeaves $leave)
    {
        $year = Carbon::parse($leave->leave_start_date)->year;
        $month = Carbon::parse($leave->leave_start_date)->month;

        $summary = EmployeeLeaveMonthlySummary::firstOrNew([
            'employee_id' => $leave->employee_id,
            'organization_id' => $leave->organization_id,
            'year' => $year,
            'month' => $month,
        ]);

        $summary->organization_entity_id = $leave->organization_entity_id ?? null;
        $summary->year = $year;
        $summary->month = $month;

        // Recompute summary from all leaves in that month
        $leaves = EmployeeLeaves::with(['leaveType', 'leaveCategory'])
            ->where('organization_id', $leave->organization_id)
            ->where('employee_id', $leave->employee_id)
            ->whereYear('leave_start_date', $year)
            ->whereMonth('leave_start_date', $month)
            ->get();

        if ($leaves->isEmpty()) {
            $summary->delete();
            return;
        }

        // Reset counters
        $summary->total_leave_days = 0;
        $summary->approved_leave_days = 0;
        $summary->unapproved_leave_days = 0;
        $summary->casual_leaves = 0;
        $summary->medical_leaves = 0;
        $summary->earned_leaves = 0;
        $summary->compensatory_off_leaves = 0;
        $summary->leave_without_pay = 0;
        $summary->leave_with_pay = 0;

        foreach ($leaves as $l) {
            $days = (float) $l->total_leave_days;

            $summary->total_leave_days += $days;
            if ($l->leave_status === 'Approved') {
                $summary->approved_leave_days += $days;
            } else {
                $summary->unapproved_leave_days += $days;
            }

            // By TYPE
            if ($l->leaveType) {
                $typeName = strtolower($l->leaveType->leave_type_name);
                if ($typeName === 'casual leave') {
                    $summary->casual_leaves += $days;
                } elseif ($typeName === 'medical leave') {
                    $summary->medical_leaves += $days;
                }
            }

            // By CATEGORY
            if ($l->leaveCategory) {
                $catName = strtolower($l->leaveCategory->leave_category_name);
                switch ($catName) {
                    case 'paid leave':
                    case 'statutory leave':
                        $summary->leave_with_pay += $days;
                        break;
                    case 'leave without pay':
                        $summary->leave_without_pay += $days;
                        break;
                    case 'earned leave':
                        $summary->earned_leaves += $days;
                        break;
                    case 'compensatory off':
                        $summary->compensatory_off_leaves += $days;
                        break;
                }
            }
        }

        $summary->save();
    }

    public function deleted(EmployeeLeaves $leave)
    {
        $year = Carbon::parse($leave->leave_start_date)->year;
        $month = Carbon::parse($leave->leave_start_date)->month;

        $summary = EmployeeLeaveMonthlySummary::where([
            'employee_id' => $leave->employee_id,
            'organization_id' => $leave->organization_id,
            'year' => $year,
            'month' => $month,
        ])->first();

        if (!$summary) {
            return; // nothing to subtract
        }

        $days = (float) $leave->total_leave_days;

        // Subtract totals
        $summary->total_leave_days -= $days;
        if ($leave->leave_status === 'Approved') {
            $summary->approved_leave_days -= $days;
        } else {
            $summary->unapproved_leave_days -= $days;
        }

        // By TYPE
        if ($leave->leaveType) {
            $typeName = strtolower($leave->leaveType->leave_type_name);
            if ($typeName === 'casual leave') {
                $summary->casual_leaves -= $days;
            } elseif ($typeName === 'medical leave') {
                $summary->medical_leaves -= $days;
            }
        }

        // By CATEGORY
        if ($leave->leaveCategory) {
            $catName = strtolower($leave->leaveCategory->leave_category_name);
            switch ($catName) {
                case 'paid leave':
                case 'statutory leave':
                    $summary->leave_with_pay -= $days;
                    break;
                case 'leave without pay':
                    $summary->leave_without_pay -= $days;
                    break;
                case 'earned leave':
                    $summary->earned_leaves -= $days;
                    break;
                case 'compensatory off':
                    $summary->compensatory_off_leaves -= $days;
                    break;
            }
        }

        // If total_leave_days <= 0, delete the summary
        if ($summary->total_leave_days <= 0) {
            $summary->delete();
        } else {
            $summary->save();
        }
    }
}
