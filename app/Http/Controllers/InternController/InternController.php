<?php

namespace App\Http\Controllers\InternController;

use App\Models\InterModel\Interns;
use Auth;
use Illuminate\Support\Facades\Validator;
use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class InternController extends Controller
{
    public function index(Request $request, $org_id)
    {
        try {
            $request->merge(['organization_id' => $org_id]);
            $validator = Validator::make($request->all(), [
                'organization_id' => 'required|integer|exists:organizations,organization_id',
            ]);
            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()], 422);
            }
            if ($request->input('mode') == 1) {

                 $interns = Interns::with(
                'address.city',
                'organization',
                'Mentor',
                'contact',
                'IntershipType',
                'organizationunit',
                'workshift',
                'Status',
                'departmentLocation.department',
            )->where('organization_id', $org_id)->get();

            $interns = $interns->filter(function ($emp) {
                return optional($emp->Status)->internship_status_name !== 'Exited';
            });

            if ($interns->isEmpty()) {
                return response()->json([
                    'message' => 'Intern not found.'
                ], 404);
            }

            $mappedEmployees = $interns->map(function ($emp) {

                $dob = $emp->date_of_birth ? Carbon::parse($emp->date_of_birth) : null;
                $internship_start_date = $emp->internship_start_date ? Carbon::parse($emp->internship_start_date) : null;

                $age = $dob ? $dob->age : null;
                $workingSince = $internship_start_date ? $internship_start_date->diff(Carbon::now()) : null;
                return array_merge([
                    'id' => $emp->intern_id ?? '',
                    'Intern_Code' => $emp->intern_code ?? '',
                    'Name' => trim(($emp->first_name ?? '') . ' ' . ($emp->middle_name ?? '') . ' ' . ($emp->last_name ?? '')),
                    'Department' => $emp->departmentLocation->first()?->department?->department_name ?? '',

                    'Gender' => $emp->gender ?? '',
                    'Date_of_Birth' => $emp->date_of_birth ?? '',
                    'Age' => $age,
                    'Marital_Status' => $emp->marital_status ?? '',
                    'Date_of_Joining' => $emp->internship_start_date
                        ? Carbon::parse($emp->internship_start_date)->format('d-M-Y')
                        : null,
                    'Working_Since' => $workingSince
                        ? $workingSince->y . ' years ' . $workingSince->m . ' months ' . $workingSince->d . ' days'
                        : null,
                    'Organization_Unit' => $emp->organizationunit->unit_name ?? '',
                    'Internship_Type' => $emp->IntershipType->employment_type_name ?? '',
                    'Internship_Status' => $emp->Status->internship_status_name ?? '',
                    'Work_Shift' => $emp->workshift->work_shift_name ?? '',
                    'Personal_Email' => $emp->contact->personal_email ?? '',
                    'Personal_Phone_No.' => $emp->contact->personal_phone_number ?? '',
                    'Location' => $emp->address?->city?->city_name ?? '',
                    'Reporting_Manager' => trim(($emp->Mentor->first_name ?? '') . ' ' . ($emp->Mentor->middle_name ?? '') . ' ' . ($emp->Mentor->last_name ?? '')),
                    'profile_image_url' => $emp->profile_image_url ?? ''

                ]);
            });

            return response()->json( $mappedEmployees);
                
            }







            $query = Interns::with('Education.degree', 'Mentor', 'Status')->where('organization_id', $org_id);
            $per = $request->input('per_page', 10);
            $search = $request->input('search');
            if ($search) {
                $query->where(function ($q) use ($search) {
                    $q->where('internship_type_name', 'like', "%{$search}%");
                });
            }
            $data = $query->orderBy('created_at', 'desc')->paginate($per);
            return response()->json([
                'message' => 'OK',
                'intership' => $data,
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
                    'message' => 'Unauthorized'
                ], 401);
            }

            $request->merge(['organization_id' => $org_id]);

            $validator = Validator::make($request->all(), [
                'organization_id' => 'required|integer|exists:organizations,organization_id',

                'organization_entity_id' => 'nullable|integer|exists:organization_entities,organization_entity_id',
                'intern_code' => [
                    'sometimes',
                    'nullable',
                    'string',
                    'max:10',
                    Rule::unique('interns', 'intern_code')
                        ->where(function ($query) use ($org_id) {
                            return $query->where('organization_id', $org_id);
                        }),
                ],
                'first_name' => 'nullable|string|max:30',
                'middle_name' => 'nullable|string|max:30',
                'last_name' => 'nullable|string|max:30',
                'date_of_birth' => 'nulllable|date',
                'gender' => 'nullable|in:Male,Female,Other',
                'marital_status' => 'nullable|in:Single,Married,Divorced,Widowed,Separated',
                'profile_image_url' => 'nullable|string|max:255',
                'internship_start_date' => 'nullable|date',
                'internship_end_date' => 'nullable|date',
                'stipend_amount' => 'nullable|numeric|between:0,9999999999.99',
                'organization_unit_id' => 'required|integer|exists:organization_units,organization_unit_id',
                'organization_department_location_id' => 'required|integer|exists:organization_department_locations,organization_department_location_id',
                'organization_internship_type_id' => 'required|integer|exists:organization_internship_types,organization_internship_type_id',
                'organization_internship_status_id' => 'required|integer|exists:organization_internship_statuses,organization_internship_status_id',
                'mentor_employee_id' => 'required|integer|exists:employees,employee_id',
                'organization_user_id' => 'required|integer|exists:organization_users,organization_user_id',

            ]);

            if ($validator->fails()) {
                return response()->json([
                    'errors' => $validator->errors()
                ], 422);
            }

            $data = $request->all();
            $intership = Interns::create($data);
            return response()->json([
                'message' => 'Organization Inter Successfully.',
                'intership' => $intership
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Something went wrong. Please try again later.',
                'details' => $e->getMessage()
            ], 500);
        }
    }

    public function show(Request $request, $org_id, $intern_id)
    {
        try {
            $user = Auth::guard('applicationusers')->user();
            $organizationIds = $user->Client->Organization->pluck('organization_id')->toArray();
            if (!in_array($org_id, $organizationIds)) {
                return response()->json([
                    'messages' => 'unauthorized'
                ], 401);

            }
            $request->merge(['organization_id' => $org_id, 'intern_id' => $intern_id]);
            $validator = Validator::make($request->all(), [
                'organization_id' => 'required|integer|exists:organizations,organization_id',
                'intern_id' => 'required|integer|exists:interns,intern_id',
            ]);
            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()], 422);
            }
            $intership = Interns::find($intern_id);
            return response()->json([

                'intership' => $intership
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Something went wrong. Please try again later.',
                'details' => $e->getMessage()
            ], 500);
        }
    }

    public function update(Request $request, $org_id, $intern_id)
    {
        try {
            $user = Auth::guard('applicationusers')->user();
            $organizationIds = $user->Client->Organization->pluck('organization_id')->toArray();
            if (!in_array($org_id, $organizationIds)) {
                return response()->json([
                    'messages' => 'unauthorized'
                ], 401);

            }
            $request->merge([
                'organization_id' => $org_id,
                'intern_id' => $intern_id
            ]);
            $rules = [
                'organization_id' => 'required|integer|exists:organizations,organization_id',
                'intern_id' => 'required|integer|exists:interns,intern_id',
                'first_name' => 'nullable|string|max:30',
                'middle_name' => 'nullable|string|max:30',
                'last_name' => 'nullable|string|max:30',
                'date_of_birth' => 'nulllable|date',
                'gender' => 'nullable|in:Male,Female,Other',
                'marital_status' => 'nullable|in:Single,Married,Divorced,Widowed,Separated',
                'profile_image_url' => 'nullable|string|max:255',
                'internship_start_date' => 'nullable|date',
                'internship_end_date' => 'nullable|date',
                'stipend_amount' => 'nullable|numeric|between:0,9999999999.99',
                'organization_unit_id' => 'required|integer|exists:organization_units,organization_unit_id',
                'organization_department_location_id' => 'required|integer|exists:organization_department_locations,organization_department_location_id',
                'organization_internship_type_id' => 'required|integer|exists:organization_internship_types,organization_internship_type_id',
                'organization_internship_status_id' => 'required|integer|exists:organization_internship_statuses,organization_internship_status_id',
                'mentor_employee_id' => 'required|integer|exists:employees,employee_id',
                'organization_user_id' => 'required|integer|exists:organization_users,organization_user_id',



            ];
            $validator = Validator::make($request->all(), $rules);
            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()], 422);
            }
            $intership = Interns::find($intern_id);
            $intership->update($request->only([
                'organization_id',
                'organization_entity_id',
                'intern_code',
                'first_name',
                'middle_name',
                'last_name',
                'date_of_birth',
                'gender',
                'marital_status',
                'profile_image_url',
                'organization_unit_id',
                'organization_department_location_id',
                'organization_internship_type_id',
                'organization_internship_status_id',
                'internship_start_date',
                'internship_end_date',
                'stipend_amount',
                'mentor_employee_id',
                'organization_user_id'
            ]));
            return response()->json([
                'intership' => $intership
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Something went wrong. Please try again later.',
                'details' => $e->getMessage()
            ], 500);
        }
    }

    public function destroy(Request $request, $org_id, $intern_id)
    {
        try {
            $user = Auth::guard('applicationusers')->user();
            $organizationIds = $user->Client->Organization->pluck('organization_id')->toArray();
            if (!in_array($org_id, $organizationIds)) {
                return response()->json([
                    'messages' => 'unauthorized'
                ], 401);
            }
            $request->merge(['organization_id' => $org_id, 'intern_id' => $intern_id]);
            $validator = Validator::make($request->all(), [
                'organization_id' => 'required|integer|exists:organizations,organization_id',
                'intern_id' => 'required|integer|exists:interns,intern_id',
            ]);
            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()], 422);
            }
            $intership = Interns::find($intern_id);
            $intership->delete();
            return response()->json([
                'message' => 'Organization Intern Deleted SuccessFully !'
            ], 200); // or just remove 200 — it's the default

        } catch (Exception $e) {
            if ($e instanceof \Illuminate\Database\QueryException && $e->errorInfo[1] == 1451) {
                // Foreign key constraint violation
                return response()->json([
                    'error' => 'Cannot delete Business Unit type because it is linked with other records. Please delete dependent records first.'
                ], 409); // 409 Conflict
            }

            // For other exceptions
            return response()->json([
                'error' => 'Failed to delete ownership type.',
                'exception' => $e->getMessage() // Optional: remove in production
            ], 500);
        }
    }




    public function getallFilterInter(Request $request, $org_id)
    {
        try {
            // Main employee data
            $interns = Interns::with(
                'address.city',
                'organization',
                'Mentor',
                'contact',
                'IntershipType',
                'organizationunit',
                'workshift',
                'Status',
                'departmentLocation.department',
            )->where('organization_id', $org_id)->get();

            $interns = $interns->filter(function ($emp) {
                return optional($emp->Status)->internship_status_name !== 'Exited';
            });

            if ($interns->isEmpty()) {
                return response()->json([
                    'message' => 'Intern not found.'
                ], 404);
            }

            $mappedEmployees = $interns->map(function ($emp) {

                $dob = $emp->date_of_birth ? Carbon::parse($emp->date_of_birth) : null;
                $internship_start_date = $emp->internship_start_date ? Carbon::parse($emp->internship_start_date) : null;

                $age = $dob ? $dob->age : null;
                $workingSince = $internship_start_date ? $internship_start_date->diff(Carbon::now()) : null;
                return array_merge([
                    'id' => $emp->intern_id ?? '',
                    'Intern_Code' => $emp->intern_code ?? '',
                    'Name' => trim(($emp->first_name ?? '') . ' ' . ($emp->middle_name ?? '') . ' ' . ($emp->last_name ?? '')),
                    'Department' => $emp->departmentLocation->first()?->department?->department_name ?? '',

                    'Gender' => $emp->gender ?? '',
                    'Date_of_Birth' => $emp->date_of_birth ?? '',
                    'Age' => $age,
                    'Marital_Status' => $emp->marital_status ?? '',
                    'Date_of_Joining' => $emp->internship_start_date
                        ? Carbon::parse($emp->internship_start_date)->format('d-M-Y')
                        : null,
                    'Working_Since' => $workingSince
                        ? $workingSince->y . ' years ' . $workingSince->m . ' months ' . $workingSince->d . ' days'
                        : null,
                    'Organization_Unit' => $emp->organizationunit->unit_name ?? '',
                    'Internship_Type' => $emp->IntershipType->employment_type_name ?? '',
                    'Internship_Status' => $emp->Status->internship_status_name ?? '',
                    'Work_Shift' => $emp->workshift->work_shift_name ?? '',
                    'Personal_Email' => $emp->contact->personal_email ?? '',
                    'Personal_Phone_No.' => $emp->contact->personal_phone_number ?? '',
                    'Location' => $emp->address?->city?->city_name ?? '',
                    'Reporting_Manager' => trim(($emp->Mentor->first_name ?? '') . ' ' . ($emp->Mentor->middle_name ?? '') . ' ' . ($emp->Mentor->last_name ?? '')),
                    'profile_image_url' => $emp->profile_image_url ?? ''

                ]);
            });

            return response()->json($mappedEmployees);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Something went wrong.',
                'details' => $e->getMessage()
            ], 500);
        }
    }


}
