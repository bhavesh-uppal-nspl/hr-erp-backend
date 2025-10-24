<?php

namespace App\Http\Controllers\AttendenceController;
use App\Http\Controllers\Controller;
use App\Models\AttendenceModels\AttendenceRecord;
use App\Models\EmployeesModel\Employees;
use Auth;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class AttendenceRecordController extends Controller
{
    public function index(Request $request, $org_id)
    {
        try {
            $request->merge(['organization_id' => $org_id]);
            $user = Auth::guard('applicationusers')->user();
            // Get all organization IDs linked to the user
            $organizationIds = $user->Client->Organization->pluck('organization_id')->toArray();

            if (!in_array((int) $org_id, $organizationIds)) {
                return response()->json([
                    'message' => 'Unauthenticated'
                ], 401);
            }

            $perPage = $request->get('per_page');
            $page = (int) $request->get('page', 1);
            $search = $request->get('search');
            $organizationId = $request->get('organization_id');

            // Start query
            $query = AttendenceRecord::with('statusType', 'Employee');

            // Filter by organization ID
            if (!empty($organizationId)) {
                $query->where('organization_id', $organizationId);
            }

            // Apply search filter
            if (!empty($search)) {
                $query->where(function ($q) use ($search) {
                    $q->where('remarks', 'like', '%' . $search . '%')
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


            return response()->json([
                'message' => 'Attendance Record  fetched successfully',
                'attendance_records' => $statusTypes
            ]);
        } catch (\Exception $e) {
            \Log::error('Error fetching attendance Record: ' . $e->getMessage());
            return response()->json([
                'message' => 'Failed to fetch attendance Record'
            ], 500);
        }
    }

    public function reportdata(Request $request, $org_id)
    {
        try {
            $request->merge(['organization_id' => $org_id]);
            $user = Auth::guard('applicationusers')->user();

            // Get all organization IDs linked to the user
            $organizationIds = $user->Client->Organization->pluck('organization_id')->toArray();
            if (!in_array((int) $org_id, $organizationIds)) {
                return response()->json([
                    'message' => 'Unauthenticated'
                ], 401);
            }

            $perPage = $request->get('per_page');
            $page = (int) $request->get('page', 1);
            $search = $request->get('search');
            $organizationId = $request->get('organization_id');

            // Yesterday's date
            $yesterday = Carbon::yesterday()->toDateString();

            // Fetch employees who are PRESENT (have attendance record for yesterday)
            $presentQuery = AttendenceRecord::with(['statusType', 'Employee'])
                ->where('organization_id', $organizationId)
                ->whereDate('attendance_date', $yesterday);

            if (!empty($search)) {
                $presentQuery->where(function ($q) use ($search) {
                    $q->where('remarks', 'like', '%' . $search . '%')
                        ->orWhere('description', 'like', '%' . $search . '%');
                });
            }

            $presentEmployees = $presentQuery->get();

            // Get IDs of employees who are present
            $presentEmployeeIds = $presentEmployees->pluck('employee_id')->toArray();

            // Fetch employees who are ABSENT (no record in attendance table for yesterday)
            $absentEmployees = Employees::where('organization_id', $organizationId)
                ->whereNotIn('employee_id', $presentEmployeeIds)
                ->get()
                ->map(function ($emp) {
                    return [
                        'employee_id' => $emp->employee_id,
                         'employee_code' => $emp->employee_code,
                        'employee_name' => trim(implode(' ', array_filter([
                        $emp->first_name,
                        $emp->middle_name,
                        $emp->last_name
                    ]))),
                        'attendance_status' => 'Absent',
                        'attendance_date' => Carbon::yesterday()->toDateString(),
                        'actual_total_work_minutes' => 0
                    ];
                });

            // Format present employees with attendance status
            $presentData = $presentEmployees->map(function ($record) {
                return [
                    'employee_id' => $record->Employee->employee_id,
                     'employee_code' => $record->Employee->employee_code,
                    'employee_name' => trim(implode(' ', array_filter([
                    $record->Employee?->first_name,
                    $record->Employee?->middle_name,
                    $record->Employee?->last_name
                ]))),
                    'attendance_status' => 'Present',
                    'attendance_date' => $record->attendance_date,
                    'attendance_status_type' => optional($record->statusType)->attendance_status_type ?? null,
                    'clock_in_time' => $record->clock_in_time,
                    'clock_out_time' => $record->clock_out_time,
                    'actual_total_work_minutes' => $record->actual_total_work_minutes ?? 0,
                    'remarks' => $record->remarks,
                ];
            });

            // Combine both present + absent employees
            $attendanceData = $presentData->merge($absentEmployees);

            // Paginate if required
            if ($perPage === 'all') {
                $paginatedData = $attendanceData;
            } elseif (!empty($perPage) && is_numeric($perPage)) {
                $perPage = (int) $perPage;
                $offset = ($page - 1) * $perPage;
                $paginatedData = $attendanceData->slice($offset, $perPage)->values();
            } else {
                $paginatedData = $attendanceData;
            }

            return response()->json([
                'message' => 'Attendance data fetched successfully',
                'date' => $yesterday,
                'total_records' => $attendanceData->count(),
                'attendance_records' => $paginatedData->values()
            ]);
        } catch (\Exception $e) {
            \Log::error('Error fetching attendance Record: ' . $e->getMessage());
            return response()->json([
                'message' => 'Failed to fetch attendance Record'
            ], 500);
        }
    }
}
