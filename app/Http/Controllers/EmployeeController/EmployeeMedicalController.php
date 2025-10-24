<?php

namespace App\Http\Controllers\EmployeeController;
use App\Models\EmployeesModel\EmployeeMedical;
use Auth;
use Carbon\Carbon;
use Illuminate\Support\Facades\Validator;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class EmployeeMedicalController extends Controller
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
            $query = EmployeeMedical::where('organization_id', $org_id);
            $per = $request->input('per_page', 10);
            $search = $request->input('search');
            if ($search) {
                $query->where(function ($q) use ($search) {
                    $q->where('blood_group', 'like', "%{$search}%");
                    $q->where('allergies', 'like', "%{$search}%");
                    $q->where('diseases', 'like', "%{$search}%");
                    $q->where('disability_description', 'like', "%{$search}%");

                });
            }
            $data = $query->orderBy('created_at', 'desc')->paginate($per);
            return response()->json([
                'message' => 'OK',
                'Medical' => $data,
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
                'blood_group' => ['nullable', 'in:A+,A-,B+,B-,AB+,AB-,O+,O-'],
                'allergies' => ['nullable', 'string', 'max:255'],
                'diseases' => ['nullable', 'string', 'max:255'],
                'disability_status' => ['nullable', 'in:None,Physical,Mental,Both,Other'],
                'disability_description' => ['nullable', 'string', 'max:255'],
                'is_fit_for_duty' => ['nullable', 'boolean'],
                'last_health_check_date' => ['nullable', 'date'],
                'medical_notes' => ['nullable', 'string', 'max:255'],

            ]);
            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()], 422);
            }
            $data = $request->all();
            $Medical = EmployeeMedical::create(array_merge($data));
            return response()->json([
                'message' => 'Employee FMedical Detail  Added SuccessFully.',
                'Medical' => $Medical
            ], 201);
        } catch (\Exception $e) {
            // Handle any other errors and return a response
            return response()->json([
                'error' => 'Something went wrong. Please try again later.',
                'details' => $e->getMessage()
            ], 500);
        }
    }

    public function show(Request $request, $org_id, $medical_id)
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
                'employee_medical_id' => $medical_id
            ]);

            $validator = Validator::make($request->all(), [
                'organization_id' => 'required|integer|exists:organizations,organization_id',
                'employee_medical_id' => 'required|integer|exists:employee_medicals,employee_medical_id',
            ]);

            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()], 422);
            }

            $Medical = EmployeeMedical::find($medical_id);

            if (!$Medical) {
                return response()->json(['error' => 'Employee Medical not found.'], 404);
            }

            $member = $Medical->toArray();
            $member['last_health_check_date'] = $Medical->last_health_check_date ? Carbon::parse($Medical->last_health_check_date)->format('Y-m-d') : null;

            return response()->json([
                'message' => 'Employee Family Medical Found',
                'Medical' => $member
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Something went wrong. Please try again later.',
                'details' => $e->getMessage()
            ], 500);
        }
    }


    public function update(Request $request, $org_id, $medical_id)
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
                'employee_medical_id' => $medical_id
            ]);

            $rules = [
                'employee_medical_id' => 'sometimes|integer|exists:employee_medicals,employee_medical_id',
                'organization_id' => 'required|integer|exists:organizations,organization_id',
                'employee_id' => 'nuullable|integer|exists:employees,employee_id',
                'blood_group' => ['nullable', 'in:A+,A-,B+,B-,AB+,AB-,O+,O-'],
                'allergies' => ['nullable', 'string', 'max:255'],
                'diseases' => ['nullable', 'string', 'max:255'],
                'disability_status' => ['nullable', 'in:None,Physical,Mental,Both,Other'],
                'disability_description' => ['nullable', 'string', 'max:255'],
                'is_fit_for_duty' => ['nullable', 'boolean'],
                'last_health_check_date' => ['nullable', 'date'],
                'medical_notes' => ['nullable', 'string', 'max:255'],
            ];

            $validator = Validator::make($request->all(), $rules);

            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()], 422);
            }

            $Medical = EmployeeMedical::find($medical_id);
            $Medical->update($request->only([
                'employee_id',
                'blood_group',
                'allergies',
                'diseases',
                'disability_status',
                'disability_description',
                'is_fit_for_duty',
                'last_health_check_date',
                'medical_notes'
            ]));


            return response()->json([
                'message' => 'Employee Family Member  Updated Successfully.',
                'Merdical' => $Medical
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Something went wrong. Please try again later.',
                'details' => $e->getMessage()
            ], 500);
        }
    }

    public function destroy(Request $request, $org_id, $medical_id)
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
                'employee_medical_id' => $medical_id
            ]);
            $validator = Validator::make($request->all(), [
                'organization_id' => 'required|integer|exists:organizations,organization_id',
                'employee_medical_id' => 'sometimes|integer|exists:employee_medicals,employee_medical_id',
            ]);
            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()], 422);
            }
            $FamilyMember = EmployeeMedical::find($medical_id);
            $FamilyMember->delete();
            return response()->json([
                'message' => 'Employee Medical Deleted Successfully'
            ], 200);


        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Something went wrong. Please try again later.',
                'details' => $e->getMessage()
            ], 500);
        }
    }
}
