<?php

namespace App\Http\Controllers\InternController;
use App\Http\Controllers\Controller;
use App\Models\AttendenceModels\AttendenceDeviationRecord;
use App\Models\AttendenceModels\AttendenceRecord;
use App\Models\AttendenceModels\EmployeeAttendenceTimeLog;
use App\Models\EmployeesModel\Employees;
use App\Models\InterModel\InternAttendanceDeviationRecord;
use App\Models\InterModel\InternAttendanceRecord;
use App\Models\InterModel\Interns;
use App\Models\InterModel\InternTimeLogs;
use App\Models\InterModel\OverTimeRecords;
use Auth;
use Carbon\Carbon;
use DB;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class InterAttendanceTimeLogController extends Controller
{
    // public function index(Request $request, $org_id)
    // {
    //     try {


    //         $request->merge(['organization_id' => $org_id]);
    //         $user = Auth::guard('applicationusers')->user();
    //         // Get all organization IDs linked to the user
    //         $organizationIds = $user->Client->Organization->pluck('organization_id')->toArray();

    //         if (!in_array((int) $org_id, $organizationIds)) {
    //             return response()->json([
    //                 'message' => 'Unauthenticated'
    //             ], 401);
    //         }


    //         $perPage = $request->get('per_page');
    //         $page = (int) $request->get('page', 1);
    //         $search = $request->get('search');
    //         $organizationId = $request->get('organization_id');

    //         // Start query
    //         $query = InternTimeLogs::with('Intern');

    //         // Filter by organization ID
    //         if (!empty($organizationId)) {
    //             $query->where('organization_id', $organizationId);
    //         }

    //         // Apply search filter
    //         if (!empty($search)) {
    //             $query->where(function ($q) use ($search) {
    //                 $q->where(
    //                     'deviation_reason_type_name',
    //                     'like',
    //                     '%' . $search . '%'
    //                 )
    //                     ->orWhere('description', 'like', '%' . $search . '%');
    //             });
    //         }
    //         if ($perPage === 'all') {
    //             $statusTypes = $query->get();
    //         } elseif (!empty($perPage) && is_numeric($perPage)) {
    //             $perPage = (int) $perPage;
    //             $statusTypes = $query->paginate($perPage, ['*'], 'page', $page);
    //         } else {
    //             $statusTypes = $query->get();
    //         }


    //         return response()->json([
    //             'message' => 'Intern Attendance Time logs fetched successfully',
    //             'attendance_time_logs' => $statusTypes
    //         ]);
    //     } catch (\Exception $e) {
    //         Log::error('Error fetching attendance status types: ' .
    //             $e->getMessage());
    //         return response()->json([
    //             'message' => 'Failed to fetch attendance status types'
    //         ], 500);
    //     }
    // }



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


            if ($request->input('mode') == 1) {
                $timelog = InternTimeLogs::with('Intern','breakType')->where('organization_id', $org_id)->get();

                if ($timelog->isEmpty()) {
                    return response()->json([
                        'message' => 'Timelogs not found.'
                    ], 404);
                }
                $mappedWorkshift = $timelog->map(function ($dep) {
                    return [
                     'intern_name' => trim(($dep->Intern->first_name ?? '') . ' ' . ($dep->Intern->last_name ?? '')),

                        'attendance_date' => $dep->attendance_date ?? '',
                        'attendance_log_type' => $dep->attendance_log_type ?? '',
                        'attendance_log_time' => $dep->attendance_log_time ?? '',
                        'break_type' => $dep->breakType->attendance_break_type_name?? '',
                        'remarks' => $dep->remarks  ?? '',
                    ];
                });
                return response()->json($mappedWorkshift);
            }


            // Fetch all logs for the organization without search or pagination
            $attendanceTimeLogs = InternTimeLogs::with('Intern')
                ->where('organization_id', $org_id)
                ->get();

            return response()->json([
                'message' => 'Intern Attendance Time logs fetched successfully',
                'attendance_time_logs' => $attendanceTimeLogs
            ]);
        } catch (\Exception $e) {
            Log::error('Error fetching attendance status types: ' . $e->getMessage());
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
                'intern_id' => 'required|integer|exists:interns,intern_id',
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

            $logs = InternTimeLogs::create($request->all());

            if (
                in_array($request->attendance_log_type, ['Clock In', 'Clock Out']) &&
                $request->filled('deviation_reason_type_id') && $request->filled('deviation_reason_id')
            ) {
                $interns = Interns::with('workShift')->find($request->intern_id);

                if (!$interns || !$interns->workShift) {
                    return response()->json([
                        'message' => 'Employee work shift not found.'
                    ], 404);
                }

                $workShift = $interns->workShift;
                $expectedTime = $request->attendance_log_type === 'Clock In'
                    ? $workShift->work_shift_start_time
                    : $workShift->work_shift_end_time;

                $expectedDateTime = \Carbon\Carbon::parse($request->attendance_date . ' ' . $expectedTime);
                $actualDateTime = \Carbon\Carbon::parse($request->attendance_log_time);

                // Calculate deviation in minutes
                $deviationMinutes = $expectedDateTime->diffInMinutes($actualDateTime, false);

                if ($deviationMinutes !== 0) {
                    InternAttendanceDeviationRecord::create([
                        'organization_id' => $org_id,
                        'intern_attendance_timelog_id' => $logs->intern_attendance_timelog_id,
                        'intern_id' => $request->intern_id,
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



            if ($request->attendance_log_type === 'Clock Out') {
                $clockIn = InternTimeLogs::where('intern_id', $request->intern_id)
                    ->where('attendance_date', $request->attendance_date)
                    ->where('attendance_log_type', 'Clock In')
                    ->orderBy('attendance_log_time', 'asc')
                    ->first();

                if ($clockIn) {

                    $intern = Interns::with('workShift')->find($request->intern_id);

                    $clockInTime = \Carbon\Carbon::parse($clockIn->attendance_log_time);
                    $clockOutTime = \Carbon\Carbon::parse($request->attendance_log_time);

                    if ($clockOutTime->lessThanOrEqualTo($clockInTime)) {
                        return response()->json([
                            'error' => 'Clock-out time must be after clock-in time.'
                        ], 422);
                    }

                    $totalWorkedMinutes = $clockInTime->diffInMinutes($clockOutTime);
                    $actualWorkMinutes = $totalWorkedMinutes;

                    // $workShiftMinutes = null;
                    // $workShiftBreak = null;

                    // $workShiftStart = \Carbon\Carbon::parse($intern->workShift->work_shift_start_time);
                    // $workShiftEnd = \Carbon\Carbon::parse($intern->workShift->work_shift_end_time);

                    // $workShiftBreak = $intern->workShift->break_duration_minutes ?? 0;
                    // $workShiftMinutes = $workShiftStart->diffInMinutes($workShiftEnd) - $workShiftBreak;


                    // Check if work shift exists
                    if (!$intern || !$intern->workShift) {
                        return response()->json([
                            'error' => 'Work shift not found for this intern.'
                        ], 404);
                    }

                    // Use the model's accessor for accurate calculation (handles overnight shifts)
                    $workShiftMinutes = $intern->workShift->total_work_minutes;
                    $workShiftBreak = $intern->workShift->total_break_minutes;

                    // 🔹 Calculate actual break minutes from Break Start / Break End logs
                    $breakLogs = InternTimeLogs::where('intern_id', $request->intern_id)
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

                    // 🔹 Deduct actual breaks from actual work minutes
                    $actualWorkMinutes = $totalWorkedMinutes - $actualBreakMinutes;

                    $deviationCount = InternAttendanceDeviationRecord::where('intern_id', $request->intern_id)
                        ->where('attendance_date', $request->attendance_date)
                        ->count();

                    $data = InternAttendanceRecord::create([
                        'organization_id' => $org_id,
                        'organization_entity_id' => $request->organization_entity_id,
                        'intern_id' => $request->intern_id,
                        'attendance_date' => $request->attendance_date,
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
                        'remarks' => $request->remarks
                    ]);



                    if ($data->overtime_minutes > 0) {
                        // make its entry in overtime record tabel 
                        OverTimeRecords::create([

                            'intern_id' => $request->intern_id,
                            'organization_id' => $org_id,
                            'organization_entity_id' => $request->organization_entity_id,
                            'intern_attendance_timelog_id' => $logs->intern_attendance_timelog_id,
                            'attendance_date' => $request->attendance_date,
                            'overtime_minutes' => $data->overtime_minutes,
                            'start_time' => $data->clock_in_time,
                            'end_time' => $data->clock_out_time


                        ]);

                    }




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
                'intern_attendance_timelog_id' => $log_id
            ]);
            $validator = Validator::make($request->all(), [
                'organization_id' => 'required|integer|exists:organizations,organization_id',
                'intern_attendance_timelog_id' => 'required|integer|exists:intern_attendance_timelogs,intern_attendance_timelog_id',
            ]);
            if ($validator->fails()) {
                return response()->json([
                    'errors' =>
                        $validator->errors()
                ], 422);
            }
            $logs = InternTimeLogs::find($log_id);
            return response()->json([
                'message' => 'Intern Attendence Time Log Type Found',
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
                return response()->json(['message' => 'Unauthenticated'], 401);
            }

            // Default attendance date & time
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
                'organization_entity_id' => 'nullable|integer|exists:organization_entities,organization_entity_id',
                'intern_id' => 'nullable|integer|exists:interns,intern_id',
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

            // 🔹 Find log
            $log = InternTimeLogs::findOrFail($log_id);

            // 🔹 Update log
            $log->update($request->only([
                'organization_entity_id',
                'intern_id',
                'attendance_date',
                'attendance_log_type',
                'attendance_log_time',
                'attendance_break_type_id',
                'attendance_source_type_id',
                'remarks'
            ]));

            // 🔹 Fetch intern + work shift
            $intern = Interns::with('workShift')->find($log->intern_id);
            if (!$intern || !$intern->workShift) {
                return response()->json(['message' => 'Intern work shift not found.'], 404);
            }

            $workShift = $intern->workShift;

            // 🔹 Handle deviation records
            if (
                in_array($log->attendance_log_type, ['Clock In', 'Clock Out']) &&
                $request->filled('deviation_reason_type_id') &&
                $request->filled('deviation_reason_id')
            ) {
                $expectedTime = $log->attendance_log_type === 'Clock In'
                    ? $workShift->work_shift_start_time
                    : $workShift->work_shift_end_time;

                $reference = $log->attendance_log_type;
                $expectedDateTime = Carbon::parse($log->attendance_date . ' ' . $expectedTime);
                $actualDateTime = Carbon::parse($log->attendance_log_time);
                $deviationMinutes = $expectedDateTime->diffInMinutes($actualDateTime, false);

                if ($deviationMinutes !== 0) {
                    InternAttendanceDeviationRecord::updateOrCreate(
                        ['intern_attendance_timelog_id' => $log->intern_attendance_timelog_id],
                        [
                            'organization_id' => $org_id,
                            'organization_entity_id' => $log->organization_entity_id,
                            'intern_id' => $log->intern_id,
                            'deviation_reason_type_id' => $request->deviation_reason_type_id,
                            'deviation_reason_id' => $request->deviation_reason_id,
                            'attendance_date' => $log->attendance_date,
                            'expected_time' => $expectedDateTime->format('Y-m-d H:i:s'),
                            'actual_time' => $actualDateTime->format('Y-m-d H:i:s'),
                            'deviation_minutes' => $deviationMinutes,
                            'reference_point' => $reference,
                            'remarks' => $log->remarks
                        ]
                    );
                } else {
                    InternAttendanceDeviationRecord::where('intern_attendance_timelog_id', $log->intern_attendance_timelog_id)->delete();
                }
            }

            // 🔹 Find Clock In & Clock Out logs for this intern and date
            $clockIn = InternTimeLogs::where('intern_id', $log->intern_id)
                ->where('attendance_date', $log->attendance_date)
                ->where('attendance_log_type', 'Clock In')
                ->orderBy('attendance_log_time', 'asc')
                ->first();

            $clockOut = InternTimeLogs::where('intern_id', $log->intern_id)
                ->where('attendance_date', $log->attendance_date)
                ->where('attendance_log_type', 'Clock Out')
                ->orderBy('attendance_log_time', 'desc')
                ->first();

            if ($clockIn && $clockOut) {
                $clockInTime = Carbon::parse($clockIn->attendance_log_time);
                $clockOutTime = Carbon::parse($clockOut->attendance_log_time);

                if ($clockOutTime->lessThanOrEqualTo($clockInTime)) {
                    return response()->json(['error' => 'Clock-out time must be after clock-in time.'], 422);
                }

                $totalWorkedMinutes = $clockInTime->diffInMinutes($clockOutTime);

                $workShiftStart = Carbon::parse($workShift->work_shift_start_time);
                $workShiftEnd = Carbon::parse($workShift->work_shift_end_time);
                $workShiftBreak = $workShift->break_duration_minutes ?? 0;
                $workShiftMinutes = $workShiftStart->diffInMinutes($workShiftEnd) - $workShiftBreak;

                // 🔹 Calculate actual breaks
                $breakLogs = InternTimeLogs::where('intern_id', $log->intern_id)
                    ->where('attendance_date', $log->attendance_date)
                    ->whereIn('attendance_log_type', ['Break Start', 'Break End'])
                    ->orderBy('attendance_log_time', 'asc')
                    ->get();

                $actualBreakMinutes = 0;
                $lastBreakStart = null;
                foreach ($breakLogs as $breakLog) {
                    if ($breakLog->attendance_log_type === 'Break Start') {
                        $lastBreakStart = Carbon::parse($breakLog->attendance_log_time);
                    } elseif ($breakLog->attendance_log_type === 'Break End' && $lastBreakStart) {
                        $breakEnd = Carbon::parse($breakLog->attendance_log_time);
                        if ($breakEnd->greaterThan($lastBreakStart)) {
                            $actualBreakMinutes += $lastBreakStart->diffInMinutes($breakEnd);
                        }
                        $lastBreakStart = null;
                    }
                }

                $actualWorkMinutes = $totalWorkedMinutes - $actualBreakMinutes;

                $deviationCount = InternAttendanceDeviationRecord::where('intern_id', $log->intern_id)
                    ->where('attendance_date', $log->attendance_date)
                    ->count();

                // 🔹 Update or create attendance record
                InternAttendanceRecord::updateOrCreate(
                    [
                        'intern_id' => $log->intern_id,
                        'attendance_date' => $log->attendance_date
                    ],
                    [
                        'organization_id' => $org_id,
                        'organization_entity_id' => $log->organization_entity_id,
                        'attendance_status_type_id' => null,
                        'clock_in_time' => $clockInTime->format('Y-m-d H:i:s'),
                        'clock_out_time' => $clockOutTime->format('Y-m-d H:i:s'),
                        'workshift_total_work_minutes' => +$workShiftMinutes,
                        'actual_total_work_minutes' => +$actualWorkMinutes,
                        'workshift_total_break_minutes' => +$workShiftBreak,
                        'actual_total_break_minutes' => +$actualBreakMinutes,
                        'overtime_minutes' => ($workShiftMinutes !== null)
                            ? max(0, $actualWorkMinutes - $workShiftMinutes)
                            : 0,
                        'has_deviations' => $deviationCount > 0,
                        'number_of_deviations' => $deviationCount,
                        'remarks' => $log->remarks
                    ]
                );


                $overtimeMinutes = max(0, $actualWorkMinutes - $workShiftMinutes);
                if ($overtimeMinutes > 0) {
                    OverTimeRecords::updateOrCreate(
                        [
                            'intern_id' => $log->intern_id,
                            'attendance_date' => $log->attendance_date
                        ],
                        [
                            'organization_id' => $org_id,
                            'organization_entity_id' => $log->organization_entity_id,
                            'intern_attendance_timelog_id' => $log->intern_attendance_timelog_id,
                            'clock_in_time' => $clockInTime->format('Y-m-d H:i:s'),
                            'clock_out_time' => $clockOutTime->format('Y-m-d H:i:s'),
                            'workshift_end_time' => $workShiftEnd->format('Y-m-d H:i:s'),
                            'overtime_minutes' => $overtimeMinutes,
                            'remarks' => $log->remarks
                        ]
                    );
                } else {
                    OverTimeRecords::where('intern_id', $log->intern_id)
                        ->where('attendance_date', $log->attendance_date)
                        ->delete();
                }
            }

            return response()->json([
                'message' => 'Intern Attendance Time Log & Records updated successfully.',
                'attendance_log' => $log
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Something went wrong. Please try again later.',
                'details' => $e->getMessage()
            ], 500);
        }
    }

    public function destroy(Request $request, $org_id, $log_id)
    {
        DB::beginTransaction();
        try {
            $log = InternTimeLogs::findOrFail($log_id);

            $internId = $log->intern_id;
            $attendanceDate = $log->attendance_date;
            $logType = $log->attendance_log_type;

            switch ($logType) {
                case 'Clock In':
                    // Delete everything for this intern & date
                    InternTimeLogs::where('intern_id', $internId)
                        ->where('attendance_date', $attendanceDate)
                        ->delete();
                    InternAttendanceDeviationRecord::where('intern_id', $internId)
                        ->whereDate('attendance_date', $attendanceDate)
                        ->delete();
                    InternAttendanceRecord::where('intern_id', $internId)
                        ->whereDate('attendance_date', $attendanceDate)
                        ->delete();
                    OverTimeRecords::where('intern_id', $internId)
                        ->whereDate('attendance_date', $attendanceDate)
                        ->delete();
                    break;

                case 'Clock Out':
                    // Delete only this Clock Out log
                    $log->delete();

                    // Delete overtime linked to this log
                    OverTimeRecords::where('intern_attendance_timelog_id', $log_id)->delete();

                    // Update or delete attendance record if needed
                    InternAttendanceRecord::where('intern_id', $internId)
                        ->where('attendance_date', $attendanceDate)
                        ->delete();

                    // Delete deviation record for this log if exists
                    InternAttendanceDeviationRecord::where('intern_attendance_timelog_id', $log_id)
                        ->delete();
                    break;

                case 'Break Start':
                    // Delete this Break Start
                    $log->delete();

                    // Delete paired Break End if exists (first one after this Break Start)
                    $pairedBreakEnd = InternTimeLogs::where('intern_id', $internId)
                        ->where('attendance_date', $attendanceDate)
                        ->where('attendance_log_type', 'Break End')
                        ->where('attendance_log_time', '>', $log->attendance_log_time)
                        ->orderBy('attendance_log_time', 'asc')
                        ->first();
                    if ($pairedBreakEnd) {
                        $pairedBreakEnd->delete();
                        // Delete overtime linked to paired Break End if exists
                        OverTimeRecords::where('intern_attendance_timelog_id', $pairedBreakEnd->intern_attendance_timelog_id)->delete();
                    }
                    break;

                case 'Break End':
                    // Delete only this Break End
                    $log->delete();
                    // Delete overtime linked to this Break End
                    OverTimeRecords::where('intern_attendance_timelog_id', $log_id)->delete();
                    break;

                default:
                    $log->delete();
                    // Delete any overtime linked to this log
                    OverTimeRecords::where('intern_attendance_timelog_id', $log_id)->delete();
                    break;
            }

            DB::commit();

            return response()->json([
                'message' => 'Intern attendance log and related overtime deleted successfully.'
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