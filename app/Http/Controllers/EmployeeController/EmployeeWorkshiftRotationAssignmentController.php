<?php

namespace App\Http\Controllers\EmployeeController;
use App\Models\EmployeesModel\EmployeeWorkshiftRotationAssignment;
use Auth;
use Carbon\Carbon;
use Illuminate\Support\Facades\Validator;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class EmployeeWorkshiftRotationAssignmentController extends Controller
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
                $workshift = EmployeeWorkshiftRotationAssignment::with('RotationPattern', 'employee')->where('organization_id', $org_id)->get();

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
                        'work_shift_rotation_pattern' => $dep->RotationPattern->pattern_name ?? '',
                        'effective_start_date' => $dep->effective_start_date  ?? '',
                        'effective_end_date' => $dep->effective_end_date ?? '',
                        'anchor_day_number' => $dep->anchor_day_number ?? '',
                        'is_active ' => $dep->is_active  ?? '',
                        'remarks' => $dep->remarks ?? '',
                       
                    ];
                });
                return response()->json($mappedWorkshift);
            }

            $query = EmployeeWorkshiftRotationAssignment::with('RotationPattern', 'employee')->where('organization_id', $org_id);
            $per = $request->input('per_page', 10);
            $search = $request->input('search');
            if ($search) {
                $query->where(function ($q) use ($search) {
                    $q->where('remarks', 'like', "%{$search}%");
                });
            }
            $data = $query->orderBy('created_at', 'desc')->paginate($per);
            return response()->json($data);

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
                'organization_work_shift_rotation_pattern_id' =>'required|integer|exists:organization_work_shift_rotation_patterns,organization_work_shift_rotation_pattern_id',
                'effective_start_date' => ['nullable', 'date'],
                'effective_end_date' => ['nullable', 'date', 'after_or_equal:effective_start_date'],
                 'is_active ' => ['nullable', 'boolean'],
                 'anchor_day_number' => ['nullable', 'integer','min:1', 'max:366'],
                'remarks' => ['nullable', 'string', 'max:255'],
            ]);
            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()], 422);
            }
            $data = $request->all();
            EmployeeWorkshiftRotationAssignment::create(array_merge($data));
            return response()->json([
                'message' => 'Employee workshift rotation Assignment Details Added SuccessFully.',
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
                'employee_work_shift_rotation_assignment_id' => $assignment_id
            ]);
            $validator = Validator::make($request->all(), [
                'organization_id' => 'required|integer|exists:organizations,organization_id',
                'employee_work_shift_rotation_assignment_id' => 'required|integer|exists:employee_work_shift_rotation_assignments,employee_work_shift_rotation_assignment_id',
            ]);
            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()], 422);
            }
            $shiftAssignment = EmployeeWorkshiftRotationAssignment::find($assignment_id);
            $shift = $shiftAssignment->toArray();
            $shift['effective_start_date'] = $shiftAssignment->effective_start_date ? Carbon::parse($shiftAssignment->effective_start_date)->format('Y-m-d') : null;
            $shift['effective_end_date'] = $shiftAssignment->effective_end_date ? Carbon::parse($shiftAssignment->effective_end_date)->format('Y-m-d') : null;
            return response()->json(
                $shift);

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
                'employee_work_shift_rotation_assignment_id' => $assignment_id
            ]);
            $rules = [
                'organization_id' => 'required|integer|exists:organizations,organization_id',
                'employee_work_shift_rotation_assignment_id' => 'required|integer|exists:employee_work_shift_rotation_assignments,employee_work_shift_rotation_assignment_id',
                'employee_id' => 'nullable|integer|exists:employees,employee_id',
                'organization_work_shift_rotation_pattern_id' => 'nullable|integer|exists:organization_work_shift_rotation_patterns,organization_work_shift_rotation_pattern_id',
                'effective_start_date' => ['nullable', 'date'],
                'effective_end_date' => ['nullable', 'date', 'after_or_equal:effective_start_date'],
                 'is_active ' => ['nullable', 'boolean'],
                 'anchor_day_number' => ['nullable', 'integer','min:1', 'max:366'],
                'remarks' => ['nullable', 'string', 'max:255'],
            ];
            $validator = Validator::make($request->all(), $rules);
            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()], 422);
            }
            $shiftAssignment = EmployeeWorkshiftRotationAssignment::find($assignment_id);
            $shiftAssignment->update($request->only([
                'employee_id',
                'organization_work_shift_rotation_pattern_id',
                'effective_start_date',
                'effective_end_date',
                'remarks',
                'anchor_day_number',
                'is_active'
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
                'employee_work_shift_rotation_assignment_id' => $assignment_id
            ]);
            $validator = Validator::make($request->all(), [
                'organization_id' => 'required|integer|exists:organizations,organization_id',
                'employee_work_shift_rotation_assignment_id' => 'required|integer|exists:employee_work_shift_rotation_assignments,employee_work_shift_rotation_assignment_id',

            ]);
            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()], 422);
            }
            $shiftAssignment = EmployeeWorkshiftRotationAssignment::find($assignment_id);
            $shiftAssignment->delete();
            return response()->json([
                'message' => 'Employee shift Rotation Assignment Deleted Successfully'
            ], 200);


        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Something went wrong. Please try again later.',
                'details' => $e->getMessage()
            ], 500);
        }
    }
}
