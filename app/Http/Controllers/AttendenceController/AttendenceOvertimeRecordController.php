<?php

namespace App\Http\Controllers\AttendenceController;
use App\Http\Controllers\Controller;

use App\Models\AttendenceModels\AttendenceOvertimeRecord;
use Auth;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class AttendenceOvertimeRecordController extends Controller
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

            $query = AttendenceOvertimeRecord::query();
            if (!empty($organizationId)) {
                $query->where('organization_id', $organizationId);
            }
            if (!empty($search)) {
                $query->where(function ($q) use ($search) {
                    $q->where('year', 'like', '%' . $search . '%');
                });
            }
            // Handle perPage = all
            if ($perPage === 'all') {
                $overtimeRecord = $query->get();
            } else {
                $perPage = (int) $perPage; // make sure it's integer
                $overtimeRecord = $query->paginate($perPage, ['*'], 'page', $page);
            }
            return response()->json([
                'message' => 'Attendance OverTime Record fetched successfully',
                'overtimeRecord' => $overtimeRecord
            ]);
        } catch (\Exception $e) {
            \Log::error('Error fetching Attendance Overtime Record: ' . $e->getMessage());
            return response()->json(['message' => 'Failed to fetch Attendance Overtime Record'], 500);
        }
    }

    public function store(Request $request, $org_id)
    {
        try {
            $request->merge(['organization_id' => $org_id]);
            $validator = Validator::make($request->all(), [
                'organization_id' => 'required|integer|exists:organizations,organization_id',
                'employee_id' => 'nullable|integer|exists:employees,employee_id',
                'organization_entity_id' => 'nullable|integer|exists:organization_entities,organization_entity_id',
                'employee_attendance_record_id' => 'nullable|integer|exists:employee_attendance_records,employee_attendance_record_id',
                'overtime_date' => 'nullable|date_format:Y-m-d',
                'start_time' => 'nullable|date_format:Y-m-d H:i:s',
                'end_time' => 'nullable|date_format:Y-m-d H:i:s|after:start_time',
                'overtime_minutes' => 'nullable|integer|min:1',
                'deviation_reason_id' => 'nullable|integer|exists:organization_attendance_deviation_reasons,organization_attendance_deviation_reason_id',
                'compensation_type' => 'nullable|in:Paid,CompOff,None',
                'is_approved' => 'nullable|boolean',
                'approved_by_employee_id' => 'nullable|integer|exists:employees,employee_id',
                'approved_at' => 'nullable|date_format:Y-m-d H:i:s',
                'remarks' => 'nullable|string|max:255',
                'created_at' => 'nullable|date',
                'updated_at' => 'nullable|date',
            ]);
            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()], 422);
            }
            $data = $request->all();
            $OvertimeRecord = AttendenceOvertimeRecord::create(array_merge($data));
            return response()->json([
                'message' => 'Employees Attendence overtime Record Added SuccessFully.',
                'OvertimeRecord' => $OvertimeRecord
            ], status: 201);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Something went wrong. Please try again later.',
                'details' => $e->getMessage()
            ], 500);
        }
    }

    public function show(Request $request, $org_id, $record_id)
    {
        try {
            $request->merge(['organization_id' => $org_id, 'employee_attendance_overtime_record_id' => $record_id]);
            $validator = Validator::make($request->all(), [
                'organization_id' => 'required|integer|exists:organizations,organization_id',
                'employee_attendance_overtime_record_id' => 'required|integer|exists:employee_attendance_overtime_records,employee_attendance_overtime_record_id',
            ]);
            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()], 422);
            }
            $overtimeRecord = AttendenceOvertimeRecord::find($record_id);
            return response()->json([
                'message' => 'Employee Attendence Overtime Record  Found',
                'overtimeRecord' => $overtimeRecord
            ], status: 200);

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
                'employee_attendance_overtime_record_id' => $record_id
            ]);
            $rules = [
                'organization_id' => 'required|integer|exists:organizations,organization_id',
                'employee_attendance_overtime_record_id' => 'required|integer|exists:employee_attendance_overtime_records,employee_attendance_overtime_record_id',
                'employee_id' => 'nullable|integer|exists:employees,employee_id',
                'organization_entity_id' => 'nullable|integer|exists:organization_entities,organization_entity_id',
                'employee_attendance_record_id' => 'nullable|integer|exists:employee_attendance_records,employee_attendance_record_id',
                'overtime_date' => 'nullable|date_format:Y-m-d',
                'start_time' => 'nullable|date_format:Y-m-d H:i:s',
                'end_time' => 'nullable|date_format:Y-m-d H:i:s|after:start_time',
                'overtime_minutes' => 'nullable|integer|min:1',
                'deviation_reason_id' => 'nullable|integer|exists:organization_attendance_deviation_reasons,organization_attendance_deviation_reason_id',
                'compensation_type' => 'nullable|in:Paid,CompOff,None',
                'is_approved' => 'nullable|boolean',
                'approved_by_employee_id' => 'nullable|integer|exists:employees,employee_id',
                'approved_at' => 'nullable|date_format:Y-m-d H:i:s',
                'remarks' => 'nullable|string|max:255',
                'created_at' => 'nullable|date',
                'updated_at' => 'nullable|date',
            ];
            $validator = Validator::make($request->all(), $rules);
            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()], 422);
            }
            $overtimeRecord = AttendenceOvertimeRecord::find($record_id);
            $overtimeRecord->update($request->only([
                'employee_id',
                'organization_entity_id',
                'employee_attendance_record_id',
                'overtime_date',
                'start_time',
                'end_time',
                'overtime_minutes',
                'deviation_reason_id',
                'compensation_type',
                'is_approved',
                'approved_by_employee_id',
                'approved_at',
                'remarks',

            ]));
            return response()->json([
                'message' => 'Employee Overtime record  Updated Successfully.',
                'overtimeRecord' => $overtimeRecord
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
                'employee_attendance_overtime_record_id' => $record_id
            ]);
            $validator = Validator::make($request->all(), [
                'organization_id' => 'required|integer|exists:organizations,organization_id',
                'employee_attendance_overtime_record_id' => 'required|integer|exists:employee_attendance_overtime_records,employee_attendance_overtime_record_id',
            ]);
            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()], 422);
            }
            $overtimeRecord = AttendenceOvertimeRecord::find($record_id);
            $overtimeRecord->delete();
            return response()->json([
                'message' => 'Employee Attendence Overtime Record Deleted Successfully'
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Something went wrong. Please try again later.',
                'details' => $e->getMessage()
            ], 500);
        }
    }



}
