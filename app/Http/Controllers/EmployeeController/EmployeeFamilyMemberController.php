<?php

namespace App\Http\Controllers\EmployeeController;
use App\Models\EmployeesModel\EmployeeEducation;
use App\Models\EmployeesModel\EmployeeExit;
use App\Models\EmployeesModel\EmployeeFamilyMember;
use App\Models\EmployeesModel\EmployeeLanguage;
use Auth;
use Carbon\Carbon;
use Illuminate\Support\Facades\Validator;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class EmployeeFamilyMemberController extends Controller
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
            $query = EmployeeFamilyMember::where('organization_id', $org_id);
            $per = $request->input('per_page', 10);
            $search = $request->input('search');
            if ($search) {
                $query->where(function ($q) use ($search) {
                    $q->where('relationship', 'like', "%{$search}%");
                    $q->where('name', 'like', "%{$search}%");

                });
            }
            $data = $query->orderBy('created_at', 'desc')->paginate($per);
            return response()->json([
                'message' => 'OK',
                'FamilyMember' => $data,
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
                'employee_id' => 'nullable|integer|exists:employees,employee_id',
                'relationship' => 'nullable|in:Parent,Spouse,Sibling,Child,Inlaws,Grandparent,Other',
                'marital_status' => 'nullable|in:Married,Unmarried,Widowed,Divorced,Other',
                'current_status' => 'nullable|in:Studying,Working,Unemployed,Retired,Homemaker,Other',
                'education_details' => 'nullable|string|max:100',
                'occupation_details' => 'nullable|string|max:100',
                'name' => 'required|string|max:100',
                'email' => 'nullable|email|max:100',
                'description' => 'nullable|string|max:255',
                'date_of_birth' => 'nullable|date|before:today',
                'is_emergency_contact' => 'nullable|boolean',
                'phone' => 'nullable|string|max:20|regex:/^\d{10,20}$/',
                'is_dependent' => 'nullable|boolean',

            ]);
            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()], 422);
            }
            $data = $request->all();
            $FamilyMember = EmployeeFamilyMember::create(array_merge($data));
            return response()->json([
                'message' => 'Employee Family Member  Added SuccessFully.',
                'FamilyMember' => $FamilyMember
            ], 201);
        } catch (\Exception $e) {
            // Handle any other errors and return a response
            return response()->json([
                'error' => 'Something went wrong. Please try again later.',
                'details' => $e->getMessage()
            ], 500);
        }
    }

    public function show(Request $request, $org_id, $member_id)
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
                'employee_family_member_id' => $member_id
            ]);

            $validator = Validator::make($request->all(), [
                'organization_id' => 'required|integer|exists:organizations,organization_id',
                'employee_family_member_id' => 'required|integer|exists:employee_family_members,employee_family_member_id',
            ]);

            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()], 422);
            }

            $familymember = EmployeeFamilyMember::find($member_id);

            if (!$familymember) {
                return response()->json(['error' => 'Employee Education not found.'], 404);
            }

            $member = $familymember->toArray();
            $member['year'] = $familymember->year ? Carbon::parse($familymember->year)->format('Y') : null;

            return response()->json([
                'message' => 'Employee Family Member Found',
                'FamilyMember' => $member
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Something went wrong. Please try again later.',
                'details' => $e->getMessage()
            ], 500);
        }
    }


    public function update(Request $request, $org_id, $member_id)
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
                'employee_family_member_id' => $member_id
            ]);

            $rules = [
                'employee_family_member_id' => 'sometimes|integer|exists:employee_family_members,employee_family_member_id',
                'organization_id' => 'required|integer|exists:organizations,organization_id',
                'employee_id' => 'required|integer|exists:employees,employee_id',
                'relationship' => 'nullable|in:Parent,Spouse,Sibling,Child,Inlaws,Grandparent,Other',
                'marital_status' => 'nullable|in:Married,Unmarried,Widowed,Divorced,Other',
                'current_status' => 'nullable|in:Studying,Working,Unemployed,Retired,Homemaker,Other',
                'education_details' => 'nullable|string|max:100',
                'occupation_details' => 'nullable|string|max:100',
                'name' => 'required|string|max:100',
                'email' => 'nullable|email|max:100',
                'description' => 'nullable|string|max:255',
                'date_of_birth' => 'nullable|date|before:today',
                'is_emergency_contact' => 'nullable|boolean',
                'phone' => 'nullable|string|max:20|regex:/^\d{10,20}$/',
                'is_dependent' => 'nullable|boolean',
            ];

            $validator = Validator::make($request->all(), $rules);

            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()], 422);
            }

            $FamilyMember = EmployeeFamilyMember::find($member_id);
            $FamilyMember->update($request->only([
              'organization_id',
                'employee_id',
                'relationship',
                'marital_status',
                'current_status' ,
                'education_details',
                'occupation_details',
                'name',
                'email',
                'description',
                'date_of_birth',
                'is_emergency_contact',
                'phone',
                'is_dependent',
            ]));


            return response()->json([
                'message' => 'Employee Family Member  Updated Successfully.',
                'FamilyMember' => $FamilyMember
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Something went wrong. Please try again later.',
                'details' => $e->getMessage()
            ], 500);
        }
    }

    public function destroy(Request $request, $org_id, $member_id)
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
                'employee_family_member_id' => $member_id
            ]);
            $validator = Validator::make($request->all(), [
                'organization_id' => 'required|integer|exists:organizations,organization_id',
                'employee_family_member_id' => 'sometimes|integer|exists:employee_family_members,employee_family_member_id',
            ]);
            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()], 422);
            }
            $FamilyMember = EmployeeFamilyMember::find($member_id);
            $FamilyMember->delete();
            return response()->json([
                'message' => 'Employee Family Member Deleted Successfully'
            ], 200);


        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Something went wrong. Please try again later.',
                'details' => $e->getMessage()
            ], 500);
        }
    }
}
