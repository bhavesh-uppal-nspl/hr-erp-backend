<?php

namespace App\Http\Controllers\InternController;

use App\Models\InterModel\InterExitRecord;
use App\Models\InterModel\IntershipTypes;
use Auth;
use Illuminate\Support\Facades\Validator;
use App\Http\Controllers\Controller;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class InternExitController extends Controller
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
                $exit = InterExitRecord::with('Intern')->where('organization_id', $org_id)->get();

                 if ($exit->isEmpty()) {
                    return response()->json([
                        'message' => 'workshift not found.'
                    ], 404);
                }
                $mappedExit= $exit->map(function ($dep) {
                    return [
                      'intern_name' => trim(($dep->Intern->first_name ?? '') . ' ' . ($dep->Intern->last_name ?? '')),

                        'exit_type' => $dep->exit_type ?? '',
                        'exit_date' => $dep->exit_date ?? '',
                        'last_working_day' => $dep->last_working_day ?? '',
                        'reason_for_exit' => $dep->reason_for_exit ?? '',
                        'handover_completed' => $dep->handover_completed  ?? '',
                        'handover_notes' => $dep->handover_notes  ?? '',
                        'clearance_status' => $dep->clearance_status  ?? '',
                        'manager_feedback' => $dep->manager_feedback  ?? '',
                        'intern_feedback' => $dep->intern_feedback  ?? '',
                        'certificate_issued' => $dep->certificate_issued  ?? '',
                        'certificate_issue_date' => $dep->certificate_issue_date  ?? '',
                    ];
                });
                return response()->json($mappedExit);

                
            }




            $query = InterExitRecord::with('Intern')->where('organization_id', $org_id);
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
                'intern_id' => 'nullable|exists:interns,intern_id',
                'exit_type' => 'nullable|in:Completed,Terminated,Resigned,ConvertedToEmployee',
                'clearance_status' => 'nullable|in:Pending,In Progress,Completed',
                'exit_date' => 'nullable|date',
                'certificate_issue_date' => 'nullable|date',
                'last_working_day' => 'nullable|date',
                'reason_for_exit' => 'nullable|string|max:255',
                'handover_notes' => 'nullable|string|max:255',
                'manager_feedback' => 'nullable|string|max:255',
                'intern_feedback' => 'nullable|string|max:255',
                'handover_completed' => 'nullable|boolean',
                'certificate_issued' => 'nullable|boolean'

            ]);

            if ($validator->fails()) {
                return response()->json([
                    'errors' => $validator->errors()
                ], 422);
            }

            $data = $request->all();
            $intership = InterExitRecord::create($data);
            return response()->json([
                'message' => 'Intership Record Added Successfully.',
                'intership' => $intership
            ], 201);

        } catch (\Exception $e) {
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
                    'messages' => 'unauthorized'
                ], 401);

            }
            $request->merge(['organization_id' => $org_id, 'intern_exit_record_id' => $exit_id]);
            $validator = Validator::make($request->all(), [
                'organization_id' => 'required|integer|exists:organizations,organization_id',
                'intern_exit_record_id' => 'required|integer|exists:intern_exit_records,intern_exit_record_id',
            ]);
            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()], 422);
            }
            $intership = InterExitRecord::find($exit_id);
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

    public function update(Request $request, $org_id, $exit_id)
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
                'intern_exit_record_id' => $exit_id
            ]);
            $rules = [
                'organization_id' => 'required|integer|exists:organizations,organization_id',
                'intern_exit_record_id' => 'required|integer|exists:intern_exit_records,intern_exit_record_id',
            ];
            $validator = Validator::make($request->all(), $rules);
            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()], 422);
            }
            $intership = InterExitRecord::find($exit_id);
            $intership->update($request->only([
                'intern_id',
                'exit_type',
                'exit_date',
                'last_working_day',
                'reason_for_exit',
                'handover_completed',
                'handover_notes',
                'clearance_status',
                'manager_feedback',
                'intern_feedback',
                'certificate_issued',
                'certificate_issue_date'
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

    public function destroy(Request $request, $org_id, $exit_id)
    {
        try {
            $user = Auth::guard('applicationusers')->user();
            $organizationIds = $user->Client->Organization->pluck('organization_id')->toArray();
            if (!in_array($org_id, $organizationIds)) {
                return response()->json([
                    'messages' => 'unauthorized'
                ], 401);
            }
            $request->merge(['organization_id' => $org_id, 'intern_exit_record_id' => $exit_id]);
            $validator = Validator::make($request->all(), [
                'organization_id' => 'required|integer|exists:organizations,organization_id',
                'intern_exit_record_id' => 'required|integer|exists:intern_exit_records,intern_exit_record_id',
            ]);
            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()], 422);
            }
            $intership = InterExitRecord::find($exit_id);
            $intership->delete();
            return response()->json([
                'message' => 'Intership Exit Record Deleted SuccessFully !'
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
}
