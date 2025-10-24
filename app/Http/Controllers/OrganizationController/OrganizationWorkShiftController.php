<?php

namespace App\Http\Controllers\OrganizationController;

use App\Models\EmployeesModel\Employees;
use App\Models\InterModel\Interns;
use Illuminate\Support\Facades\Validator;
use App\Http\Controllers\Controller;
use App\Models\OrganizationModel\Organization;
use App\Models\OrganizationModel\OrganizationWorkShift;
use Exception;
use Illuminate\Http\Request;
use Auth;
use Illuminate\Validation\Rule;

class OrganizationWorkShiftController extends Controller
{

    public function index(Request $request, $org_id)
    {
        try {
            $user = Auth::guard('applicationusers')->user();
            $organizationIds = $user->Client->Organization->pluck('organization_id')->toArray();
            if (!in_array($org_id, $organizationIds)) {
                return response()->json([
                    'messages' => 'unauthorized'
                ], 401);

            }
            $request->merge(['organization_id' => $org_id]);
            $validator = Validator::make($request->all(), [
                'organization_id' => 'required|integer|exists:organizations,organization_id',
            ]);
            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()], 422);
            }


            if ($request->input('mode') == 1) {
                $workshift = OrganizationWorkShift::with('location', 'shiftType')->where('organization_id', $org_id)->get();

                if ($workshift->isEmpty()) {
                    return response()->json([
                        'message' => 'workshift not found.'
                    ], 404);
                }
                $mappedWorkshift = $workshift->map(function ($dep) {
                    return [
                        'work_shift_name'=>$dep->work_shift_name,
                        'work_shift_start_time' => $dep->work_shift_start_time ?? '',
                        'work_shift_end_time' => $dep->work_shift_end_time ?? '',
                        'break_duration_minutes' => $dep->break_duration_minutes ?? '',
                        'work_duration_minutes' => $dep->work_duration_minutes ?? '',
                        'location' => $dep->location->location_name  ?? '',
                        'work_shift_type' => $dep->shiftType->work_shift_type_name  ?? '',
                    ];
                });
                return response()->json($mappedWorkshift);
            }
            
            $query = OrganizationWorkShift::with(['location', 'shiftType'])->where('organization_id', $org_id);
            $per = $request->input('per_page', 999);
            $search = $request->input('search');
            if ($search) {
                $query->where(function ($q) use ($search) {
                    $q->where('work_shift_name', 'like', "%{$search}%");
                });
            }
            $data = $query->orderBy('created_at', 'desc')->paginate($per);
            return response()->json([
                'message' => 'OK',
                'workshifts' => $data,

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
                    'messages' => 'unauthorized'
                ], 401);

            }
            $request->merge(['organization_id' => $org_id]);
            $validator = Validator::make($request->all(), [
                'organization_id' => 'required|integer|exists:organizations,organization_id',
                'organization_work_shift_type_id' => 'required|integer|exists:organization_work_shift_types,organization_work_shift_type_id',
                'organization_location_id' => 'required|integer|exists:organization_locations,organization_location_id',
                'work_shift_name' => [
                    'required',
                    'string',
                    'max:30',
                    Rule::unique('organization_work_shifts')->where(function ($query) use ($org_id) {
                        return $query->where('organization_id', $org_id);
                    }),
                ],
                'work_shift_start_time' => 'required|date_format:H:i',
                'work_shift_end_time' => 'required|date_format:H:i|after:work_shift_start_time',
                'break_duration_minutes' => 'nullable|integer|min:0|max:999',
                'work_duration_minutes' => 'nullable|integer|min:0|max:24860',
                'is_active' => 'nullable|boolean',
            ]);
            // Return validation errors if any
            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()], 422);
            }
            $data = $request->all();
            $workshift = OrganizationWorkShift::create($data);
            return response()->json([
                'message' => 'Organization work shift added successfullly.',
                'workshift' => $workshift
            ], 201);
        } catch (\Exception $e) {
            // Handle any other errors and return a response
            return response()->json([
                'error' => 'Something went wrong. Please try again later.',
                'details' => $e->getMessage()
            ], 500);
        }
    }

    public function show(Request $request, $org_id, $work_shift_id)
    {
        try {
            $user = Auth::guard('applicationusers')->user();
            $organizationIds = $user->Client->Organization->pluck('organization_id')->toArray();
            if (!in_array($org_id, $organizationIds)) {
                return response()->json([
                    'messages' => 'unauthorized'
                ], 401);

            }
            // Merge org_id from route into request for validation
            $request->merge(['organization_id' => $org_id, 'organization_work_shift_id' => $work_shift_id]);
            $validator = Validator::make($request->all(), [
                'organization_id' => 'required|integer|exists:organizations,organization_id',
                'organization_work_shift_id' => 'required|integer|exists:organization_work_shifts,organization_work_shift_id'
            ]);
            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()], 422);
            }
            $workshift = OrganizationWorkShift::find($work_shift_id);
            return response()->json([
                'message' => "Orgaization work shift  found",
                'workshift' => $workshift
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Something went wrong. Please try again later.',
                'details' => $e->getMessage()
            ], 500);
        }
    }

    public function update(Request $request, $org_id, $work_shift_id)
    {
        try {
            $user = Auth::guard('applicationusers')->user();
            $organizationIds = $user->Client->Organization->pluck('organization_id')->toArray();
            if (!in_array($org_id, $organizationIds)) {
                return response()->json([
                    'messages' => 'unauthorized'
                ], 401);

            }
            $request->merge(['organization_id' => $org_id, 'organization_work_shift_id' => $work_shift_id]);
            $rules = [
                'organization_id' => 'required|integer|exists:organizations,organization_id',
                'organization_work_shift_type_id' => 'sometimes|integer|exists:organization_work_shift_types,organization_work_shift_type_id',
                'organization_work_shift_id' => 'required|integer|exists:organization_work_shifts,organization_work_shift_id',
                'organization_location_id' => 'sometimes|integer|exists:organization_locations,organization_location_id',
                'work_shift_name' => [
                    'sometimes',
                    'string',
                    'max:30',
                    Rule::unique('organization_work_shifts', 'work_shift_name')
                        ->where(function ($query) use ($org_id) {
                            return $query->where('organization_id', $org_id);
                        })
                        ->ignore($work_shift_id, 'organization_work_shift_id')
                ],
                'work_shift_start_time' => 'sometimes|date_format:H:i',
                'work_shift_end_time' => 'sometimes|date_format:H:i|after:work_shift_start_time',
                'break_duration_minutes' => 'sometimes|nullable|integer|min:0|max:999',
                'work_duration_minutes' => 'nullable|integer|min:0|max:24860',
                'is_active' => 'sometimes|nullable|boolean',
            ];

            // Run validation
            $validator = Validator::make($request->all(), $rules);

            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()], 422);
            }
            $workshift = OrganizationWorkShift::find($work_shift_id);
            $workshift->update($request->only([
                'organization_location_id',
                'work_shift_name',
                'work_shift_start_time',
                'work_shift_end_time',
                'break_duration_minutes',
                'work_duration_minutes',
                'is_active',
                'organization_work_shift_type_id'
            ]));

            return response()->json([
                'message' => 'Organization work shift  updated successfully.',
                'workshift' => $workshift
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Something went wrong. Please try again later.',
                'details' => $e->getMessage()
            ], 500);
        }
    }

    public function destroy(Request $request, $org_id, $work_shift_id)
    {
        try {
            $user = Auth::guard('applicationusers')->user();
            $organizationIds = $user->Client->Organization->pluck('organization_id')->toArray();
            if (!in_array($org_id, $organizationIds)) {
                return response()->json([
                    'messages' => 'unauthorized'
                ], 401);

            }
            $request->merge(['organization_id' => $org_id, 'organization_work_shift_id' => $work_shift_id]);
            $validator = Validator::make($request->all(), [
                'organization_id' => 'required|integer|exists:organizations,organization_id',
                'organization_work_shift_id' => 'required|integer|exists:organization_work_shifts,organization_work_shift_id',

            ]);
            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()], 422);
            }
            $workshift = OrganizationWorkShift::find($work_shift_id);
            $workshift->delete();
            return response()->json([
                'message' => 'Organization work shift  Deleted Successfully'
            ], 200); // or just remove 200 — it's the default


        } catch (Exception $e) {
            if ($e instanceof \Illuminate\Database\QueryException && $e->errorInfo[1] == 1451) {
                // Foreign key constraint violation
                return response()->json([
                    'error' => 'Cannot delete shift type because it is linked with other records. Please delete dependent records first.'
                ], 409); // 409 Conflict
            }

            // For other exceptions
            return response()->json([
                'error' => 'Failed to delete work shift .',
                'exception' => $e->getMessage() // Optional: remove in production
            ], 500);
        }
    }



    public function getEmployeeWorkShift(Request $request, $org_id, $employee_id)
    {
        try {
            // Authenticate the logged-in user
            $user = Auth::guard('applicationusers')->user();
            $organizationIds = $user->Client->Organization->pluck('organization_id')->toArray();

            // Check if the organization belongs to the logged-in user
            if (!in_array($org_id, $organizationIds)) {
                return response()->json([
                    'message' => 'unauthorized'
                ], 401);
            }

            // Merge org_id and employee_id for validation
            $request->merge([
                'organization_id' => $org_id,
                'employee_id' => $employee_id
            ]);

            // Validate org_id & employee_id
            $validator = Validator::make($request->all(), [
                'organization_id' => 'required|integer|exists:organizations,organization_id',
                'employee_id' => 'required|integer|exists:employees,employee_id'
            ]);

            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()], 422);
            }

            // Fetch the employee with related work shift
            $employee = Employees::with('workShift')
                ->where('employee_id', $employee_id)
                ->where('organization_id', $org_id)
                ->first();

            if (!$employee) {
                return response()->json([
                    'message' => 'Employee not found in this organization.'
                ], 404);
            }

            // Check if the employee has a work shift assigned
            if (!$employee->organization_work_shift_id) {
                return response()->json([
                    'message' => 'This employee does not have any assigned work shift.'
                ], 404);
            }

            // Fetch the work shift details
            $workshift = OrganizationWorkShift::find($employee->organization_work_shift_id);

            if (!$workshift) {
                return response()->json([
                    'message' => 'Work shift details not found.'
                ], 404);
            }

            return response()->json([
                'message' => 'Employee work shift details found successfully.',
                'employee_id' => $employee->employee_id,
                'employee_name' => $employee->employee_name ?? null,
                'workshift' => $workshift
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Something went wrong. Please try again later.',
                'details' => $e->getMessage()
            ], 500);
        }
    }




        public function getInternWorkShift(Request $request, $org_id, $intern_id)
    {
        try {
            // Authenticate the logged-in user
            $user = Auth::guard('applicationusers')->user();
            $organizationIds = $user->Client->Organization->pluck('organization_id')->toArray();

            // Check if the organization belongs to the logged-in user
            if (!in_array($org_id, $organizationIds)) {
                return response()->json([
                    'message' => 'unauthorized'
                ], 401);
            }

            // Merge org_id and employee_id for validation
            $request->merge([
                'organization_id' => $org_id,
                'intern_id' => $intern_id
            ]);

            // Validate org_id & employee_id
            $validator = Validator::make($request->all(), [
                'organization_id' => 'required|integer|exists:organizations,organization_id',
                'intern_id' => 'required|integer|exists:interns,intern_id'
            ]);

            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()], 422);
            }

            // Fetch the employee with related work shift
            $intern = Interns::with('workShift')
                ->where('intern_id', $intern_id)
                ->where('organization_id', $org_id)
                ->first();

            if (!$intern) {
                return response()->json([
                    'message' => 'Intern not found in this organization.'
                ], 404);
            }

            // Check if the employee has a work shift assigned
            if (!$intern->organization_work_shift_id) {
                return response()->json([
                    'message' => 'This Intern does not have any assigned work shift.'
                ], 404);
            }

            $workshift = OrganizationWorkShift::find($intern->organization_work_shift_id);
            if (!$workshift) {
                return response()->json([
                    'message' => 'Work shift details not found.'
                ], 404);
            }

            return response()->json([
                'message' => 'Intern work shift details found successfully.',
                'intern_id' => $intern->intern_id,
                'intern_name' => $intern->intern_id ?? null,
                'workshift' => $workshift
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Something went wrong. Please try again later.',
                'details' => $e->getMessage()
            ], 500);
        }
    }
}
