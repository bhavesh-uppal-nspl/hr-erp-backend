<?php

namespace App\Http\Controllers\EMSControllers;

use App\Http\Controllers\Controller;
use App\Models\EMSModels\Student;
use App\Models\EMSModels\TrainingProgram;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class StudentsController extends Controller
{

    public function index(Request $request)
    {

        try {
            $perPage = $request->get('per_page', 10); // Keep as string for "all" comparison
            $page = (int) $request->get('page', 1);
            $search = $request->get('search');
            $organizationId = $request->get('organization_id');

            $query = Student::with('country', 'state', 'city');

            if (!empty($organizationId)) {
                $query->where('organization_id', $organizationId);
            }

            if (!empty($search)) {
                $query->where(function ($q) use ($search) {
                    $q->where('first_name', 'like', '%' . $search . '%')
                        ->orWhere('gender', 'like', '%' . $search . '%')
                        ->orWhere('certificate_name', 'like', '%' . $search . '%');
                });
            }

            $student = ($perPage === 'all')
                ? $query->get()
                : $query->paginate((int) $perPage, ['*'], 'page', $page);

            return response()->json([
                'message' => 'Student fetched successfully',
                'data' => $student
            ]);

        } catch (\Exception $e) {

            \Log::error('Error fetching Student: ' . $e->getMessage());
            return response()->json(['message' => 'Failed to fetch Student'], 500);
        }

    }


    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'organization_id' => 'required|integer|exists:organizations,organization_id',
            'organization_entity_id' => 'sometimes|nullable|integer|exists:organization_entities,organization_entity_id',
            'country_id' => 'sometimes|nullable|integer|exists:general_countries,general_country_id',
            'state_id' => 'sometimes|nullable|integer|exists:general_states,general_state_id',
            'city_id' => 'sometimes|nullable|integer|exists:general_cities,general_city_id',
            'first_name' => 'required|string|max:100',
            'last_name' => 'nullable|string|max:50',
            'gender' => 'nullable|in:Male,Female,Other',
            'date_of_birth' => 'nullable|date',
            'email' => [
                'required',
                'string',
                'max:100',
                Rule::unique('organization_ems_students')->where(function ($query) use ($request) {
                    return $query->where('organization_id', $request->organization_id);
                }),
            ],
            'phone' => [
                'required',
                'string',
                'max:20',
                Rule::unique('organization_ems_students')->where(function ($query) use ($request) {
                    return $query->where('organization_id', $request->organization_id);
                }),
            ],
            'alternate_phone' => 'nullable|string|max:20',
            'address' => 'nullable|string|max:255',
            'certificate_name' => 'nullable|string|max:150',
            'student_status' => 'nullable|in:Active,Completed,Dropped,On Hold',
            'remarks' => 'nullable|string',

            'profile_image_url' => 'nullable|file|mimes:jpg,jpeg,png|max:2048',
        ], [
            'email.unique' => 'This Email already exists for the selected organization.',
            'phone.unique' => 'This Phone already exists for the selected organization.',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

    
        $data = $validator->validated();

        if ($request->hasFile('profile_image_url') && $request->file('profile_image_url')->isValid()) {
            $file = $request->file('profile_image_url');
            $path = $file->store('students', 'public');
            $data['profile_image_url'] = basename($path);
        } else {
            $data['profile_image_url'] = $request->input('profile_image_url');
        }

        $uniqueStudentId = 'STU' . date('Ymd') . rand(1000, 9999);
        while (\DB::table('organization_ems_students')->where('student_id', $uniqueStudentId)->exists()) {
            $uniqueStudentId = 'STU' . date('Ymd') . rand(1000, 9999);
        }
        $data['student_id'] = $uniqueStudentId;

        if (empty($data['student_status'])) {
            $data['student_status'] = 'Active';
        }

        try {
            $student = Student::create($data);

            return response()->json([
                'success' => true,
                'message' => 'Student created successfully.',
                'data' => $student,
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to create student.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }


    public function show($id)
    {
        try {
            $student = Student::with('country', 'state', 'city')->findOrFail($id);

            return response()->json([
                'success' => true,
                'data' => $student,
            ]);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Student not found.',
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error fetching Student.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }


    public function update(Request $request, $id)
    {
        $student = Student::find($id);

        if (!$student) {
            return response()->json([
                'success' => false,
                'message' => 'Student not found.',
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'organization_id' => 'required|integer|exists:organizations,organization_id',
            'organization_entity_id' => 'sometimes|nullable|integer|exists:organization_entities,organization_entity_id',
            'country_id' => 'sometimes|nullable|integer|exists:general_countries,general_country_id',
            'state_id' => 'sometimes|nullable|integer|exists:general_states,general_state_id',
            'city_id' => 'sometimes|nullable|integer|exists:general_cities,general_city_id',

            'first_name' => 'required|string|max:100',
            'last_name' => 'nullable|string|max:50',
            'gender' => 'nullable|in:Male,Female,Other',
            'date_of_birth' => 'nullable|date',
            'email' => [
                'required',
                'string',
                'max:100',
                Rule::unique('organization_ems_students')
                    ->ignore($student->organization_ems_student_id, 'organization_ems_student_id')
                    ->where(function ($query) use ($request) {
                        return $query->where('organization_id', $request->organization_id);
                    }),
            ],
            'phone' => [
                'required',
                'string',
                'max:20',
                Rule::unique('organization_ems_students')
                    ->ignore($student->organization_ems_student_id, 'organization_ems_student_id')
                    ->where(function ($query) use ($request) {
                        return $query->where('organization_id', $request->organization_id);
                    }),
            ],
            'alternate_phone' => 'nullable|string|max:20',
            'address' => 'nullable|string|max:255',
            'certificate_name' => 'nullable|string|max:150',
            'student_status' => 'nullable|in:Active,Completed,Dropped,On Hold',
            'remarks' => 'nullable|string',

            'profile_image_url' => 'nullable|file|mimes:jpg,jpeg,png|max:2048',
        ], [
            'email.unique' => 'This Email already exists for the selected organization.',
            'phone.unique' => 'This Phone already exists for the selected organization.',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }


        $data = $request->except(['student_id']);

     
        if ($request->hasFile('profile_image_url') && $request->file('profile_image_url')->isValid()) {
            $file = $request->file('profile_image_url');

            if ($student->profile_image_url) {
                $oldFileName = basename($student->profile_image_url);
                Storage::disk('public')->delete('students/' . $oldFileName);
            }


            $path = $file->store('students', 'public');
            $data['profile_image_url'] = basename($path);
        } else {
        
            $data['profile_image_url'] = $student->profile_image_url;
        }

        try {
            $student->update($data);


            return response()->json([
                'success' => true,
                'message' => 'Student updated successfully.',
                'data' => $student,
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update student.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }


    public function destroy($id)
    {
        try {
            $student = Student::findOrFail($id);
            $student->delete();

            return response()->json([
                'success' => true,
                'message' => 'Program deleted successfully.',
            ]);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Program not found.',
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete Program.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

}
