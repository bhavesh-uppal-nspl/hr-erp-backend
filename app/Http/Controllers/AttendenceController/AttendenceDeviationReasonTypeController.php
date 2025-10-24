<?php

namespace App\Http\Controllers\AttendenceController;

use App\Http\Controllers\Controller;
use App\Models\AttendenceModels\AttendenceBreakTypes;
use App\Models\AttendenceModels\AttendenceDeviationReason;
use App\Models\AttendenceModels\AttendenceDeviationReasonType;
use Auth;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class AttendenceDeviationReasonTypeController extends Controller
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
            $query = AttendenceDeviationReasonType::query();

            // Filter by organization ID
            if (!empty($organizationId)) {
                $query->where('organization_id', $organizationId);
            }

            // Apply search filter
            if (!empty($search)) {
                $query->where(function ($q) use ($search) {
                    $q->where('deviation_reason_type_name', 'like', '%' . $search . '%')
                        ->orWhere('description', 'like', '%' . $search . '%');
                });
            }

            // If per_page=all → fetch all records
            if ($perPage === 'all') {
                $statusTypes = $query->get();
            }
            // ✅ If per_page is provided & numeric → paginate
            elseif (!empty($perPage) && is_numeric($perPage)) {
                $perPage = (int) $perPage;
                $statusTypes = $query->paginate($perPage, ['*'], 'page', $page);
            }
            // ✅ If per_page is NOT provided → fetch ALL
            else {
                $statusTypes = $query->get();
            }


            return response()->json([
                'message' => 'Attendance Status Types fetched successfully',
                'attendance_deviation_reason_type' => $statusTypes
            ]);
        } catch (\Exception $e) {
            \Log::error('Error fetching attendance status types: ' . $e->getMessage());
            return response()->json([
                'message' => 'Failed to fetch attendance status types'
            ], 500);
        }
    }


    public function store(Request $request, $org_id)
    {
        try {
            $request->merge(['organization_id' => $org_id]);
            $validator = Validator::make($request->all(), [
                'organization_id' => 'required|integer|exists:organizations,organization_id',
                'organization_entity_id' => 'nullable|integer|exists:organization_entities,organization_entity_id',
                'organization_configuration_template_id' => 'nullable|integer|exists:organization_configuration_templates,organization_configuration_template_id',
                'deviation_reason_type_name' => 'required|string|max:50',
                'is_active' => 'nullable|boolean',
                'created_at' => 'nullable|date',
                'updated_at' => 'nullable|date',

            ]);
            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()], 422);
            }
            $data = $request->all();
            $deviationReasonType = AttendenceDeviationReasonType::create(array_merge($data));
            return response()->json([
                'message' => 'Employees Attendence Deviation Reason Type Added SuccessFully.',
                'deviationReasonType' => $deviationReasonType
            ], 201);
        } catch (\Exception $e) {
            // Handle any other errors and return a response
            return response()->json([
                'error' => 'Something went wrong. Please try again later.',
                'details' => $e->getMessage()
            ], 500);
        }
    }

    public function show(Request $request, $org_id, $type_id)
    {
        try {
            $request->merge(['organization_id' => $org_id, 'organization_attendance_deviation_reason_type_id' => $type_id]);
            $validator = Validator::make($request->all(), [
                'organization_id' => 'required|integer|exists:organizations,organization_id',
                'organization_attendance_deviation_reason_type_id' => 'required|integer|exists:organization_attendance_deviation_reason_types,organization_attendance_deviation_reason_type_id',
            ]);
            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()], 422);
            }
            $deviationReasonType = AttendenceDeviationReasonType::find($type_id);
            return response()->json([
                'message' => 'Employee Deviation Reason Type Found',
                'deviationReasonType' => $deviationReasonType
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Something went wrong. Please try again later.',
                'details' => $e->getMessage()
            ], 500);
        }
    }

    public function update(Request $request, $org_id, $type_id)
    {
        try {
            $request->merge([
                'organization_id' => $org_id,
                'organization_attendance_deviation_reason_type_id' => $type_id
            ]);
            $rules = [
                'organization_id' => 'required|integer|exists:organizations,organization_id',
                'organization_attendance_deviation_reason_type_id' => 'required|integer|exists:organization_attendance_deviation_reason_types,organization_attendance_deviation_reason_type_id',
                'organization_entity_id' => 'nullable|integer|exists:organization_entities,organization_entity_id',
                'organization_configuration_template_id' => 'nullable|integer|exists:organization_configuration_templates,organization_configuration_template_id',
                'deviation_reason_type_name' => 'required|string|max:50',
                'is_active' => 'nullable|boolean',
                'created_at' => 'nullable|date',
                'updated_at' => 'nullable|date',
            ];
            $validator = Validator::make($request->all(), $rules);
            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()], 422);
            }
            $deviationReasonType = AttendenceDeviationReasonType::find($type_id);
            $deviationReasonType->update($request->only([
                'deviation_reason_type_name',
                'is_active',
            ]));
            return response()->json([
                'message' => 'Employee Deviation Reason Type Updated Successfully.',
                'deviationReasonType' => $deviationReasonType
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Something went wrong. Please try again later.',
                'details' => $e->getMessage()
            ], 500);
        }
    }

    public function destroy(Request $request, $org_id, $type_id)
    {
        try {
            $request->merge([
                'organization_id' => $org_id,
                'organization_attendance_deviation_reason_type_id' => $type_id
            ]);
            $validator = Validator::make($request->all(), [
                'organization_id' => 'required|integer|exists:organizations,organization_id',
                'organization_attendance_deviation_reason_type_id' => 'required|integer|exists:organization_attendance_deviation_reason_types,organization_attendance_deviation_reason_type_id',
            ]);
            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()], 422);
            }
            $deviationReasonType = AttendenceDeviationReasonType::find($type_id);
            AttendenceDeviationReason::where('organization_attendance_deviation_reason_type_id', $type_id)->delete();

            $deviationReasonType->delete();
            return response()->json([
                'message' => 'Employee Attendence Deviation Reason Type Deleted Successfully'
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Something went wrong. Please try again later.',
                'details' => $e->getMessage()
            ], 500);
        }
    }



}
