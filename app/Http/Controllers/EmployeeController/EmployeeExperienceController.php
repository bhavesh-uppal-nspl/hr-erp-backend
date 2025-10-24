<?php

namespace App\Http\Controllers\EmployeeController;
use App\Models\EmployeesModel\EmployeeExperience;
use Auth;
use Carbon\Carbon;
use Illuminate\Support\Facades\Validator;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class EmployeeExperienceController extends Controller
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
            $query = EmployeeExperience::with('employee')->where('organization_id', $org_id);
            $per = $request->input('per_page', 10);
            $search = $request->input('search');
            if ($search) {
                $query->where(function ($q) use ($search) {
                    $q->where('employment_type', 'like', "%{$search}%");
                    $q->where('internship_compensation_type', 'like', "%{$search}%");
                });
            }
            $data = $query->orderBy('created_at', 'desc')->paginate($per);
            return response()->json([
                'message' => 'OK',
                'employeeExperience' => $data,
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
                'organization_name' => 'required|string|max:100',
                'experience_type' => 'nullable|in:Job,Internship,Freelance,Apprenticeship,Research,Entrepreneurship',
                'work_title' => 'nullable|string|max:100',
                'work_mode' => 'nullable|in:Full-Time,Part-Time,Hourly,Project-Based,Ad-hoc,Flexible',
                'compensation_status' => 'nullable|in:Paid,Unpaid',
                'compensation_payout_model' => 'nullable|in:Annual,Monthly,Weekly,Daily,Hourly,Per Project,One-Time,Commission-Based',
                'compensation_amount' => 'nullable|numeric|min:0|max:9999999999.99',
                'currency_code' => 'nullable|string|max:10',
                'start_date' => 'required|date',
                'end_date' => 'nullable|date|after_or_equal:start_date',
                'reporting_manager_name' => 'nullable|string|max:100',
                'reporting_manager_contact' => 'nullable|string|max:50',
                'location' => 'nullable|string|max:100',
                'description' => 'nullable|string|max:255',
                'is_verified' => 'nullable|boolean',
                'verified_by' => 'nullable|string|max:100',
                'verification_date' => 'nullable|date',
                'verification_notes' => 'nullable|string|min:10|max:255',
                'general_industry_id' => 'nullable|integer|exists:general_industries,general_industry_id',

            ]);
            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()], 422);
            }
            $data = $request->all();
            $Experience = EmployeeExperience::create(array_merge($data));
            return response()->json([
                'message' => 'Employee Experience  Added SuccessFully.',
                'Experience' => $Experience
            ], 201);
        } catch (\Exception $e) {
            // Handle any other errors and return a response
            return response()->json([
                'error' => 'Something went wrong. Please try again later.',
                'details' => $e->getMessage()
            ], 500);
        }
    }

    public function show(Request $request, $org_id, $experience_id)
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
                'employee_experience_id' => $experience_id
            ]);

            $validator = Validator::make($request->all(), [
                'organization_id' => 'required|integer|exists:organizations,organization_id',
                'employee_experience_id' => 'required|integer|exists:employee_experiences,employee_experience_id',
            ]);

            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()], 422);
            }

            $employeeExperience = EmployeeExperience::find($experience_id);

            if (!$employeeExperience) {
                return response()->json(['error' => 'Employee Experience not found.'], 404);
            }
            $experience = $employeeExperience->toArray();
            $experience['start_date'] = $employeeExperience->start_date ? Carbon::parse($employeeExperience->start_date)->format('Y-m-d') : null;
            $experience['end_date'] = $employeeExperience->end_date ? Carbon::parse($employeeExperience->end_date)->format('Y-m-d') : null;
            $experience['verification_date'] = $employeeExperience->verification_date ? Carbon::parse($employeeExperience->verification_date)->format('Y-m-d') : null;
            return response()->json([
                'message' => 'Employee Experience Found',
                'Experience' => $experience
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Something went wrong. Please try again later.',
                'details' => $e->getMessage()
            ], 500);
        }
    }


    public function update(Request $request, $org_id, $experience_id)
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
                'employee_experience_id' => $experience_id
            ]);

            $rules = [
                'organization_id' => 'required|integer|exists:organizations,organization_id',
                'employee_experience_id' => 'sometimes|integer|exists:employee_experiences,employee_experience_id',
                'employee_id' => 'sometimes|nullable|integer|exists:employees,employee_id',
                'organization_name' => 'required|string|max:100',
                'experience_type' => 'nullable|in:Job,Internship,Freelance,Apprenticeship,Research,Entrepreneurship',
                'work_title' => 'nullable|string|max:100',
                'work_mode' => 'nullable|in:Full-Time,Part-Time,Hourly,Project-Based,Ad-hoc,Flexible',
                'compensation_status' => 'nullable|in:Paid,Unpaid',
                'compensation_payout_model' => 'nullable|in:Annual,Monthly,Weekly,Daily,Hourly,Per Project,One-Time,Commission-Based',
                'compensation_amount' => 'nullable|numeric|min:0|max:9999999999.99',
                'currency_code' => 'nullable|string|max:10',
                'start_date' => 'required|date',
                'end_date' => 'nullable|date|after_or_equal:start_date',
                'reporting_manager_name' => 'nullable|string|max:100',
                'reporting_manager_contact' => 'nullable|string|max:50',
                'location' => 'nullable|string|max:100',
                'description' => 'nullable|string|max:255',
                'is_verified' => 'nullable|boolean',
                'verified_by' => 'nullable|string|max:100',
                'verification_date' => 'nullable|date',
                'verification_notes' => 'nullable|string|min:10|max:255',
                'general_industry_id' => 'nullable|integer|exists:general_industries,general_industry_id',

            ];

            $validator = Validator::make($request->all(), $rules);

            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()], 422);
            }

            $employeeExperience = EmployeeExperience::find($experience_id);
            $employeeExperience->update($request->only([
                'employee_id',
                'organization_name',
                'experience_type',
                'work_title',
                'work_mode',
                'compensation_status',
                'compensation_payout_model',
                'compensation_payout_model',
                'compensation_amount',
                'currency_code',
                'start_date',
                'end_date',
                'reporting_manager_name',
                'reporting_manager_contact',
                'location',
                'description',
                'is_verified',
                'verification_notes',
                'verification_date',
                'general_industry_id'

            ]));
            return response()->json([
                'message' => 'Employee Experience  Updated Successfully.',
                'Experience' => $employeeExperience
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Something went wrong. Please try again later.',
                'details' => $e->getMessage()
            ], 500);
        }
    }

    public function destroy(Request $request, $org_id, $experience_id)
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
                'employee_experience_id' => $experience_id
            ]);
            $validator = Validator::make($request->all(), [
                'organization_id' => 'required|integer|exists:organizations,organization_id',
                'employee_experience_id' => 'sometimes|integer|exists:employee_experiences,employee_experience_id',
            ]);
            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()], 422);
            }
            $employeeExperience = EmployeeExperience::find($experience_id);
            $employeeExperience->delete();
            return response()->json([
                'message' => 'Employee Experience Deleted Successfully'
            ], 200);


        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Something went wrong. Please try again later.',
                'details' => $e->getMessage()
            ], 500);
        }
    }
}
