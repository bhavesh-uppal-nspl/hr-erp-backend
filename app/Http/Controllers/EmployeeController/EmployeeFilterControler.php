<?php

namespace App\Http\Controllers\EmployeeController;
use App\Models\EmployeesModel\Employees;
use Auth;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Carbon\Carbon;

class EmployeeFilterControler extends Controller
{


    public function Timelogs($type)
    {
      
        try {

            switch ($type) {
                case "Break Start":
                    return [

                        'clockin' => false,
                        'breakstart' => false,
                        'breakend' => true,
                        'clockout' => true,
                    ];
                case "Clock Out":
                    return [

                        'clockin' => true,
                        'breakstart' => false,
                        'breakend' => false,
                        'clockout' => false,
                    ];

                default:
                    return [
                        'clockin' => false,
                        'breakstart' => true,
                        'breakend' => false,
                        'clockout' => true,
                    ];
            }

        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Something went wrong. Please try again later.',
                'details' => $e->getMessage()
            ], 500);
        }
    }


    public function getallFilterEmployee(Request $request, $org_id)
    {
        try {
            // Main employee data
            $employee = Employees::with(
                'workmodel',
                'address.city',
                'organization',
                'manager',
                'contact',
                'designation',
                'employemnettype',
                'organizationunit',
                'workshift',
                'departmentLocation.department',
                'TodayLatestAttendance',
                'employmentsatus'
            )->where('organization_id', $org_id)->get();

            if ($employee->isEmpty()) {
                return response()->json([
                    'message' => 'Employee not found.'
                ], 404);
            }

         
            $mappedEmployees = $employee->map(function ($emp) {

                $dob = $emp->date_of_birth ? Carbon::parse($emp->date_of_birth) : null;
                $joiningDate = $emp->date_of_joining ? Carbon::parse($emp->date_of_joining) : null;

                $age = $dob ? $dob->age : null;
                $workingSince = $joiningDate ? $joiningDate->diff(Carbon::now()) : null;

                $dd = $emp && $emp->TodayLatestAttendance ? $this->Timelogs($emp->TodayLatestAttendance["attendance_log_type"]) : [
                    'clockin' => true,
                    'breakstart' => false,
                    'breakend' => false,
                    'clockout' => false,
                ];

                // return $dd;


                return array_merge([
                    // 'id' => $emp->employee_id ?? '',
                    'Employee_Code' => $emp->employee_code ?? '',
                    'Name' => trim(($emp->first_name ?? '') . ' ' . ($emp->middle_name ?? '') . ' ' . ($emp->last_name ?? '')),
                    'Department' => $emp->departmentLocation->first()?->department?->department_name ?? '',
                    'Designation' => $emp->designation->designation_name,
                    'Gender' => $emp->gender ?? '',
                    'Date_of_Birth'=>$emp->date_of_birth ?? '',
                    'Age' => $age,
                    'organization_user_id'=>$emp->organization_user_id,
                    'Marital_Status' => $emp->marital_status ?? '',
                    'Date_of_Joining' => $emp->date_of_joining
                        ? Carbon::parse($emp->date_of_joining)->format('d-M-Y')
                        : null,
                    'Working_Since' => $workingSince
                        ? $workingSince->y . ' years ' . $workingSince->m . ' months ' . $workingSince->d . ' days'
                        : null,
                    'Organization_Unit' => $emp->organizationunit->unit_name ?? '',
                    'Employment_Type' => $emp->employemnettype->employment_type_name ?? '',
                    'Work_Model' => $emp->workmodel->work_model_name ?? '',
                    'Work_Shift' => $emp->workshift->work_shift_name ?? '',
                    'Personal_Email' => $emp->contact->personal_email ?? '',
                    'Personal_Phone_No.' => $emp->contact->personal_phone_number ?? '',
                    'Work_Email' => $emp->contact->work_email ?? '',
                    'Work_Phone_No' => $emp->contact->work_phone_number ?? '',
                    'Location' => $emp->address?->city?->city_name ?? '',
                    'Reporting_Manager' => trim(($emp->manager->first_name ?? '') . ' ' . ($emp->manager->middle_name ?? '') . ' ' . ($emp->manager->last_name ?? '')),
                     'profile_image_url'=> $emp->profile_image_url ?? ''

                ],$dd);
            });

            return response()->json($mappedEmployees);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Something went wrong.',
                'details' => $e->getMessage()
            ], 500);
        }
    }




}
