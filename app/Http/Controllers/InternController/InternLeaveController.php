<?php

namespace App\Http\Controllers\InternController;
use App\Models\InterModel\InternLeaves;
use Carbon\Carbon;
use Illuminate\Support\Facades\Validator;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class InternLeaveController extends Controller
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
                $leave = InternLeaves::with('Intern', 'leaveType', 'leaveReason', 'leaveCategory', 'ApprovedBy', 'RejectedBy')->where('organization_id', $org_id)->get();
                if ($leave->isEmpty()) {
                    return response()->json([
                        'message' => 'workshift not found.'
                    ], 404);
                }
                $mappedExit = $leave->map(function ($dep) {
                    return [
                       'intern_name' => trim(($dep->Intern->first_name ?? '') . ' ' . ($dep->Intern->last_name ?? '')),

                        'leave_type' => $dep->leaveType->leave_type_name ?? '',
                        'leave_category' => $dep->leaveCategory->leave_category_name ?? '',
                        'leave_reason' => $dep->leaveReason->leave_reason_name ?? '',
                        'leave_duration_type' => $dep->leave_duration_type ?? '',
                        'total_leave_days' => $dep->total_leave_days ?? '',
                        'leave_start_date' => $dep->leave_start_date ?? '',
                        'leave_end_date' => $dep->leave_end_date ?? '',
                        'total_leave_hours' => $dep->total_leave_hours ?? '',
                        'intern_remarks' => $dep->intern_remarks ?? '',
                        'leave_start_time' => $dep->leave_start_time ?? '',
                        'leave_end_time' => $dep->leave_end_time ?? '',
                        'leave_status' => $dep->leave_status ?? '',
                        'approved_by' => $dep->ApprovedBy
                            ? trim(($dep->ApprovedBy->first_name ?? '') . ' ' . ($dep->ApprovedBy->middle_name ?? '') . ' ' . ($dep->ApprovedBy->last_name ?? ''))
                            : '',
                        'approval_date' => $dep->approval_date ?? '',
                        'leave_rejection_reason' => $dep->leave_rejection_reason ?? '',
                    ];
                });
                return response()->json($mappedExit);
            }

            $query = InternLeaves::with('Intern', 'leaveType', 'leaveReason', 'leaveCategory', 'ApprovedBy', 'RejectedBy')->where('organization_id', $org_id);
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
            $request->merge(['organization_id' => $org_id]);
            $validator = Validator::make($request->all(), [
                'organization_id' => 'required|integer|exists:organizations,organization_id',
                'intern_id' => 'required|integer|exists:interns,intern_id',
                'organization_leave_type_id' => 'nullable|integer|exists:organization_leave_types,organization_leave_type_id',
                'organization_leave_reason_type_id' => 'nullable|integer|exists:organization_leave_reason_types,organization_leave_reason_type_id',
                'organization_leave_category_id' => 'nullable|integer|exists:organization_leave_categories,organization_leave_category_id',
                'organization_leave_reason_id' => 'nullable|integer|exists:organization_leave_reasons,organization_leave_reason_id',
                'leave_duration_type' => 'nullable|in:full_day,half_day,short_leave',
                'total_leave_days' => 'nullable|numeric|min:0',
                'leave_start_date' => 'nullable|date',
                'leave_end_date' => 'nullable|date|after_or_equal:leave_start_date',
                'total_leave_hours' => 'nullable|numeric|min:0',
                'intern_remarks' => 'nullable|string|max:512',
                'leave_start_time' => 'nullable|date_format:H:i',
                'leave_end_time' => 'nullable|date_format:H:i',
                'leave_status' => 'nullable|in:Pending,Approved,Rejected',
                'approved_by' => 'nullable|integer',
                'rejected_by' => 'nullable|integer',
                'approval_date' => 'nullable|date',
                'rejection_date' => 'nullable|date',
                'supporting_document_url' => 'nullable|url|max:512',
                'leave_rejection_reason' => 'nullable|string|max:512',
            ]);
            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()], 422);
            }
            $data = $request->all();
            InternLeaves::create(array_merge($data));
            return response()->json([
                'message' => 'Intern Leave  Added SuccessFully.',
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Something went wrong. Please try again later.',
                'details' => $e->getMessage()
            ], 500);
        }
    }

    public function show(Request $request, $org_id, $leave_id)
    {
        try {
            $request->merge(['organization_id' => $org_id, 'intern_leave_id' => $leave_id]);
            $validator = Validator::make($request->all(), [
                'organization_id' => 'required|integer|exists:organizations,organization_id',
                'intern_leave_id' => 'required|integer|exists:intern_leaves,intern_leave_id',
            ]);
            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()], 422);
            }

            $internleave = InternLeaves::find($leave_id);
            $internleave->load('Intern', 'leaveType', 'leaveReason.leaveReasonType', 'leaveCategory', 'ApprovedBy', 'RejectedBy');
            if (!$internleave) {
                return response()->json(['error' => 'Employee leave not found.'], 404);
            }

            $leaveData = $internleave->toArray();
            $leaveData['leave_start_date'] = $internleave->leave_start_date ? Carbon::parse($internleave->leave_start_date)->format('Y-m-d') : null;
            $leaveData['leave_end_date'] = $internleave->leave_end_date ? Carbon::parse($internleave->leave_end_date)->format('Y-m-d') : null;
            $leaveData['approval_date'] = $internleave->approval_date ? Carbon::parse($internleave->approval_date)->format('Y-m-d') : null;
            $leaveData['rejection_date'] = $internleave->rejection_date ? Carbon::parse($internleave->rejection_date)->format('Y-m-d') : null;

            return response()->json([
                'message' => 'Intern Leave Found',
                'intership' => $leaveData
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Something went wrong. Please try again later.',
                'details' => $e->getMessage()
            ], 500);
        }
    }

    public function update(Request $request, $org_id, $leave_id)
    {
        try {
            $request->merge([
                'organization_id' => $org_id,
                'intern_leave_id' => $leave_id
            ]);
            $rules = [
                'organization_id' => 'required|integer|exists:organizations,organization_id',
                'intern_leave_id' => 'required|integer|exists:intern_leaves,intern_leave_id',
                'intern_id' => 'nullable|exists:interns,intern_id',
                'organization_leave_type_id' => 'sometimes|integer|exists:organization_leave_types,organization_leave_type_id',
                'organization_leave_category_id' => 'nullable|integer|exists:organization_leave_categories,organization_leave_category_id',
                'organization_leave_reason_type_id' => 'nullable|integer|exists:organization_leave_reason_types,organization_leave_reason_type_id',
                'organization_leave_reason_id' => 'sometimes|integer|exists:organization_leave_reasons,organization_leave_reason_id',
                'leave_duration_type' => 'nullable|in:full_day,half_day,short_leave',
                'total_leave_days' => 'nullable|numeric|min:0',
                'leave_start_date' => 'nullable|date',
                'leave_end_date' => 'date|after_or_equal:leave_start_date',
                'total_leave_hours' => 'nullable|numeric|min:0',
                'intern_remarks' => 'nullable|string|max:512',
                'leave_start_time' => 'nullable|date_format:H:i:s',
                'leave_end_time' => 'nullable|date_format:H:i:s',
                'leave_status' => 'nullable|in:Pending,Approved,Rejected',
                'approved_by' => 'nullable|integer',
                'approval_date' => 'nullable|date',
                'rejection_date' => 'nullable|date',
                'rejected_by' => 'nullable|integer',
                'supporting_document_url' => 'nullable|url|max:512',
                'leave_rejection_reason' => 'nullable|string|max:512',
            ];
            $validator = Validator::make($request->all(), $rules);
            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()], 422);
            }
            $employeeleave = InternLeaves::find($leave_id);
            $employeeleave->update($request->only([
                'leave_duration_type',
                'total_leave_days',
                'leave_start_date',
                'leave_end_date',
                'total_leave_hours',
                'intern_remarks',
                'leave_start_time',
                'rejected_by',
                'leave_end_time',
                'leave_status',
                'approved_by',
                'approval_date',
                'supporting_document_url',
                'leave_rejection_reason',
                'intern_id',
                'rejection_date',
                'organization_leave_type_id',
                'organization_leave_category_id',
                'organization_leave_reason_id',
                'organization_leave_reason_type_id'
            ]));

            return response()->json([
                'message' => 'Employee Leave  Updated Successfully.',
                'intership' => $employeeleave
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Something went wrong. Please try again later.',
                'details' => $e->getMessage()
            ], 500);
        }
    }

    public function destroy(Request $request, $org_id, $leave_id)
    {
        try {
            $request->merge([
                'organization_id' => $org_id,

                'intern_leave_id' => $leave_id
            ]);
            $validator = Validator::make($request->all(), [
                'organization_id' => 'required|integer|exists:organizations,organization_id',

                'intern_leave_id' => 'required|integer|exists:intern_leaves,intern_leave_id',
            ]);
            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()], 422);
            }
            $employeeleave = InternLeaves::find($leave_id);
            $employeeleave->delete();
            return response()->json([
                'message' => 'Intern Leave Deleted Successfully'
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Something went wrong. Please try again later.',
                'details' => $e->getMessage()
            ], 500);
        }
    }

    // public function AllLeaves(Request $request, $org_id)
    // {
    //     try {
    //         $request->merge(['organization_id' => $org_id]);
    //         $validator = Validator::make($request->all(), [
    //             'organization_id' => 'required|integer|exists:organizations,organization_id',
    //         ]);
    //         if ($validator->fails()) {
    //             return response()->json(['errors' => $validator->errors()], 422);
    //         }
    //         $query = EmployeeLeaves::with('employee.designation', 'leaveReason', 'leaveType', 'leaveCategory', 'ApprovedBy', 'RejectedBy')->where('organization_id', $org_id);
    //         $per = $request->input('per_page', 10);
    //         $search = $request->input('search');
    //         if ($search) {
    //             $query->where(function ($q) use ($search) {
    //                 $q->where('leave_duration_type', 'like', "%{$search}%");
    //             });
    //         }
    //         $data = $query->orderBy('created_at', 'desc')->paginate($per);
    //         return response()->json([$data]);
    //     } catch (\Exception $e) {
    //         return response()->json([
    //             'error' => 'Something went wrong. Please try again later.',
    //             'details' => $e->getMessage()
    //         ], 500);
    //     }
    // }

}




