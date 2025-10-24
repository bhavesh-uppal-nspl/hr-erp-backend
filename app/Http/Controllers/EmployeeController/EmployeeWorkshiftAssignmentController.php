<?php

namespace App\Http\Controllers\EmployeeController;
use App\Models\EmployeesModel\EmployeeMedical;
use App\Models\EmployeesModel\EmployeeWorkshiftAssignment;
use Auth;
use Carbon\Carbon;
use Illuminate\Support\Facades\Validator;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class EmployeeWorkshiftAssignmentController extends Controller
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
                $workshift = EmployeeWorkshiftAssignment::with('Workshift', 'employee')->where('organization_id', $org_id)->get();

                if ($workshift->isEmpty()) {
                    return response()->json([
                        'message' => 'workshift not found.'
                    ], 404);
                }
                $mappedWorkshift = $workshift->map(function ($dep) {
                    return [
                        'employee_name' => trim(
                            ($dep->employee->first_name ?? '') . ' ' .
                            ($dep->employee->middle_name ?? '') . ' ' .
                            ($dep->employee->last_name ?? '')
                        ),
                        'work_shift' => $dep->Workshift->work_shift_type_name ?? '',
                        'assignment_date ' => $dep->assignment_date ?? '',
                        'is_override' => $dep->is_override ?? '',
                        'remarks' => $dep->remarks ?? '',

                    ];
                });
                return response()->json($mappedWorkshift);
            }
            $query = EmployeeWorkshiftAssignment::with('Workshift', 'employee')->where('organization_id', $org_id);
            $per = $request->input('per_page', 999);
            $search = $request->input('search');
            if ($search) {
                $query->where(function ($q) use ($search) {
                    $q->where('remarks', 'like', "%{$search}%");
                });
            }
            $data = $query->orderBy('created_at', 'desc')->paginate($per);
            return response()->json([$data]);

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
                'organization_work_shift_id' => 'required|integer|exists:organization_work_shifts,organization_work_shift_id',
                'is_override' => ['nullable', 'boolean'],
                'assignment_date' => ['nullable', 'date'],
                'remarks' => ['nullable', 'string', 'max:255'],
            ]);
            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()], 422);
            }
            $data = $request->all();
            $shiftAssignment = EmployeeWorkshiftAssignment::create(array_merge($data));
            return response()->json([
                'message' => 'Employee shift Assignment Detail Added SuccessFully.',
                'shiftAssignment' => $shiftAssignment
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Something went wrong. Please try again later.',
                'details' => $e->getMessage()
            ], 500);
        }
    }

    public function show(Request $request, $org_id, $assignment_id)
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
                'employee_work_shift_assignment_id' => $assignment_id
            ]);
            $validator = Validator::make($request->all(), [
                'organization_id' => 'required|integer|exists:organizations,organization_id',
                'employee_work_shift_assignment_id' => 'required|integer|exists:employee_work_shift_assignments,employee_work_shift_assignment_id',
            ]);
            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()], 422);
            }
            $shiftAssignment = EmployeeWorkshiftAssignment::find($assignment_id);
            $shift = $shiftAssignment->toArray();
            $shift['assignment_date'] = $shiftAssignment->assignment_date ? Carbon::parse($shiftAssignment->assignment_date)->format('Y-m-d') : null;



            return response()->json([
                'message' => 'Assignment data founf',
                'Assignmentdata' => $shift
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Something went wrong. Please try again later.',
                'details' => $e->getMessage()
            ], 500);
        }
    }


    public function update(Request $request, $org_id, $assignment_id)
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
                'employee_work_shift_assignment_id' => $assignment_id
            ]);
            $rules = [
                'organization_id' => 'required|integer|exists:organizations,organization_id',
                'employee_work_shift_assignment_id' => 'required|integer|exists:employee_work_shift_assignments,employee_work_shift_assignment_id',
                'employee_id' => 'nullable|integer|exists:employees,employee_id',
                'organization_work_shift_id' => 'nullable|integer|exists:organization_work_shifts,organization_work_shift_id',
                'is_override' => ['nullable', 'boolean'],
                'assignment_date' => ['nullable', 'date'],
                'remarks' => ['nullable', 'string', 'max:255'],
            ];

            $validator = Validator::make($request->all(), $rules);

            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()], 422);
            }
            $shiftAssignment = EmployeeWorkshiftAssignment::find($assignment_id);
            $shiftAssignment->update($request->only([
                'employee_id',
                'organization_work_shift_id',
                'assignment_date',
                'is_override',
                'remarks'
            ]));
            return response()->json([
                'message' => 'Employee workshift Assignment Updated Successfully.',
                'shiftAssignment' => $shiftAssignment
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Something went wrong. Please try again later.',
                'details' => $e->getMessage()
            ], 500);
        }
    }

    public function destroy(Request $request, $org_id, $assignment_id)
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
                'employee_work_shift_assignment_id' => $assignment_id
            ]);
            $validator = Validator::make($request->all(), [
                'organization_id' => 'required|integer|exists:organizations,organization_id',
                'employee_work_shift_assignment_id' => 'required|integer|exists:employee_work_shift_assignments,employee_work_shift_assignment_id',

            ]);
            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()], 422);
            }
            $shiftAssignment = EmployeeWorkshiftAssignment::find($assignment_id);
            $shiftAssignment->delete();
            return response()->json([
                'message' => 'Employee shift Assignment Deleted Successfully'
            ], 200);


        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Something went wrong. Please try again later.',
                'details' => $e->getMessage()
            ], 500);
        }
    }
}
