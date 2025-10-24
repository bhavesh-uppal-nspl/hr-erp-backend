<?php

// namespace App\Observers;

// use App\Models\EmployeesModel\EmployeeLeaves;
// use App\Models\EmployeesModel\Employees;
// use App\Models\OrganizationModel\OrganizationLeaveEntitlement;
// use App\Models\EmployeesModel\EmployeeLeaveBalances;

// class OrganizationLeaveEntitlementObserver
// {
// public function created(OrganizationLeaveEntitlement $entitlement)
// {
//     $employees = Employees::where('organization_id', $entitlement->organization_id)->get();

//     foreach ($employees as $employee) {
//         // ✅ Calculate already approved leave days for this employee + leave type
//         $leaveTaken = EmployeeLeaves::where('employee_id', $employee->employee_id)
//             ->where('organization_leave_type_id', $entitlement->organization_leave_type_id)
//             ->where('leave_status', 'approved')
//             ->sum('total_leave_days');
//         $carryForward = $entitlement->carry_forward_days ?? 0;
//         $balanceDays = ($entitlement->entitled_days) - $leaveTaken;

//         EmployeeLeaveBalances::create([
//             'employee_id'                => $employee->employee_id,
//             'employee_code'                => $employee->employee_code,
//             'organization_id'            => $entitlement->organization_id,
//             'organization_entity_id'     => $entitlement->organization_entity_id,
//             'organization_leave_type_id' => $entitlement->organization_leave_type_id,
//             'leave_period_start_date'    => now()->startOfYear(),
//             'leave_period_end_date'      => now()->endOfYear(),
//             'entitled_days'              => $entitlement->entitled_days,
//             'carry_forward_days'         => $carryForward,
//             'leave_taken_days'           => $leaveTaken,
//             'encashed_days'              => 0, 
//             'adjusted_days'              => 0, 
//             'last_updated_at'            => now(),
//         ]);
//     }
// }

// }



namespace App\Observers;

use App\Models\EmployeesModel\EmployeeLeaves;
use App\Models\EmployeesModel\Employees;
use App\Models\OrganizationModel\OrganizationLeaveEntitlement;
use App\Models\EmployeesModel\EmployeeLeaveBalances;

class OrganizationLeaveEntitlementObserver
{
    /**
     * Called when a leave entitlement is created
     */
    public function created(OrganizationLeaveEntitlement $entitlement)
    {
        $employees = Employees::where('organization_id', $entitlement->organization_id)->get();

        foreach ($employees as $employee) {
            $this->createOrUpdateBalance($employee->employee_id, $entitlement->organization_leave_type_id);
        }
    }

    /**
     * Called when a leave entitlement is deleted
     */
    public function deleted(OrganizationLeaveEntitlement $entitlement)
    {
        // Remove all balances for this entitlement
        EmployeeLeaveBalances::where('organization_leave_type_id', $entitlement->organization_leave_type_id)
            ->where('organization_id', $entitlement->organization_id)
            ->delete();
    }

    /**
     * Create or update balance when employee applies leave or entitlement is added
     */
    public static function createBalanceIfNotExists($employee_id, $organization_leave_type_id)
    {
        $observer = new self();
        $observer->createOrUpdateBalance($employee_id, $organization_leave_type_id);
    }

    /**
     * Core logic for creating or updating leave balance
     */
    private function createOrUpdateBalance($employee_id, $organization_leave_type_id)
    {
        $employee = Employees::find($employee_id);
        if (!$employee) return;

        // Check if entitlement exists
        $entitlement = OrganizationLeaveEntitlement::where('organization_id', $employee->organization_id)
            ->where('organization_leave_type_id', $organization_leave_type_id)
            ->first();

        // Calculate already approved leave days
        $leaveTaken = EmployeeLeaves::where('employee_id', $employee->employee_id)
            ->where('organization_leave_type_id', $organization_leave_type_id)
            ->where('leave_status', 'approved')
            ->sum('total_leave_days');

        // Prepare data
        $entitledDays = $entitlement ? $entitlement->entitled_days : 0;
        $carryForward = $entitlement ? ($entitlement->carry_forward_days ?? 0) : 0;
        $organizationEntityId = $entitlement ? $entitlement->organization_entity_id : null;

        // Check if balance exists
        $balance = EmployeeLeaveBalances::where('employee_id', $employee->employee_id)
            ->where('organization_leave_type_id', $organization_leave_type_id)
            ->first();

        if ($balance) {
            // Update existing balance
            $balance->update([
                'entitled_days'          => $entitledDays,
                'carry_forward_days'     => $carryForward,
                'leave_taken_days'       => $leaveTaken,
                'organization_entity_id' => $organizationEntityId,
                'encashed_days'          => 0,
                'adjusted_days'          => 0,
                'last_updated_at'        => now(),
            ]);
        } else {
            // Create new balance
            EmployeeLeaveBalances::create([
                'employee_id'                => $employee->employee_id,
                'employee_code'              => $employee->employee_code,
                'organization_id'            => $employee->organization_id,
                'organization_entity_id'     => $organizationEntityId,
                'organization_leave_type_id' => $organization_leave_type_id,
                'leave_period_start_date'    => now()->startOfYear(),
                'leave_period_end_date'      => now()->endOfYear(),
                'entitled_days'              => $entitledDays,
                'carry_forward_days'         => $carryForward,
                'leave_taken_days'           => $leaveTaken,
                'encashed_days'              => 0,
                'adjusted_days'              => 0,
                'last_updated_at'            => now(),
            ]);
        }
    }

    /**
     * Call this function when an employee leave is deleted
     */
    public static function updateBalanceOnLeaveDelete($employee_leave)
    {
        $balance = EmployeeLeaveBalances::where('employee_id', $employee_leave->employee_id)
            ->where('organization_leave_type_id', $employee_leave->organization_leave_type_id)
            ->first();

        if ($balance) {
            $newLeaveTaken = max($balance->leave_taken_days - $employee_leave->total_leave_days, 0);
            $balance->update([
                'leave_taken_days' => $newLeaveTaken,
                'last_updated_at'  => now(),
            ]);
        }
    }
}







