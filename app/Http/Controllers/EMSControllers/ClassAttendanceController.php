<?php

namespace App\Http\Controllers\EMSControllers;

use App\Http\Controllers\Controller;
use App\Models\EMSModels\ClassAttendence;
use App\Models\EMSModels\FeeInstallments;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class ClassAttendanceController extends Controller
{

    public function index(Request $request)
    {

        try {
            $perPage = $request->get('per_page', 10); // Keep as string for "all" comparison
            $page = (int) $request->get('page', 1);
            $search = $request->get('search');
            $organizationId = $request->get('organization_id');

            $query = ClassAttendence::with('student', 'batchClass');

            // Filter by organization_id
            if (!empty($organizationId)) {
                $query->where('organization_id', $organizationId);
            }

            // Search filter
            if (!empty($search)) {
                $query->where(function ($q) use ($search) {
                    $q->where('remarks', 'like', '%' . $search . '%');
                });
            }

            // Handle pagination or return all
            $classAttendence = ($perPage === 'all')
                ? $query->get()
                : $query->paginate((int) $perPage, ['*'], 'page', $page);

            return response()->json([
                'message' => 'Class Attendence fetched successfully',
                'data' => $classAttendence
            ]);
        } catch (\Exception $e) {
            \Log::error('Error fetching FeeInstallments: ' . $e->getMessage());
            return response()->json(['message' => 'Failed to fetch Class Attendence'], 500);
        }

    }


    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'organization_id' => 'required|integer|exists:organizations,organization_id',
            'organization_entity_id' => 'sometimes|nullable|integer|exists:organization_entities,organization_entity_id',
            'organization_ems_batch_class_id' => 'required|integer|exists:organization_ems_batch_classes,organization_ems_batch_class_id',
            'organization_ems_student_id' => 'required|integer|exists:organization_ems_students,organization_ems_student_id',
            'attendance_status' => 'required|in:Present,Absent,Late,Excused',
            'remarks' => 'nullable|string|max:500'
        ]);


        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            $classAttendence = ClassAttendence::create($request->all());

            return response()->json([
                'success' => true,
                'message' => 'Class Attendence created successfully.',
                'data' => $classAttendence,
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to create Class Attendence.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function show($id)
    {
        try {
            $classAttendence = ClassAttendence::with('student', 'batchClass')->findOrFail($id);

            return response()->json([
                'success' => true,
                'data' => $classAttendence,
            ]);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Class Attendence not found.',
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error fetching Class Attendence.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }


    public function update(Request $request, $id)
    {
        try {
            $classAttendence = ClassAttendence::findOrFail($id);

            $validator = Validator::make($request->all(), [
                'organization_id' => 'sometimes|integer|exists:organizations,organization_id',
                'organization_entity_id' => 'sometimes|integer|exists:organization_entities,organization_entity_id',
                'organization_ems_batch_class_id' => 'sometimes|integer|exists:organization_ems_batch_classes,organization_ems_batch_class_id',
                'organization_ems_student_id' => 'sometimes|integer|exists:organization_ems_students,organization_ems_student_id',
                'attendance_status' => 'sometimes|in:Present,Absent,Late,Excused',
                'remarks' => 'nullable|string|max:500'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'errors' => $validator->errors(),
                ], 422);
            }

            $classAttendence->update($request->all());

            return response()->json([
                'success' => true,
                'message' => 'Class Attendence updated successfully.',
                'data' => $classAttendence,
            ]);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Class Attendence not found.',
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update Class Attendence.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function destroy($id)
    {
        try {
            $classAttendence = ClassAttendence::findOrFail($id);
            $classAttendence->delete();

            return response()->json([
                'success' => true,
                'message' => 'Class Attendence deleted successfully.',
            ]);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Class Attendence not found.',
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete Class Attendence.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
