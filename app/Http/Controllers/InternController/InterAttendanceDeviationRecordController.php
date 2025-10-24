<?php

namespace App\Http\Controllers\InternController;

use App\Http\Controllers\Controller;
use App\Models\InterModel\InternAttendanceDeviationRecord;
use Auth;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class InterAttendanceDeviationRecordController extends Controller
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
            $perPage = $request->get('per_page', 10);
            $page = (int) $request->get('page', 1);
            $search = $request->get('search');
            $organizationId = $request->get('organization_id');

            $query = InternAttendanceDeviationRecord::query();
            if (!empty($organizationId)) {
                $query->where('organization_id', $organizationId);
            }

            // Apply search filter
            if (!empty($search)) {
                $query->where(function ($q) use ($search) {
                    $q->where('reference_point', 'like', '%' . $search . '%');
                    $q->where('remarks', 'like', '%' . $search . '%');
                });
            }
            // Handle perPage = all
            if ($perPage === 'all') {
                $deviationRecord = $query->get();
            } else {
                $perPage = (int) $perPage; // make sure it's integer
                $deviationRecord = $query->paginate($perPage, ['*'], 'page', $page);
            }
            return response()->json([
                'message' => 'Attendance Deviation Record fetched successfully',
                'intership' => $deviationRecord
            ]);
        } catch (\Exception $e) {
            \Log::error('Error fetching Attendance Deviation Record: ' . $e->getMessage());
            return response()->json(['message' => 'Failed to fetch Attendance Deviation Record'], 500);
        }
    }

    public function store(Request $request, $org_id)
    {
        try {
            $request->merge(['organization_id' => $org_id]);
            $validator = Validator::make($request->all(), [
                'organization_id' => 'required|integer|exists:organizations,organization_id',
                'intern_attendance_timelog_id' => 'required|integer|exists:intern_attendance_records,intern_attendance_timelog_id',
                'intern_id' => 'required|integer|exists:interns,intern_id',
                'attendance_date' => 'nullable|date',
                'organization_entity_id' => 'required|integer|exists:organization_entities,organization_entity_id',
                'deviation_reason_type_id' => 'required|integer|exists:organization_deviation_reason_types,organization_attendance_deviation_reason_type_id',
                'deviation_reason_id' => 'required|integer|exists:organization_deviation_reasons,organization_attendance_deviation_reason_id',
                'expected_time' => 'nullable|date_format:Y-m-d H:i:s',
                'actual_time' => 'nullable|date_format:Y-m-d H:i:s|after_or_equal:expected_time',
                'deviation_minutes' => 'nullable|integer|min:0',
                'reference_point' => 'nullable|in:ClockIn,ClockOut,BreakStart,BreakEnd',
                'remarks' => 'nullable|string|max:255',
            ]);
            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()], 422);
            }
            $data = $request->all();
            $deviationRecord = InternAttendanceDeviationRecord::create(array_merge($data));
            return response()->json([
                'message' => 'Intern Attendence Deviation Record Added SuccessFully.',
                'intership' => $deviationRecord
            ], 201);
        } catch (\Exception $e) {
            // Handle any other errors and return a response
            return response()->json([
                'error' => 'Something went wrong. Please try again later.',
                'details' => $e->getMessage()
            ], 500);
        }
    }

    public function show(Request $request, $org_id, $record_id)
    {
        try {
            $request->merge(['organization_id' => $org_id, 'employee_attendance_deviation_record_id' => $record_id]);
            $validator = Validator::make($request->all(), [
                'organization_id' => 'required|integer|exists:organizations,organization_id',
                'intern_attendance_deviation_record_id' => 'required|integer|exists:intern_attendance_deviation_records,intern_attendance_deviation_record_id',
            ]);
            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()], 422);
            }
            $deviationRecord = InternAttendanceDeviationRecord::find($record_id);
            return response()->json([
                'message' => 'Intern Deviation Record Found',
                'intership' => $deviationRecord
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Something went wrong. Please try again later.',
                'details' => $e->getMessage()
            ], 500);
        }
    }

    public function update(Request $request, $org_id, $record_id)
    {
        try {
            $request->merge([
                'organization_id' => $org_id,
                'intern_attendance_deviation_record_id' => $record_id
            ]);
            $rules = [
                'organization_id' => 'required|integer|exists:organizations,organization_id',
                'intern_attendance_deviation_record_id' => 'required|integer|exists:intern_attendance_deviation_records,intern_attendance_deviation_record_id',
                'intern_attendance_timelog_id' => 'required|integer|exists:intern_attendance_records,intern_attendance_timelog_id',
                'intern_id' => 'required|integer|exists:interns,intern_id',
                'attendance_date' => 'nullable|date',
                'organization_entity_id' => 'required|integer|exists:organization_entities,organization_entity_id',
                'deviation_reason_type_id' => 'required|integer|exists:organization_deviation_reason_types,organization_attendance_deviation_reason_type_id',
                'deviation_reason_id' => 'required|integer|exists:organization_deviation_reasons,organization_attendance_deviation_reason_id',
                'expected_time' => 'nullable|date_format:Y-m-d H:i:s',
                'actual_time' => 'nullable|date_format:Y-m-d H:i:s|after_or_equal:expected_time',
                'deviation_minutes' => 'nullable|integer|min:0',
                'reference_point' => 'nullable|in:ClockIn,ClockOut,BreakStart,BreakEnd',
                'remarks' => 'nullable|string|max:255',
            ];
            $validator = Validator::make($request->all(), $rules);
            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()], 422);
            }
            $deviationRecord = InternAttendanceDeviationRecord::find($record_id);
            $deviationRecord->update($request->only([
                'organization_id',
                'organization_entity_id',
                'intern_id',
                'intern_attendance_timelog_id',
                'reference_point',
                'deviation_reason_type_id',
                'deviation_reason_id',
                'expected_time',
                'actual_time',
                'deviation_minutes',
                'remarks',
                'attendance_date'
            ]));
            return response()->json([
                'message' => 'Intern Deviation record  Updated Successfully.',
                'intership' => $deviationRecord
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Something went wrong. Please try again later.',
                'details' => $e->getMessage()
            ], 500);
        }
    }

    public function destroy(Request $request, $org_id, $record_id)
    {
        try {
            $request->merge([
                'organization_id' => $org_id,
                'intern_attendance_deviation_record_id' => $record_id
            ]);
            $validator = Validator::make($request->all(), [
                'organization_id' => 'required|integer|exists:organizations,organization_id',
                'intern_attendance_deviation_record_id' => 'required|integer|exists:intern_attendance_deviation_records,intern_attendance_deviation_record_id',
            ]);
            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()], 422);
            }
            $deviationRecord = InternAttendanceDeviationRecord::find($record_id);
            $deviationRecord->delete();
            return response()->json([
                'message' => 'Intern Attendence Deviation Record  Deleted Successfully'
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Something went wrong. Please try again later.',
                'details' => $e->getMessage()
            ], 500);
        }
    }

}
