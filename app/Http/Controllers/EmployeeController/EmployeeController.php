<?php

namespace App\Http\Controllers\EmployeeController;

use App\Models\EmployeesModel\EmployeeExit;
use App\Models\EmployeesModel\EmployeeLeaves;
use App\Models\OrganizationModel\Organization;
use App\Models\OrganizationModel\OrganizationDepartmentLocation;
use Carbon\Carbon;
use Auth;
use DB;
use Illuminate\Support\Facades\Validator;
use App\Http\Controllers\Controller;
use App\Models\EmployeesModel\Employees;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class EmployeeController extends Controller
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

            // ==============================
            // MODE 1 → Basic employee list
            // ==============================
            if ($request->input("mode") == 1) {
                $query = Employees::with(
                    'designation',
                    'employemnettype',
                    'contact',
                    'workshift',
                    'departmentLocation.department',
                    'employmentsatus',
                    'manager'
                )->where('organization_id', $org_id);

                $per = $request->input('per_page', 10);
                $search = $request->input('search');

                if ($search) {
                    $query->where(function ($q) use ($search) {
                        $q->where('first_name', 'like', "%{$search}%");
                    });
                }

                $data = $query->orderBy('created_at', 'desc')->get();

                return response()->json([
                    'message' => 'OK',
                    'employees' => $data,
                ]);
            }

            // ==========================================
            // ELSE → Detailed employee information mode
            // ==========================================
            $employees = Employees::with(
                'workmodel',
                'address.city',
                'organization',
                'manager',
                'contact',
                'designation',
                'employemnettype',
                'organizationunit',
                'workshift',
                'departmentLocation.department',
                'TodayLatestAttendance',
                'employmentsatus',
            )->where('organization_id', $org_id)->get();

            if ($employees->isEmpty()) {
                return response()->json([
                    'message' => 'Employee not found.'
                ], 404);
            }

            $mappedEmployees = $employees->map(function ($emp) {
                $dob = $emp->date_of_birth ? Carbon::parse($emp->date_of_birth) : null;
                $joiningDate = $emp->date_of_joining ? Carbon::parse($emp->date_of_joining) : null;

                $age = $dob ? $dob->age : null;
                $workingSince = $joiningDate ? $joiningDate->diff(Carbon::now()) : null;

                return [
                    'id' => $emp->employee_id ?? '',
                    'Employee_Code' => $emp->employee_code ?? '',
                    'Name' => trim(($emp->first_name ?? '') . ' ' . ($emp->middle_name ?? '') . ' ' . ($emp->last_name ?? '')),
                    'Department' => $emp->departmentLocation->first()?->department?->department_name ?? '',
                    'Designation' => $emp->designation->designation_name ?? '',
                    'Gender' => $emp->gender ?? '',
                 

                     'Date_of_Birth' => $emp->date_of_birth ? Carbon::parse($emp->date_of_birth)->format('Y-m-d') : null,
                    'Age' => $age,
                    'organization_user_id' => $emp->organization_user_id ?? '',
                    'Marital_Status' => $emp->marital_status ?? '',
                    'Date_of_Joining' => $emp->date_of_joining
                        ? Carbon::parse($emp->date_of_joining)->format('d-M-Y')
                        : null,
                    'Working_Since' => $workingSince
                        ? $workingSince->y . ' years ' . $workingSince->m . ' months ' . $workingSince->d . ' days'
                        : null,
                    'Organization_Unit' => $emp->organizationunit->unit_name ?? '',
                    'Employment_Type' => $emp->employemnettype->employment_type_name ?? '',
                    'Work_Model' => $emp->workmodel->work_model_name ?? '',
                    'Work_Shift' => $emp->workshift->work_shift_name ?? '',
                    'Personal_Email' => $emp->contact->personal_email ?? '',
                    'Personal_Phone_No.' => $emp->contact->personal_phone_number ?? '',
                    'Work_Email' => $emp->contact->work_email ?? '',
                    'Work_Phone_No' => $emp->contact->work_phone_number ?? '',
                    'Location' => $emp->address?->city?->city_name ?? '',
                    'Reporting_Manager' => trim(($emp->manager->first_name ?? '') . ' ' . ($emp->manager->middle_name ?? '') . ' ' . ($emp->manager->last_name ?? '')),
                    'profile_image_url' => $emp->profile_image_url ?? '',
                    'Status' => $emp->employmentsatus->employment_status_name?? ''
                ];
            });

            return response()->json($mappedEmployees);

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
            // add organization department location   
            $department = OrganizationDepartmentLocation::where('organization_department_id', $request->organization_department_id)
                ->where('organization_id', $org_id)
                ->first();

            $validator = Validator::make($request->all(), [
                'organization_id' => 'required|integer|exists:organizations,organization_id',
                'employee_code' => [
                    'required',
                    'string',
                    'max:10',
                    Rule::unique('employees')->where(function ($query) use ($org_id) {
                        return $query->where('organization_id', $org_id);
                    }),
                ],
                'first_name' => 'required|string|max:30',
                'middle_name' => 'nullable|string|max:30',
                'last_name' => 'nullable|string|max:30',
                'date_of_birth' => 'required|date|before:today',
                'gender' => 'required|in:Male,Female,Other',
                'marital_status' => 'required|in:Single,Married,Divorced,Widowed',
                'disability_flag' => 'nullable|boolean',
                'organization_unit_id' => 'nullable|integer|exists:organization_units,organization_unit_id',
                'organization_designation_id' => 'nullable|integer|exists:organization_designations,organization_designation_id',
                'organization_employment_type_id' => 'nullable|integer|exists:organization_employment_types,organization_employment_type_id',
                'organization_work_model_id' => 'nullable|integer|exists:organization_work_models,organization_work_model_id',
                'organization_work_shift_id' => 'nullable|integer|exists:organization_work_shifts,organization_work_shift_id',
                'organization_employment_status_id' => 'nullable|integer|exists:organization_employment_statuses,organization_employment_status_id',
                'date_of_joining' => 'nullable|date|before_or_equal:today',
                'reporting_manager_id' => 'nullable|integer|exists:employees,employee_id',
                'organization_user_id' => 'nullable|integer|exists:organization_users,organization_user_id'
            ]);
            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()], 422);
            }
            $data = $request->all();

            // store the image 

            if ($request->hasFile('profile_image_url')) {
                $img = $request->profile_image_url;
                $ext = $img->getClientOriginalExtension();
                $imageName = 'emp_savings' . time() . '.' . $ext;
                $img->move(public_path() . '/emp_img/', $imageName);
                $data['profile_image_url'] = asset('emp_img/' . $imageName);
            }

            $data['organization_department_location_id'] = $department->organization_department_location_id;

            $employees = Employees::create(array_merge($data));
            return response()->json([
                'message' => 'Employees  Added SuccessFully.',
                'employees' => $employees
            ], 201);
        } catch (\Exception $e) {
            // Handle any other errors and return a response
            return response()->json([
                'error' => 'Something went wrong. Please try again later.',
                'details' => $e->getMessage()
            ], 500);
        }
    }


    public function show(Request $request, $org_id, $employee_id)
    {
        try {

            $user = Auth::guard('applicationusers')->user();
            $organizationIds = $user->Client->Organization->pluck('organization_id')->toArray();
            if (!in_array($org_id, $organizationIds)) {
                return response()->json([
                    'messages' => 'Unauthenticated'
                ], 401);
            }
            $request->merge(['organization_id' => $org_id, 'employee_id' => $employee_id]);
            $validator = Validator::make($request->all(), [
                'organization_id' => 'required|integer|exists:organizations,organization_id',
                'employee_id' => 'required|integer|exists:employees,employee_id',

            ]);
            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()], 422);
            }
            $employees = Employees::find($employee_id);
            $employee = $employees->toArray();
            $employee['date_of_birth'] = Carbon::parse($employees->date_of_birth)->format('Y-m-d');
            $employee['date_of_joining'] = Carbon::parse($employees->date_of_joining)->format('Y-m-d');
            return response()->json([
                'message' => 'Organization Employee Found',
                'employee' => $employee
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Something went wrong. Please try again later.',
                'details' => $e->getMessage()
            ], 500);
        }
    }

    public function update(Request $request, $org_id, $employee_id)
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
                'employee_id' => $employee_id
            ]);

            $rules = [
                'organization_id' => 'required|integer|exists:organizations,organization_id',
                'employee_id' => 'required|integer|exists:employees,employee_id',

                'employee_code' => [
                    'sometimes',
                    'string',
                    'max:255',
                    Rule::unique('employees', 'employee_code')->ignore($employee_id, 'employee_id')
                ],
                'first_name' => 'sometimes|string|max:30',
                'middle_name' => 'sometimes|nullable|string|max:30',
                'last_name' => 'sometimes|string|max:30',
                'date_of_birth' => 'sometimes|date|before:today',
                'gender' => 'sometimes|in:Male,Female,Other',
                'marital_status' => 'sometimes|in:Single,Married,Divorced,Widowed',
                'disability_flag' => 'sometimes|boolean',
                'profile_image_url' => 'sometimes|nullable|file',
                'organization_unit_id' => 'nullable|integer|exists:organization_units,organization_unit_id',
                'organization_designation_id' => 'sometimes|integer|exists:organization_designations,organization_designation_id',
                'organization_employment_type_id' => 'sometimes|integer|exists:organization_employment_types,organization_employment_type_id',
                'organization_work_model_id' => 'sometimes|integer|exists:organization_work_models,organization_work_model_id',
                'organization_work_shift_id' => 'sometimes|nullable|integer|exists:organization_work_shifts,organization_work_shift_id',
                'organization_employment_status_id' => 'sometimes|integer|exists:organization_employment_statuses,organization_employment_status_id',
                'date_of_joining' => 'sometimes|nullable|date|before_or_equal:today',
                'organization_user_id' => 'nullable|integer|exists:organization_users,organization_user_id',
                'reporting_manager_id' => 'sometimes|nullable|integer|exists:employees,employee_id',
            ];

            $validator = Validator::make($request->all(), $rules);
            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()], 422);
            }

            $employee = Employees::find($employee_id);
            if (!$employee) {
                return response()->json([
                    'error' => 'Employee not found.'
                ], 404);
            }

            // Handle image upload
            if ($request->hasFile('profile_image_url')) {
                // Delete old image if exists
                if ($employee->profile_image_url) {
                    $oldPath = public_path(str_replace(asset('/'), '', $employee->profile_image_url));
                    if (file_exists($oldPath)) {
                        unlink($oldPath);
                    }
                }

                $img = $request->qr_code_img;
                $imageName = 'qr-savings-' . time() . '.' . $img->getClientOriginalExtension();
                $img->move(public_path('/qr_codes/'), $imageName);
                $data['qr_code_img'] = asset('qr_codes/' . $imageName);

            }

            $employee->update($request->only([
                'employee_code',
                'first_name',
                'middle_name',
                'last_name',
                'date_of_birth',
                'gender',
                'marital_status',
                'disability_flag',
                'profile_image_url',
                'date_of_joining',
                'organization_location_department_id',
                'organization_business_unit_id',
                'organization_business_division_id',
                'organization_designation_id',
                'organization_employment_type_id',
                'organization_work_model_id',
                'organization_work_shift_id',
                'organization_employment_status_id',
                'reporting_manager_id'
            ]));

            return response()->json([
                'message' => 'Employee data updated successfully.',
                'employee' => $employee
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Something went wrong. Please try again later.',
                'details' => $e->getMessage()
            ], 500);
        }
    }


    public function destroy(Request $request, $org_id, $employee_id)
    {
        try {
            $user = Auth::guard('applicationusers')->user();
            $organizationIds = $user->Client->Organization->pluck('organization_id')->toArray();
            if (!in_array($org_id, $organizationIds)) {
                return response()->json([
                    'messages' => 'Unauthenticated'
                ], 401);
            }
            $request->merge(['organization_id' => $org_id, 'employee_id' => $employee_id]);
            $validator = Validator::make($request->all(), [
                'organization_id' => 'required|integer|exists:organizations,organization_id',
                'employee_id' => 'required|integer|exists:employees,employee_id',
            ]);
            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()], 422);
            }
            $employee = Employees::find($employee_id);

            DB::transaction(function () use ($employee_id, $employee) {
                EmployeeExit::where('employee_id', $employee_id)->delete();
                EmployeeLeaves::where('employee_id', $employee_id)->delete();
                $employee->delete();
            });
            return response()->json([
                'message' => 'Employee Deleted Successfully'
            ], 200);


        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Something went wrong. Please try again later.',
                'details' => $e->getMessage()
            ], 500);
        }
    }

    public function indexv1(Request $request, $org_id)
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


            $query = Employees::with('designation', 'workshift', 'employmentsatus', 'workmodel', 'employemnettype', 'workshift', 'departmentLocation.department')->where('organization_id', $org_id);



            $per = $request->input('per_page', 10);
            $search = $request->input('search');
            if ($search) {
                $query->where(function ($q) use ($search) {
                    $q->where('first_name', 'like', "%{$search}%");
                });
            }
            $data = $query->orderBy('created_at', 'desc')->get();
            return response()->json($data);

        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Something went wrong. Please try again later.',
                'details' => $e->getMessage()
            ], 500);
        }

    }


    public function getAllEmployees(Request $request, $org_id)
    {

        $employee = Employees::with('designation', 'workshift', 'Education.degree', 'employmentsatus', 'workmodel', 'employemnettype', 'workshift', 'contact', 'address.ResidentialOwnerType', 'experience', 'family', 'departmentLocation.department', 'manager', 'TodayLatestAttendance')->where('organization_id', $org_id)->get();
        return response()->json($employee);

    }
}




