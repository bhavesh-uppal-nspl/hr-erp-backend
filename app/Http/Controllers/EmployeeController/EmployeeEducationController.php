<?php

namespace App\Http\Controllers\EmployeeController;
use App\Models\EmployeesModel\EmployeeEducation;
use App\Models\EmployeesModel\EmployeeExit;
use Auth;
use Carbon\Carbon;
use Illuminate\Support\Facades\Validator;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class EmployeeEducationController extends Controller
{
    public function index(Request $request, $org_id)
    {
        try {
            $user = Auth::guard('applicationusers')->user();
            $organizationIds = $user->Client->Organization->pluck('organization_id')->toArray();
            if (!in_array($org_id, $organizationIds)) {
                return response()->json([
                    'messages' => 'Unauthenticated'
                ], 401);
            }
            $request->merge(['organization_id' => $org_id]);
            $validator = Validator::make($request->all(), [
                'organization_id' => 'required|integer|exists:organizations,organization_id',
            ]);
            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()], 422);
            }
            $query = EmployeeEducation::with('employee', 'exitReason')->where('organization_id', $org_id);
            $per = $request->input('per_page', 10);
            $search = $request->input('search');
            if ($search) {
                $query->where(function ($q) use ($search) {
                    $q->where('institute_name', 'like', "%{$search}%");
                    $q->where('board_university_name', 'like', "%{$search}%");

                });
            }
            $data = $query->orderBy('created_at', 'desc')->paginate($per);
            return response()->json([
                'message' => 'OK',
                'employeeEducation' => $data,
            ]);

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
            $user = Auth::guard('applicationusers')->user();
            $organizationIds = $user->Client->Organization->pluck('organization_id')->toArray();
            if (!in_array($org_id, $organizationIds)) {
                return response()->json([
                    'messages' => 'Unauthenticated'
                ], 401);
            }
            $request->merge(['organization_id' => $org_id]);
            $validator = Validator::make($request->all(), [
                'organization_id' => 'required|integer|exists:organizations,organization_id',
                'employee_id' => 'required|integer|exists:employees,employee_id',
               
                'organization_education_level_id' => 'required|integer|exists:organization_education_levels,organization_education_level_id',
                'organization_education_degree_id' => 'nullable|integer|exists:organization_education_degrees,organization_education_degree_id',
                'organization_education_stream_id' => 'nullable|integer|exists:organization_education_streams,organization_education_stream_id',
                'organization_education_level_degree_stream_id' => 'nullable|integer|exists:organization_education_level_degree_streams,organization_education_level_degree_stream_id',

                'institute_name' => 'required|string|max:255',
                'board_university_name' => 'nullable|string|max:255',

                'marks_percentage' => 'nullable|numeric|min:0|max:100',
                'year_of_passing' => 'nullable|digits:4|integer|min:1900|max:' . date('Y'),

                'is_pursuing' => 'nullable|boolean',
                'is_active' => 'nullable|boolean',

            ]);
            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()], 422);
            }
            $data = $request->all();
            $employeeEducation = EmployeeEducation::create(array_merge($data));
            return response()->json([
                'message' => 'Employee Education  Added SuccessFully.',
                'employeeEduaction' => $employeeEducation
            ], 201);
        } catch (\Exception $e) {
            // Handle any other errors and return a response
            return response()->json([
                'error' => 'Something went wrong. Please try again later.',
                'details' => $e->getMessage()
            ], 500);
        }
    }

    public function show(Request $request, $org_id, $education_id)
    {
        try {
            $user = Auth::guard('applicationusers')->user();
            $organizationIds = $user->Client->Organization->pluck('organization_id')->toArray();
            if (!in_array($org_id, $organizationIds)) {
                return response()->json([
                    'messages' => 'Unauthenticated'
                ], 401);
            }
            $request->merge([
                'organization_id' => $org_id,
                'employee_education_id' => $education_id
            ]);

            $validator = Validator::make($request->all(), [
                'organization_id' => 'required|integer|exists:organizations,organization_id',
                'employee_education_id' => 'required|integer|exists:employee_educations,employee_education_id',
            ]);

            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()], 422);
            }

            $employeeEducation = EmployeeEducation::find($education_id);

            if (!$employeeEducation) {
                return response()->json(['error' => 'Employee Education not found.'], 404);
            }

               $education = $employeeEducation->toArray();
            $education['year_of_passing'] = $employeeEducation->year_of_passing ? Carbon::parse($employeeEducation->year_of_passing)->format('Y') : null;
       
            return response()->json([
                'message' => 'Employee Education Found',
                'employeeEducation' => $education
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Something went wrong. Please try again later.',
                'details' => $e->getMessage()
            ], 500);
        }
    }


    public function update(Request $request, $org_id, $education_id)
    {
        try {
            $user = Auth::guard('applicationusers')->user();
            $organizationIds = $user->Client->Organization->pluck('organization_id')->toArray();
            if (!in_array($org_id, $organizationIds)) {
                return response()->json([
                    'messages' => 'Unauthenticated'
                ], 401);
            }
            $request->merge([
                'organization_id' => $org_id,
                'employee_education_id' => $education_id
            ]);

            $rules = [
                'organization_id' => 'required|integer|exists:organizations,organization_id',
                'employee_id' => 'sometimes|nullable|integer|exists:employees,employee_id',
                'employee_education_id' => 'sometimes|integer|exists:employee_educations,employee_education_id',
             
                'organization_education_level_id' => 'nullable|integer|exists:organization_education_levels,organization_education_level_id',
                'organization_education_degree_id' => 'nullable|integer|exists:organization_education_degrees,organization_education_degree_id',
                'organization_education_stream_id' => 'nullable|integer|exists:organization_education_streams,organization_education_stream_id',
                'organization_education_level_degree_stream_id' => 'nullable|integer|exists:organization_education_level_degree_streams,organization_education_level_degree_stream_id',

                'institute_name' => 'nullable|string|max:255',
                'board_university_name' => 'nullable|string|max:255',

                'marks_percentage' => 'nullable|numeric|min:0|max:100',
                'year_of_passing' => 'nullable|digits:4|integer|min:1900|max:' . date('Y'),

                'is_pursuing' => 'nullable|boolean',
                'is_active' => 'nullable|boolean',
            ];

            $validator = Validator::make($request->all(), $rules);

            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()], 422);
            }

            $employeeEduaction = EmployeeEducation::find($education_id);
            $employeeEduaction->update($request->only([
                'employee_id',
                'organization_education_level_id',
                'organization_education_degree_id',
                'organization_education_stream_id',
                'organization_education_level_degree_stream_id',
                'organization_employment_exit_reason_id',
                'institute_name',
                'board_university_name',
                'marks_percentage',
                'is_pursuing',
                'year_of_passing',
                'is_active'
            ]));


            return response()->json([
                'message' => 'Employee Education  Updated Successfully.',
                'employeeEducation' => $employeeEduaction
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Something went wrong. Please try again later.',
                'details' => $e->getMessage()
            ], 500);
        }
    }

    public function destroy(Request $request, $org_id, $education_id)
    {
        try {
            $user = Auth::guard('applicationusers')->user();
            $organizationIds = $user->Client->Organization->pluck('organization_id')->toArray();
            if (!in_array($org_id, $organizationIds)) {
                return response()->json([
                    'messages' => 'Unauthenticated'
                ], 401);
            }
            $request->merge([
                'organization_id' => $org_id,
                  'employee_education_id' => $education_id
            ]);
            $validator = Validator::make($request->all(), [
                'organization_id' => 'required|integer|exists:organizations,organization_id',
                 'employee_education_id' => 'sometimes|integer|exists:employee_educations,employee_education_id',
            ]);
            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()], 422);
            }
            $employeeEducation = EmployeeEducation::find($education_id);
            $employeeEducation->delete();
            return response()->json([
                'message' => 'Employee Education Deleted Successfully'
            ], 200);


        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Something went wrong. Please try again later.',
                'details' => $e->getMessage()
            ], 500);
        }
    }
}
