<?php

namespace App\Http\Controllers\AttendenceController;

use App\Http\Controllers\Controller;
use App\Models\AttendenceModels\AttendenceBreakTypes;
use App\Models\AttendenceModels\AttendenceDeviationReason;
use App\Models\AttendenceModels\AttendenceDeviationReasonType;
use App\Models\AttendenceModels\AttendenceDeviationRecord;
use Auth;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class AttendenceDeviationRecordController extends Controller
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

            $query = AttendenceDeviationRecord::query();
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
                'deviationRecord' => $deviationRecord
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
                'employee_attendance_record_id' => 'required|integer|exists:employee_attendance_records,employee_attendance_record_id',
                'employee_id' => 'required|integer|exists:employees,employee_id',
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
            $deviationRecord = AttendenceDeviationRecord::create(array_merge($data));
            return response()->json([
                'message' => 'Employees Attendence Deviation Record  Added SuccessFully.',
                'deviationRecord' => $deviationRecord
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
                'employee_attendance_deviation_record_id' => 'required|integer|exists:employee_attendance_deviation_records,employee_attendance_deviation_record_id',
            ]);
            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()], 422);
            }
            $deviationRecord = AttendenceDeviationReasonType::find($record_id);
            return response()->json([
                'message' => 'Employee Deviation Record Type Found',
                'deviationRecord' => $deviationRecord
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
                'employee_attendance_deviation_record_id' => $record_id
            ]);
            $rules = [
                'organization_id' => 'required|integer|exists:organizations,organization_id',
                'employee_attendance_deviation_record_id' => 'required|integer|exists:employee_attendance_deviation_records,employee_attendance_deviation_record_id',
                'employee_attendance_record_id' => 'nullable|integer|exists:employee_attendance_records,employee_attendance_record_id',
                'employee_id' => 'nullable|integer|exists:employees,employee_id',
                'organization_entity_id' => 'nullable|integer|exists:organization_entities,organization_entity_id',
                'deviation_reason_type_id' => 'nullable|integer|exists:organization_deviation_reason_types,organization_attendance_deviation_reason_type_id',
                'deviation_reason_id' => 'nullable|integer|exists:organization_deviation_reasons,organization_attendance_deviation_reason_id',
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
            $deviationRecord = AttendenceDeviationRecord::find($record_id);
            $deviationRecord->update($request->only([
                'employee_attendance_record_id',
                'employee_id',
                'organization_entity_id',
                'deviation_reason_type_id',
                'deviation_reason_id',
                'expected_time',
                'actual_time',
                'deviation_minutes',
                'reference_point',
                'remarks',
            ]));
            return response()->json([
                'message' => 'Employee Deviation record  Updated Successfully.',
                'deviationRecord' => $deviationRecord
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
                'employee_attendance_deviation_record_id' => $record_id
            ]);
            $validator = Validator::make($request->all(), [
                'organization_id' => 'required|integer|exists:organizations,organization_id',
                'employee_attendance_deviation_record_id' => 'required|integer|exists:employee_attendance_deviation_records,employee_attendance_deviation_record_id',
            ]);
            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()], 422);
            }
            $deviationRecord = AttendenceDeviationRecord::find($record_id);
            $deviationRecord->delete();
            return response()->json([
                'message' => 'Employee Attendence Deviation Record  Deleted Successfully'
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Something went wrong. Please try again later.',
                'details' => $e->getMessage()
            ], 500);
        }
    }



}
