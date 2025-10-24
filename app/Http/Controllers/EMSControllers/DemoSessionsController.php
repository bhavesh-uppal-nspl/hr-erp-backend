<?php

namespace App\Http\Controllers\EMSControllers;

use App\Http\Controllers\Controller;
use App\Models\EMSModels\ClassAttendence;
use App\Models\EMSModels\DemoSessions;
use App\Models\EMSModels\FeeInstallments;
use App\Models\EMSModels\PlacementReferrals;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class DemoSessionsController extends Controller
{

    public function index(Request $request)
    {

        try {
            $perPage = $request->get('per_page', 10); // Keep as string for "all" comparison
            $page = (int) $request->get('page', 1);
            $search = $request->get('search');
            $organizationId = $request->get('organization_id');

            $query = DemoSessions::with('trainer', 'trainingProgram');

            // Filter by organization_id
            if (!empty($organizationId)) {
                $query->where('organization_id', $organizationId);
            }

            // Search filter
            if (!empty($search)) {
                $query->where(function ($q) use ($search) {
                    $q->where('demo_date', 'like', '%' . $search . '%');
                });
            }

            // Handle pagination or return all
            $demoSessions = ($perPage === 'all')
                ? $query->get()
                : $query->paginate((int) $perPage, ['*'], 'page', $page);

            return response()->json([
                'message' => 'Demo Session fetched successfully',
                'data' => $demoSessions
            ]);
        } catch (\Exception $e) {
            \Log::error('Error fetching Demo Session: ' . $e->getMessage());
            return response()->json(['message' => 'Failed to fetch Demo Session'], 500);
        }

    }


    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'organization_id' => 'required|integer|exists:organizations,organization_id',
            'organization_entity_id' => 'sometimes|nullable|integer|exists:organization_entities,organization_entity_id',
            'trainer_employee_id' => 'required|integer|exists:employees,employee_id',
            'organization_ems_training_program_id' => 'required|integer|exists:organization_ems_training_programs,organization_ems_training_program_id',

            'demo_date' => 'required|date',
            'start_time_ist' => 'required|date_format:H:i',
            'end_time_ist' => 'required|date_format:H:i|after:start_time_ist',
            'start_time_client' => 'required|date_format:H:i',
            'end_time_client' => 'required|date_format:H:i|after:start_time_client',

            'client_timezone' => 'required|string|max:100',

            'demo_notes' => 'nullable|string|max:255',

            'demo_mode' => 'required|in:online,offline,hybrid',
            'trainer_location' => 'required|in:office,home',

            'meeting_link' => 'nullable|string|max:255',
            'student_remarks' => 'nullable|string|max:255',
            'trainer_remarks' => 'nullable|string|max:255',
            'counsellor_remarks' => 'nullable|string|max:255',

            'status' => 'required|in:scheduled,completed,cancelled,postponed',
            'demo_duration_minutes' => 'nullable|integer|min:1|max:600',

        ], [
            'end_time_ist.after' => 'End time (IST) must be after start time (IST).',
            'end_time_client.after' => 'End time (Client) must be after start time (Client).',
        ]);


        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            $demoSession = DemoSessions::create($request->all());

            return response()->json([
                'success' => true,
                'message' => 'Demo Session created successfully.',
                'data' => $demoSession,
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to create Demo Session.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function show($id)
    {
        try {
            $demoSession = DemoSessions::with('trainer', 'trainingProgram')->findOrFail($id);

            return response()->json([
                'success' => true,
                'data' => $demoSession,
            ]);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Demo Session not found.',
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error fetching Demo Session.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }


    public function update(Request $request, $id)
    {
        try {
            $demoSession = DemoSessions::findOrFail($id);

            $validator = Validator::make($request->all(), [
                'organization_id' => 'sometimes|integer|exists:organizations,organization_id',
                'organization_entity_id' => 'sometimes|integer|exists:organization_entities,organization_entity_id',
                'trainer_employee_id' => 'sometimes|integer|exists:employees,employee_id',
                'organization_ems_training_program_id' => 'sometimes|integer|exists:organization_ems_training_programs,organization_ems_training_program_id',

                'demo_date' => 'sometimes|date',
                'start_time_ist' => 'sometimes|date_format:H:i',
                'end_time_ist' => 'sometimes|date_format:H:i|after:start_time_ist',
                'start_time_client' => 'sometimes|date_format:H:i',
                'end_time_client' => 'sometimes|date_format:H:i|after:start_time_client',

                'client_timezone' => 'sometimes|string|max:100',

                'demo_notes' => 'sometimes|nullable|string|max:255',

                'demo_mode' => 'sometimes|in:online,offline,hybrid',
                'trainer_location' => 'sometimes|in:office,home',

                'meeting_link' => 'sometimes|nullable|string|max:255',
                'student_remarks' => 'sometimes|nullable|string|max:255',
                'trainer_remarks' => 'sometimes|nullable|string|max:255',
                'counsellor_remarks' => 'sometimes|nullable|string|max:255',

                'status' => 'sometimes|in:scheduled,completed,cancelled,postponed',
                'demo_duration_minutes' => 'sometimes|integer|min:1|max:600',

            ], [
                'end_time_ist.after' => 'End time (IST) must be after start time (IST).',
                'end_time_client.after' => 'End time (Client) must be after start time (Client).',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'errors' => $validator->errors(),
                ], 422);
            }

            $demoSession->update($request->all());

            return response()->json([
                'success' => true,
                'message' => 'Demo Session updated successfully.',
                'data' => $demoSession,
            ]);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Demo Session not found.',
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update Demo Session.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function destroy($id)
    {
        try {
            $demoSession = DemoSessions::findOrFail($id);
            $demoSession->delete();

            return response()->json([
                'success' => true,
                'message' => 'Demo Session deleted successfully.',
            ]);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Demo Session not found.',
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete Demo Session.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
