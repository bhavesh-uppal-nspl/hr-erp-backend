<?php

namespace App\Http\Controllers\AttendenceController;
use App\Http\Controllers\Controller;
use App\Models\AttendenceModels\AttendenceDeviationRecord;
use App\Models\AttendenceModels\AttendenceRecord;
use App\Models\AttendenceModels\EmployeeAttendenceTimeLog;
use App\Models\EmployeesModel\Employees;
use Auth;
use Carbon\Carbon;
use DB;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class AttendenceTimeLogController extends Controller
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


            if ($request->input('mode') == 1) {
                $workshift = EmployeeAttendenceTimeLog::with('Employee', 'breakType')->where('organization_id', $org_id)->get();

                if ($workshift->isEmpty()) {
                    return response()->json([
                        'message' => 'workshift not found.'
                    ], 404);
                }
                $mappedWorkshift = $workshift->map(function ($dep) {
                    return [
                        'employee_name' => trim(
                            ($dep->Employee->first_name ?? '') . ' ' .
                            ($dep->Employee->middle_name ?? '') . ' ' .
                            ($dep->Employee->last_name ?? '')
                        )
                        ,
                        'attendance_date' => $dep->attendance_date ?? '',
                        'attendance_log_type' => $dep->attendance_log_type ?? '',
                        'attendance_log_time' => $dep->attendance_log_time ?? '',
                        'break_type' => $dep->breakType->attendance_break_type_name ?? '',

                    ];
                });
                return response()->json($mappedWorkshift);
            }


            // Start query
            $query = EmployeeAttendenceTimeLog::with('Employee', 'breakType');

            // Filter by organization ID
            if (!empty($organizationId)) {
                $query->where('organization_id', $organizationId);
            }

            // Apply search filter
            if (!empty($search)) {
                $query->where(function ($q) use ($search) {
                    $q->where(
                        'deviation_reason_type_name',
                        'like',
                        '%' . $search . '%'
                    )
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
                'message' => 'Attendance Time logs fetched successfully',
                'attendance_time_logs' => $statusTypes
            ]);
        } catch (\Exception $e) {
            return $e;
            Log::error('Error fetching attendance status types: ' .
                $e->getMessage());
            return response()->json([
                'message' => 'Failed to fetch attendance status types'
            ], 500);
        }
    }


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



    public function store(Request $request, $org_id)
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
            if (!$request->has('attendance_date')) {
                $request->merge(['attendance_date' => now()->format('Y-m-d')]);
            }
            if (!$request->has('attendance_log_time')) {
                $request->merge([
                    'attendance_log_time' => Carbon::now('Asia/Kolkata')->format('Y-m-d H:i:s')
                ]);
            }

            $validator = Validator::make($request->all(), [
                'organization_id' => 'required|integer|exists:organizations,organization_id',
                'organization_entity_id' => 'nullable|integer|exists:organization_entities,organization_entity_id',
                'employee_id' => 'required|integer|exists:employees,employee_id',
                'attendance_date' => 'required|date_format:Y-m-d',
                'attendance_log_type' => 'required|in:Clock In,Clock Out,Break Start,Break End',
                'attendance_log_time' => 'required|date_format:Y-m-d H:i:s',
                'attendance_break_type_id' => 'nullable|integer|exists:organization_attendance_break_types,organization_attendance_break_type_id',
                'attendance_source_type_id' => 'nullable|integer|exists:organization_attendance_sources,organization_attendance_source_id',
                'remarks' => 'nullable|string|max:255',
                'deviation_reason_type_id' => 'nullable|integer',
                'deviation_reason_id' => 'nullable|integer'
            ]);

            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()], 422);
            }

            $logs = EmployeeAttendenceTimeLog::create($request->all());

            if (
                in_array($request->attendance_log_type, ['Clock In', 'Clock Out']) &&
                $request->filled('deviation_reason_type_id') && $request->filled('deviation_reason_id')
            ) {
                $employee = Employees::with('workShift')->find($request->employee_id);

                if (!$employee || !$employee->workShift) {
                    return response()->json([
                        'message' => 'Employee work shift not found.'
                    ], 404);
                }

                $workShift = $employee->workShift;
                $expectedTime = $request->attendance_log_type === 'Clock In'
                    ? $workShift->work_shift_start_time
                    : $workShift->work_shift_end_time;

                $expectedDateTime = \Carbon\Carbon::parse($request->attendance_date . ' ' . $expectedTime);
                $actualDateTime = \Carbon\Carbon::parse($request->attendance_log_time);

                // Calculate deviation in minutes
                $deviationMinutes = $expectedDateTime->diffInMinutes($actualDateTime, false);

                if ($deviationMinutes !== 0) {
                    AttendenceDeviationRecord::create([
                        'organization_id' => $org_id,
                        'employee_attendance_timelog_id' => $logs->employee_attendance_timelog_id,
                        'employee_id' => $request->employee_id,
                        'organization_entity_id' => $request->organization_entity_id,
                        'deviation_reason_type_id' => $request->deviation_reason_type_id,
                        'deviation_reason_id' => $request->deviation_reason_id,
                        'attendance_date' => $request->attendance_date,
                        'expected_time' => $expectedDateTime->format('Y-m-d H:i:s'),
                        'actual_time' => $actualDateTime->format('Y-m-d H:i:s'),
                        'deviation_minutes' => $deviationMinutes,
                        'reference_point' => $request->attendance_log_type,
                        'remarks' => $request->remarks
                    ]);
                }
            }

            // 🔹 NEW LOGIC: Handle multiple clock-in/out cycles per day
            if ($request->attendance_log_type === 'Clock Out') {

                $employee = Employees::with('workShift')->find($request->employee_id);

                // Get all clock-in and clock-out logs for the day
                $clockLogs = EmployeeAttendenceTimeLog::where('employee_id', $request->employee_id)
                    ->where('attendance_date', $request->attendance_date)
                    ->whereIn('attendance_log_type', ['Clock In', 'Clock Out'])
                    ->orderBy('attendance_log_time', 'asc')
                    ->get();

                // Calculate total worked time from all clock-in/out pairs
                $totalWorkedMinutes = 0;
                $lastClockIn = null;
                $firstClockInTime = null;
                $lastClockOutTime = null;

                foreach ($clockLogs as $log) {
                    if ($log->attendance_log_type === 'Clock In') {
                        $lastClockIn = \Carbon\Carbon::parse($log->attendance_log_time);
                        if (!$firstClockInTime) {
                            $firstClockInTime = $lastClockIn;
                        }
                    } elseif ($log->attendance_log_type === 'Clock Out' && $lastClockIn) {
                        $clockOut = \Carbon\Carbon::parse($log->attendance_log_time);

                        if ($clockOut->greaterThan($lastClockIn)) {
                            $totalWorkedMinutes += $lastClockIn->diffInMinutes($clockOut);
                            $lastClockOutTime = $clockOut;
                        }
                        $lastClockIn = null;
                    }
                }

                if (!$firstClockInTime) {
                    return response()->json([
                        'error' => 'No clock-in found for this clock-out.'
                    ], 422);
                }

                // Calculate work shift details
                $workShiftMinutes = null;
                $workShiftBreak = null;

                if ($employee && $employee->workShift) {
                    $workShiftStart = \Carbon\Carbon::parse($employee->workShift->work_shift_start_time);
                    $workShiftEnd = \Carbon\Carbon::parse($employee->workShift->work_shift_end_time);
                    $workShiftBreak = $employee->workShift->break_duration_minutes ?? 0;
                    $workShiftMinutes = $workShiftStart->diffInMinutes($workShiftEnd) - $workShiftBreak;
                }

                // 🔹 Calculate actual break minutes from Break Start / Break End logs
                $breakLogs = EmployeeAttendenceTimeLog::where('employee_id', $request->employee_id)
                    ->where('attendance_date', $request->attendance_date)
                    ->whereIn('attendance_log_type', ['Break Start', 'Break End'])
                    ->orderBy('attendance_log_time', 'asc')
                    ->get();

                $actualBreakMinutes = 0;
                $lastBreakStart = null;

                foreach ($breakLogs as $breakLog) {
                    if ($breakLog->attendance_log_type === 'Break Start') {
                        $lastBreakStart = \Carbon\Carbon::parse($breakLog->attendance_log_time);
                    } elseif ($breakLog->attendance_log_type === 'Break End' && $lastBreakStart) {
                        $breakEnd = \Carbon\Carbon::parse($breakLog->attendance_log_time);
                        if ($breakEnd->greaterThan($lastBreakStart)) {
                            $actualBreakMinutes += $lastBreakStart->diffInMinutes($breakEnd);
                        }
                        $lastBreakStart = null;
                    }
                }

                // 🔹 Deduct actual breaks from total worked minutes
                $actualWorkMinutes = $totalWorkedMinutes - $actualBreakMinutes;

                $deviationCount = AttendenceDeviationRecord::where('employee_id', $request->employee_id)
                    ->where('attendance_date', $request->attendance_date)
                    ->count();

                // 🔹 Check if attendance record already exists for this day
                $existingRecord = AttendenceRecord::where('employee_id', $request->employee_id)
                    ->where('attendance_date', $request->attendance_date)
                    ->first();

                $attendanceData = [
                    'organization_id' => $org_id,
                    'organization_entity_id' => $request->organization_entity_id,
                    'employee_id' => $request->employee_id,
                    'attendance_date' => $request->attendance_date,
                    'attendance_status_type_id' => null,
                    'clock_in_time' => $firstClockInTime->format('Y-m-d H:i:s'),
                    'clock_out_time' => $lastClockOutTime->format('Y-m-d H:i:s'),
                    'workshift_total_work_minutes' => +$workShiftMinutes,
                    'actual_total_work_minutes' => +$actualWorkMinutes,
                    'workshift_total_break_minutes' => +$workShiftBreak,
                    'actual_total_break_minutes' => +$actualBreakMinutes,
                    'overtime_minutes' => +$workShiftMinutes ? max(0, $actualWorkMinutes - $workShiftMinutes) : 0,
                    'has_deviations' => $deviationCount > 0,
                    'number_of_deviations' => $deviationCount,
                    'remarks' => $request->remarks
                ];

                if ($existingRecord) {
                    // 🔹 UPDATE existing record with accumulated time
                    $existingRecord->update($attendanceData);
                } else {
                    // 🔹 CREATE new record
                    AttendenceRecord::create($attendanceData);
                }
            }

            return response()->json([
                'message' => 'Attendance log stored successfully.',
                'Attendencelogs' => $logs,
                'metadata' => $this->Timelogs($request->attendance_log_type)
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Something went wrong. Please try again later.',
                'details' => $e->getMessage()
            ], 500);
        }
    }

    public function show(Request $request, $org_id, $log_id)
    {
        try {
            $request->merge([
                'organization_id' => $org_id,
                'employee_attendance_timelog_id' => $log_id
            ]);
            $validator = Validator::make($request->all(), [
                'organization_id' => 'required|integer|exists:organizations,organization_id',
                'employee_attendance_timelog_id' => 'required|integer|exists:employee_attendance_timelogs,employee_attendance_timelog_id',
            ]);
            if ($validator->fails()) {
                return response()->json([
                    'errors' =>
                        $validator->errors()
                ], 422);
            }
            $logs = EmployeeAttendenceTimeLog::find($log_id);
            return response()->json([
                'message' => 'Employee Attendence Time Log Type Found',
                'timeLogs' => $logs
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Something went wrong. Please try again later.',
                'details' => $e->getMessage()
            ], 500);
        }
    }

    public function update(Request $request, $org_id, $log_id)
    {
        try {
            $request->merge(['organization_id' => $org_id]);
            $user = Auth::guard('applicationusers')->user();
            $organizationIds = $user->Client->Organization->pluck('organization_id')->toArray();
            if (!in_array((int) $org_id, $organizationIds)) {
                return response()->json([
                    'message' => 'Unauthenticated'
                ], 401);
            }
            if (!$request->has('attendance_date')) {
                $request->merge(['attendance_date' => now()->format('Y-m-d')]);
            }

            if (!$request->has('attendance_log_time')) {
                $request->merge([
                    'attendance_log_time' => Carbon::now('Asia/Kolkata')->format('Y-m-d H:i:s')
                ]);
            }

            $rules = [
                'organization_id' => 'required|integer|exists:organizations,organization_id',
                'employee_attendance_timelog_id' => 'required|integer|exists:employee_attendance_timelogs,employee_attendance_timelog_id',
                'organization_entity_id' => 'nullable|integer|exists:organization_entities,organization_entity_id',
                'employee_id' => 'nullable|integer|exists:employees,employee_id',
                'attendance_date' => 'nullable|date_format:Y-m-d',
                'attendance_log_type' => 'nullable|in:Clock In,Clock Out,Break Start,Break End',
                'attendance_log_time' => 'nullable|date_format:Y-m-d H:i:s',
                'attendance_break_type_id' => 'nullable|integer|exists:organization_attendance_break_types,organization_attendance_break_type_id',
                'attendance_source_type_id' => 'nullable|integer|exists:organization_attendance_sources,organization_attendance_source_id',
                'remarks' => 'nullable|string|max:255',
                'deviation_reason_type_id' => 'nullable|integer',
                'deviation_reason_id' => 'nullable|integer'
            ];

            $validator = Validator::make($request->all(), $rules);
            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()], 422);
            }

            // Find the attendance log
            $logs = EmployeeAttendenceTimeLog::findOrFail($log_id);

            // Update the attendance log fields
            $logs->update($request->only([
                'organization_entity_id',
                'employee_id',
                'attendance_date',
                'attendance_log_type',
                'attendance_log_time',
                'attendance_break_type_id',
                'attendance_source_type_id',
                'remarks'
            ]));

            // Get employee + work shift details
            $employee = Employees::with('workShift')->find($logs->employee_id);

            if (!$employee || !$employee->workShift) {
                return response()->json([
                    'message' => 'Employee work shift not found.'
                ], 404);
            }

            $workShift = $employee->workShift;


            if (
                in_array($logs->attendance_log_type, ['Clock In', 'Clock Out']) &&
                $request->filled('deviation_reason_type_id') &&
                $request->filled('deviation_reason_id')
            ) {
                $expectedTime = $logs->attendance_log_type === 'Clock In'
                    ? $workShift->work_shift_start_time
                    : $workShift->work_shift_end_time;

                $reference = $logs->attendance_log_type;
                $expectedDateTime = \Carbon\Carbon::parse($logs->attendance_date . ' ' . $expectedTime);
                $actualDateTime = \Carbon\Carbon::parse($logs->attendance_log_time);
                $deviationMinutes = $expectedDateTime->diffInMinutes($actualDateTime, false);

                if ($deviationMinutes !== 0) {
                    AttendenceDeviationRecord::updateOrCreate(
                        ['employee_attendance_timelog_id' => $logs->employee_attendance_timelog_id],
                        [
                            'organization_id' => $org_id,
                            'employee_id' => $logs->employee_id,
                            'organization_entity_id' => $logs->organization_entity_id,
                            'deviation_reason_type_id' => $request->deviation_reason_type_id,
                            'deviation_reason_id' => $request->deviation_reason_id,
                            'attendance_date' => $logs->attendance_date,
                            'expected_time' => $expectedDateTime->format('Y-m-d H:i:s'),
                            'actual_time' => $actualDateTime->format('Y-m-d H:i:s'),
                            'deviation_minutes' => $deviationMinutes,
                            'reference_point' => $reference,
                            'remarks' => $logs->remarks
                        ]
                    );
                } else {

                    AttendenceDeviationRecord::where('employee_attendance_timelog_id', $logs->employee_attendance_timelog_id)->delete();
                }
            }


            $clockIn = EmployeeAttendenceTimeLog::where('employee_id', $logs->employee_id)
                ->where('attendance_date', $logs->attendance_date)
                ->where('attendance_log_type', 'Clock In')
                ->orderBy('attendance_log_time', 'asc')
                ->first();

            $clockOut = EmployeeAttendenceTimeLog::where('employee_id', $logs->employee_id)
                ->where('attendance_date', $logs->attendance_date)
                ->where('attendance_log_type', 'Clock Out')
                ->orderBy('attendance_log_time', 'desc')
                ->first();

            // if ($clockIn && $clockOut) {
            //     $clockInTime = \Carbon\Carbon::parse($clockIn->attendance_log_time);
            //     $clockOutTime = \Carbon\Carbon::parse($clockOut->attendance_log_time);

            //     // Validate clock-out > clock-in
            //     if ($clockOutTime->lessThanOrEqualTo($clockInTime)) {
            //         return response()->json([
            //             'error' => 'Clock-out time must be after clock-in time.'
            //         ], 422);
            //     }

            //     $totalWorkedMinutes = $clockInTime->diffInMinutes($clockOutTime);


            //     $workShiftStart = \Carbon\Carbon::parse($employee->workShift->work_shift_start_time);
            //     $workShiftEnd = \Carbon\Carbon::parse($employee->workShift->work_shift_end_time);
            //     $workShiftBreak = $employee->workShift->break_duration_minutes ?? 0;
            //     $workShiftMinutes = $workShiftStart->diffInMinutes($workShiftEnd) - $workShiftBreak;
            //     $actualWorkMinutes = $totalWorkedMinutes-$workShiftBreak;

            //     $deviationCount = AttendenceDeviationRecord::where('employee_id', $logs->employee_id)
            //         ->where('attendance_date', $logs->attendance_date)
            //         ->count();


            //     AttendenceRecord::updateOrCreate(
            //         [
            //             'employee_id' => $logs->employee_id,
            //             'attendance_date' => $logs->attendance_date
            //         ],
            //         [
            //             'organization_id' => $org_id,
            //             'organization_entity_id' => $logs->organization_entity_id,
            //             'attendance_status_type_id' => null,
            //             'clock_in_time' => $clockInTime->format('Y-m-d H:i:s'),
            //             'clock_out_time' => $clockOutTime->format('Y-m-d H:i:s'),
            //             'workshift_total_work_minutes' => +$workShiftMinutes,
            //             'actual_total_work_minutes' => +$actualWorkMinutes,
            //             'workshift_total_break_minutes' => +$workShiftBreak,
            //             'actual_total_break_minutes' => 0,
            //             // 'overtime_minutes' => +$workShiftMinutes ? max(0, $actualWorkMinutes - $workShiftMinutes) : 0,
            //              'overtime_minutes' => +$workShiftMinutes ? max(0, $actualWorkMinutes - $workShiftMinutes - $workShiftBreak) : 0,
            //             'has_deviations' => $deviationCount > 0,
            //             'number_of_deviations' => $deviationCount,
            //             'remarks' => $logs->remarks
            //         ]
            //     );
            // }


            if ($clockIn && $clockOut) {
                $clockInTime = \Carbon\Carbon::parse($clockIn->attendance_log_time);
                $clockOutTime = \Carbon\Carbon::parse($clockOut->attendance_log_time);

                // Validate clock-out > clock-in
                if ($clockOutTime->lessThanOrEqualTo($clockInTime)) {
                    return response()->json([
                        'error' => 'Clock-out time must be after clock-in time.'
                    ], 422);
                }

                $totalWorkedMinutes = $clockInTime->diffInMinutes($clockOutTime);

                $workShiftStart = \Carbon\Carbon::parse($employee->workShift->work_shift_start_time);
                $workShiftEnd = \Carbon\Carbon::parse($employee->workShift->work_shift_end_time);
                $workShiftBreak = $employee->workShift->break_duration_minutes ?? 0;
                $workShiftMinutes = $workShiftStart->diffInMinutes($workShiftEnd) - $workShiftBreak;

                // 🔹 Calculate actual break minutes from Break Start / Break End logs
                $breakLogs = EmployeeAttendenceTimeLog::where('employee_id', $logs->employee_id)
                    ->where('attendance_date', $logs->attendance_date)
                    ->whereIn('attendance_log_type', ['Break Start', 'Break End'])
                    ->orderBy('attendance_log_time', 'asc')
                    ->get();

                $actualBreakMinutes = 0;
                $lastBreakStart = null;

                foreach ($breakLogs as $breakLog) {
                    if ($breakLog->attendance_log_type === 'Break Start') {
                        $lastBreakStart = \Carbon\Carbon::parse($breakLog->attendance_log_time);
                    } elseif ($breakLog->attendance_log_type === 'Break End' && $lastBreakStart) {
                        $breakEnd = \Carbon\Carbon::parse($breakLog->attendance_log_time);
                        if ($breakEnd->greaterThan($lastBreakStart)) {
                            $actualBreakMinutes += $lastBreakStart->diffInMinutes($breakEnd);
                        }
                        $lastBreakStart = null;
                    }
                }

                // 🔹 Deduct actual breaks from total worked minutes
                $actualWorkMinutes = $totalWorkedMinutes - $actualBreakMinutes;

                $deviationCount = AttendenceDeviationRecord::where('employee_id', $logs->employee_id)
                    ->where('attendance_date', $logs->attendance_date)
                    ->count();

                AttendenceRecord::updateOrCreate(
                    [
                        'employee_id' => $logs->employee_id,
                        'attendance_date' => $logs->attendance_date
                    ],
                    [
                        'organization_id' => $org_id,
                        'organization_entity_id' => $logs->organization_entity_id,
                        'attendance_status_type_id' => null,
                        'clock_in_time' => $clockInTime->format('Y-m-d H:i:s'),
                        'clock_out_time' => $clockOutTime->format('Y-m-d H:i:s'),
                        'workshift_total_work_minutes' => +$workShiftMinutes,
                        'actual_total_work_minutes' => +$actualWorkMinutes,
                        'workshift_total_break_minutes' => +$workShiftBreak,
                        'actual_total_break_minutes' => +$actualBreakMinutes,
                        'overtime_minutes' => +$workShiftMinutes ? max(0, $actualWorkMinutes - $workShiftMinutes) : 0,
                        'has_deviations' => $deviationCount > 0,
                        'number_of_deviations' => $deviationCount,
                        'remarks' => $logs->remarks
                    ]
                );
            }


            return response()->json([
                'message' => 'Employee Attendance Time Log & Records updated successfully.',
                'Attendencelogs' => $logs
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Something went wrong. Please try again later.',
                'details' => $e->getMessage()
            ], 500);
        }
    }


    // public function destroy(Request $request, $org_id, $log_id)
    // {
    //     DB::beginTransaction();

    //     try {
    //         $request->merge([
    //             'organization_id' => $org_id,
    //             'employee_attendance_timelog_id' => $log_id
    //         ]);

    //         $validator = Validator::make($request->all(), [
    //             'organization_id' =>
    //                 'required|integer|exists:organizations,organization_id',
    //             'employee_attendance_timelog_id' =>
    //                 'required|integer|exists:employee_attendance_timelogs,employee_attendance_timelog_id',
    //         ]);

    //         if ($validator->fails()) {
    //             return response()->json([
    //                 'errors' => $validator->errors()
    //             ], 422);
    //         }

    //         $logs = EmployeeAttendenceTimeLog::find($log_id);

    //         if (!$logs) {
    //             return response()->json([
    //                 'error' => 'Employee Attendance Time Log not found.'
    //             ], 404);
    //         }

    //         // Get the employee_id from the log
    //         $employeeId = $logs->employee_id;

    //         // Delete ALL related deviations for this employee
    //         AttendenceDeviationRecord::where('employee_id', $employeeId)->delete();

    //         AttendenceRecord::where('employee_id', $employeeId)->delete();

    //         EmployeeAttendenceTimeLog::where('employee_id', $employeeId)->delete();

    //         DB::commit();

    //         return response()->json([
    //             'message' => 'Employee Attendance Time Log and all related records deleted successfully.'
    //         ], 200);

    //     } catch (\Exception $e) {
    //         DB::rollBack();

    //         return response()->json([
    //             'error' => 'Something went wrong. Please try again later.',
    //             'details' => $e->getMessage()
    //         ], 500);
    //     }
    // }


    public function destroy(Request $request, $org_id, $log_id)
    {
        DB::beginTransaction();

        try {
            // Merge organization_id & log_id into request
            $request->merge([
                'organization_id' => $org_id,
                'employee_attendance_timelog_id' => $log_id
            ]);

            // Validate request
            $validator = Validator::make($request->all(), [
                'organization_id' =>
                    'required|integer|exists:organizations,organization_id',
                'employee_attendance_timelog_id' =>
                    'required|integer|exists:employee_attendance_timelogs,employee_attendance_timelog_id',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'errors' => $validator->errors()
                ], 422);
            }

            // Find the specific log first
            $log = EmployeeAttendenceTimeLog::find($log_id);

            if (!$log) {
                return response()->json([
                    'error' => 'Employee Attendance Time Log not found.'
                ], 404);
            }

            $employeeId = $log->employee_id;
            $attendanceDate = $log->attendance_date;
            EmployeeAttendenceTimeLog::where('employee_id', $employeeId)
                ->where('attendance_date', $attendanceDate)
                ->delete();
            AttendenceDeviationRecord::where('employee_id', $employeeId)
                ->whereDate('attendance_date', $attendanceDate)
                ->delete();
            AttendenceRecord::where('employee_id', $employeeId)
                ->whereDate('attendance_date', $attendanceDate)
                ->delete();
            DB::commit();
            return response()->json([
                'message' => 'Employee attendance logs and related records for this date deleted successfully.'
            ], 200);

        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'error' => 'Something went wrong. Please try again later.',
                'details' => $e->getMessage()
            ], 500);
        }
    }


}
