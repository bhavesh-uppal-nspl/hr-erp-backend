<?php

namespace App\Http\Controllers\EmployeeController;
use App\Models\EmployeesModel\EmployeeExit;
use Auth;
use Carbon\Carbon;
use Illuminate\Support\Facades\Validator;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class EmployeeExitController extends Controller
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

             if ($request->input('mode') == 1) {
                $exit = EmployeeExit::with('employee.designation', 'exitReason')->where('organization_id', $org_id)->get();

                
                $mappedExit = $exit->map(function ($dep) {
                    return [
                        'employee_name' => trim(
                            ($dep->employee->first_name ?? '') . ' ' .
                            ($dep->employee->middle_name ?? '') . ' ' .
                            ($dep->employee->last_name ?? '')
                        ),
                        'designation_name' => $dep->employee->designation->designation_name ?? '',
                       
                        'resignation_date' => $dep->resignation_date ?? '',
                        'notice_period_start' => $dep->notice_period_start ?? '',
                        'notice_period_end' => $dep->notice_period_end ?? '',
                        'last_working_date' => $dep->last_working_date ?? '',
                        'exit_reason' => $dep->exitReason->employment_exit_reason_name   ?? '',
                        'exit_interview_done' => $dep->exit_interview_done  ?? '',
                        'comments' => $dep->comments  ?? '',
                    ];
                });
                return response()->json($mappedExit);
            }






              $query = EmployeeExit::with('employee.designation','exitReason')->where('organization_id', $org_id);
             $per = $request->input('per_page', 10);
            $search = $request->input('search');
            if ($search) {
                $query->where(function ($q) use ($search) {
                    $q->where('leave_duration_type', 'like', "%{$search}%");
                });
            }
              $data = $query->orderBy('created_at', 'desc')->paginate($per);
            return response()->json([
                'message' => 'OK',
                'employeexit' => $data,
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
                'organization_id' =>'required|integer|exists:organizations,organization_id',
                'employee_id' => 'required|integer|exists:employees,employee_id',
                'resignation_date' =>'required|date',
                'notice_period_start' =>'nullable|date',
                'notice_period_end' =>'nullable|date|after_or_equal:notice_period_start',
                'last_working_date' =>'nullable|date|after_or_equal:resignation_date',
                'relieving_date' =>'nullable|date|after_or_equal:last_working_date',
                'organization_employment_exit_reason_id' => 'required|integer|exists:organization_employment_exit_reasons,organization_employment_exit_reason_id',
                'exit_interview_done' => 'nullable|boolean',
                'comments' => 'nullable|string|max:512',
            ]);
            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()], 422);
            }
            $data = $request->all();
            $employeeit = EmployeeExit::create(array_merge($data));
            return response()->json([
                'message' => 'Employee Exit  Added SuccessFully.',
                'employeeit' => $employeeit
            ], 201);
        } catch (\Exception $e) {
            // Handle any other errors and return a response
            return response()->json([
                'error' => 'Something went wrong. Please try again later.',
                'details' => $e->getMessage()
            ], 500);
        }
    }

    public function show(Request $request, $org_id, $exit_id)
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
                'employee_exit_id' => $exit_id
            ]);

            $validator = Validator::make($request->all(), [
                'organization_id' => 'required|integer|exists:organizations,organization_id',
                'employee_exit_id' => 'required|integer|exists:employee_exits,employee_exit_id',
            ]);

            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()], 422);
            }

            $employeexit = EmployeeExit::find($exit_id);

            if (!$employeexit) {
                return response()->json(['error' => 'Employee Exit not found.'], 404);
            }

            $exitData = $employeexit->toArray();

            // ✅ Format date fields using Carbon
            $exitData['resignation_date'] = $employeexit->resignation_date ? Carbon::parse($employeexit->resignation_date)->format('Y-m-d') : null;
            $exitData['notice_period_start'] = $employeexit->notice_period_start ? Carbon::parse($employeexit->notice_period_start)->format('Y-m-d') : null;
            $exitData['notice_period_end'] = $employeexit->notice_period_end ? Carbon::parse($employeexit->notice_period_end)->format('Y-m-d') : null;
            $exitData['last_working_date'] = $employeexit->last_working_date ? Carbon::parse($employeexit->last_working_date)->format('Y-m-d') : null;
            $exitData['relieving_date'] = $employeexit->relieving_date ? Carbon::parse($employeexit->relieving_date)->format('Y-m-d') : null;

            return response()->json([
                'message' => 'Employee Exit Found',
                'employeexit' => $exitData
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Something went wrong. Please try again later.',
                'details' => $e->getMessage()
            ], 500);
        }
    }


    public function update(Request $request, $org_id, $exit_id)
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
                'employee_exit_id' => $exit_id
            ]);

            $rules = [
                'organization_id' => 'required|integer|exists:organizations,organization_id',
                'employee_id' => 'sometimes|integer|exists:employees,employee_id',
                'employee_exit_id' => 'sometimes|integer|exists:employee_exits,employee_exit_id',
                'resignation_date' => 'required|date',
                'notice_period_start' => 'sometimes|nullable|date',
                'notice_period_end' => 'sometimes|nullable|date|after_or_equal:notice_period_start',
                'last_working_date' => 'sometimes|nullable|date|after_or_equal:resignation_date',
                'relieving_date' => 'sometimes|nullable|date|after_or_equal:last_working_date',
                'organization_employment_exit_reason_id' => 'sometimes|integer|exists:organization_employment_exit_reasons,organization_employment_exit_reason_id',
                'exit_interview_done' => 'nullable|boolean',
                'comments' => 'sometimes|nullable|string|max:512',
            ];

            $validator = Validator::make($request->all(), $rules);

            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()], 422);
            }

            $employexit = EmployeeExit::find($exit_id);
            $employexit->update($request->only([
                'resignation_date',
                'notice_period_start',
                'notice_period_end',
                'last_working_date',
                'relieving_date',
                'organization_employment_exit_reason_id',
                'exit_interview_done',
                'comments',
                'employee_id'
            ]));


            return response()->json([
                'message' => 'Employee Exit  Updated Successfully.',
                'employexit' => $employexit
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Something went wrong. Please try again later.',
                'details' => $e->getMessage()
            ], 500);
        }
    }

    public function destroy(Request $request, $org_id, $exit_id)
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
                'employee_exit_id' => $exit_id
            ]);
            $validator = Validator::make($request->all(), [
                'organization_id' => 'required|integer|exists:organizations,organization_id',

                'employee_exit_id' => 'required|integer|exists:employee_exits,employee_exit_id',
            ]);
            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()], 422);
            }
            $employeexit = EmployeeExit::find($exit_id);
            $employeexit->delete();
            return response()->json([
                'message' => 'Employee Exit Deleted Successfully'
            ], 200);


        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Something went wrong. Please try again later.',
                'details' => $e->getMessage()
            ], 500);
        }
    }
}
