<?php

namespace App\Http\Controllers\AttendenceController;

use App\Http\Controllers\Controller;
use App\Models\AttendenceModels\AttendenceBreakTypes;
use App\Models\AttendenceModels\AttendenceDeviationReason;
use Auth;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class AttendenceDeviationReasonController extends Controller
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
            $query = AttendenceDeviationReason::with('DeviationReasonType');

            // Filter by organization ID
            if (!empty($organizationId)) {
                $query->where('organization_id', $organizationId);
            }

            // Apply search filter
            if (!empty($search)) {
                $query->where(function ($q) use ($search) {
                    $q->where('attendance_deviation_reason_name', 'like', '%' . $search . '%')
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
                'message' => 'Attendance Deviation Reason  fetched successfully',
                'attendance_deviation_reason' => $statusTypes
            ]);
        } catch (\Exception $e) {
            \Log::error('Error fetching attendance Deviation Reason: ' . $e->getMessage());
            return response()->json([
                'message' => 'Failed to fetch attendance Deviation Reason'
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
                'organization_attendance_deviation_reason_type_id' => 'nullable|integer|exists:organization_attendance_deviation_reason_types,organization_attendance_deviation_reason_type_id',
                'attendance_deviation_reason_name' => 'required|string|max:100',
                'description' => 'nullable|string|max:255',
                'is_active' => 'nullable|boolean',
                'created_at' => 'nullable|date',
                'updated_at' => 'nullable|date',

            ]);
            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()], 422);
            }
            $data = $request->all();
            $deviationReason = AttendenceDeviationReason::create(array_merge($data));
            return response()->json([
                'message' => 'Employees Attendence Deviation Reason  Added SuccessFully.',
                'deviationReason' => $deviationReason
            ], 201);
        } catch (\Exception $e) {
            // Handle any other errors and return a response
            return response()->json([
                'error' => 'Something went wrong. Please try again later.',
                'details' => $e->getMessage()
            ], 500);
        }
    }

    public function show(Request $request, $org_id, $reason_id)
    {
        try {
            $request->merge(['organization_id' => $org_id, 'organization_attendance_deviation_reason_id' => $reason_id]);
            $validator = Validator::make($request->all(), [
                'organization_id' => 'required|integer|exists:organizations,organization_id',
                'organization_attendance_deviation_reason_id' => 'required|integer|exists:organization_attendance_deviation_reasons,organization_attendance_deviation_reason_id',

            ]);
            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()], 422);
            }
            $deviationReason = AttendenceDeviationReason::find($reason_id);
            $deviationReason->load('DeviationReasonType');
            return response()->json([
                'message' => 'Employee Deviation Reason Found',
                'deviationReason' => $deviationReason
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Something went wrong. Please try again later.',
                'details' => $e->getMessage()
            ], 500);
        }
    }

    public function update(Request $request, $org_id, $reason_id)
    {
        try {
            $request->merge([
                'organization_id' => $org_id,
                'organization_attendance_deviation_reason_id' => $reason_id
            ]);
            $rules = [
                'organization_id' => 'required|integer|exists:organizations,organization_id',
                'organization_attendance_deviation_reason_id' => 'required|integer|exists:organization_attendance_deviation_reasons,organization_attendance_deviation_reason_id',
                'organization_entity_id' => 'nullable|integer|exists:organization_entities,organization_entity_id',
                'organization_configuration_template_id' => 'nullable|integer|exists:organization_configuration_templates,organization_configuration_template_id',
                'organization_attendance_deviation_reason_type_id' => 'nullable|integer|exists:organization_attendance_deviation_reason_types,organization_attendance_deviation_reason_type_id',
                'attendance_deviation_reason_name' => 'nullable|string|max:100',
                'description' => 'nullable|string|max:255',
                'is_active' => 'nullable|boolean',
                'created_at' => 'nullable|date',
                'updated_at' => 'nullable|date',
            ];
            $validator = Validator::make($request->all(), $rules);
            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()], 422);
            }
            $deviationReason = AttendenceDeviationReason::find($reason_id);
            $deviationReason->update($request->only([
                'organization_attendance_deviation_reason_type_id',
                'description',
                'attendance_deviation_reason_name',
                'is_active',
            ]));
            return response()->json([
                'message' => 'Employee Deviation Reason Updated Successfully.',
                'deviationReason' => $deviationReason
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Something went wrong. Please try again later.',
                'details' => $e->getMessage()
            ], 500);
        }
    }

    public function destroy(Request $request, $org_id, $reason_id)
    {
        try {
            $request->merge([
                'organization_id' => $org_id,
                'organization_attendance_deviation_reason_id' => $reason_id
            ]);
            $validator = Validator::make($request->all(), [
                'organization_id' => 'required|integer|exists:organizations,organization_id',
                'organization_attendance_deviation_reason_id' => 'required|integer|exists:organization_attendance_deviation_reasons,organization_attendance_deviation_reason_id',
            ]);
            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()], 422);
            }
            $deviationReason = AttendenceDeviationReason::find($reason_id);
            $deviationReason->delete();
            return response()->json([
                'message' => 'Employee Attendence Deviation Reason Deleted Successfully'
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Something went wrong. Please try again later.',
                'details' => $e->getMessage()
            ], 500);
        }
    }



    public function getDeviationReasonsByType(Request $request, $org_id, $reason_type_id)
    {
        try {
            // Merge org_id and reason_type_id into request for validation
            $request->merge([
                'organization_id' => $org_id,
                'organization_attendance_deviation_reason_type_id' => $reason_type_id
            ]);

            // Validate request params
            $validator = Validator::make($request->all(), [
                'organization_id' => 'required|integer|exists:organizations,organization_id',
                'organization_attendance_deviation_reason_type_id' => 'required|integer|exists:organization_attendance_deviation_reason_types,organization_attendance_deviation_reason_type_id'
            ]);

            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()], 422);
            }

            // Fetch deviation reasons based on organization & reason type
            $deviationReasons = AttendenceDeviationReason::where('organization_id', $org_id)
                ->where('organization_attendance_deviation_reason_type_id', $reason_type_id)
                ->with('DeviationReasonType') // Load relation if needed
                ->get();

            // Check if reasons exist
            if ($deviationReasons->isEmpty()) {
                return response()->json([
                    'message' => 'No deviation reasons found for this organization and type.',
                    'data' => []
                ], 404);
            }

            return response()->json([
                'message' => 'Deviation reasons fetched successfully.',
                'data' => $deviationReasons
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Something went wrong. Please try again later.',
                'details' => $e->getMessage()
            ], 500);
        }
    }



}
