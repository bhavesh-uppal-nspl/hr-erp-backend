<?php
namespace App\Http\Controllers\OrganizationController;
use App\Models\OrganizationModel\OrganizationAttendanceBreak;
use App\Models\OrganizationModel\OrganizationWorkShiftBreak;
use Auth;
use Illuminate\Support\Facades\Validator;
use App\Http\Controllers\Controller;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class OrganizationWorkShiftBreakController extends Controller
{


    public function index(Request $request, $org_id)
    {
        try {
            $user = Auth::guard('applicationusers')->user();
            $organizationIds = $user->Client->Organization->pluck('organization_id')->toArray();
            if (!in_array((int) $org_id, $organizationIds)) {
                return response()->json([
                    'message' => 'Unauthenticated'
                ], 401);
            }
            $request->merge(['organization_id' => $org_id]);
            $validator = Validator::make($request->all(), [
                'organization_id' => 'required|integer|exists:organizations,organization_id',
            ]);
            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()], 422);
            }
            $perPage = $request->get('per_page');
            $page = (int) $request->get('page', 1);
            $search = $request->get('search');
            $query = OrganizationWorkShiftBreak::with('AttendanceBreak','workshift')->where('organization_id', $org_id);
            if (!empty($search)) {
                $query->where(function ($q) use ($search) {
                    $q->where('attendance_break_name', 'like', '%' . $search . '%')
                        ->orWhere('description', 'like', '%' . $search . '%');
                });
            }
            if ($perPage === 'all') {
                $statusTypes = $query->get();
            } elseif (!empty($perPage) && is_numeric($perPage)) {
                $perPage = (int) $perPage;
                $statusTypes = $query->paginate($perPage, ['*'], 'page', $page);
            } else {
                $statusTypes = $query->get();
            }

            // Return success response
            return response()->json([
                'message' => 'Organization WorkShift Break fetched successfully',
                'workshiftbreak' => $statusTypes
            ], 200);

        } catch (\Exception $e) {
            \Log::error('Error fetching attendance break types: ' . $e->getMessage());

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
                'organization_entity_id' => 'nullable|integer|exists:organization_entities,organization_entity_id',
                'organization_work_shift_id' => 'nullable|integer|exists:organization_work_shifts,organization_work_shift_id',
                'organization_attendance_break_id' => 'nullable|integer|exists:organization_attendance_breaks,organization_attendance_break_id',
            ]);
            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()], 422);
            }
            $data = $request->all();
            $WorkshiftBreaks = OrganizationWorkShiftBreak::create(array_merge(['organization_id' => $org_id], $data));
            return response()->json([
                'message' => 'Workshift break Added SuccessFully.',
                'WorkshiftBreaks' => $WorkshiftBreaks
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Something went wrong. Please try again later.',
                'details' => $e->getMessage()
            ], 500);
        }
    }



    public function show(Request $request, $org_id, $shift_id)
    {
        try {
            $user = Auth::guard('applicationusers')->user();
            $organizationIds = $user->Client->Organization->pluck('organization_id')->toArray();
            if (!in_array($org_id, $organizationIds)) {
                return response()->json([
                    'messages' => 'unauthorized'
                ], 401);
            }
            $request->merge(['organization_id' => $org_id, 'organization_work_shift_break_id' => $shift_id]);
            $validator = Validator::make($request->all(), [
                'organization_id' => 'required|integer|exists:organizations,organization_id',
                'organization_work_shift_break_id' => 'required|integer|exists:organization_work_shift_breaks,organization_work_shift_break_id',
            ]);
            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()], 422);
            }
            $workshiftbreak = OrganizationWorkShiftBreak::find($shift_id);
            $workshiftbreak->load('AttendanceBreak','workshift');
            return response()->json([
                'Workshiftbreak' => $workshiftbreak
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Something went wrong. Please try again later.',
                'details' => $e->getMessage()
            ], 500);
        }
    }


    // update the orgaization 
    public function update(Request $request, $org_id, $shift_id)
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
                'organization_work_shift_break_id' => $shift_id
            ]);
            $rules = [
                'organization_id' => 'required|integer|exists:organizations,organization_id',
                'organization_work_shift_break_id' => 'required|integer|exists:organization_work_shift_breaks,organization_work_shift_break_id',
                'organization_entity_id' => 'nullable|integer|exists:organization_entities,organization_entity_id',
                'organization_work_shift_id' => 'nullable|integer|exists:organization_work_shifts,organization_work_shift_id',
                'organization_attendance_break_id' => 'nullable|integer|exists:organization_attendance_breaks,organization_attendance_break_id',

            ];
            $validator = Validator::make($request->all(), $rules);
            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()], 422);
            }
            $workshiftbreak = OrganizationWorkShiftBreak::find($shift_id);
            $workshiftbreak->update($request->only([
                'organization_attendance_break_id',
                'organization_work_shift_id',
            ]));

            return response()->json([
                'message' => 'Organization Workshift Break updated successfully.',
                'workshiftbreak' => $workshiftbreak
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Something went wrong. Please try again later.',
                'details' => $e->getMessage()
            ], 500);
        }
    }


    // delete the orgaization  
    public function destroy(Request $request, $org_id, $shift_id)
    {
        try {
            $user = Auth::guard('applicationusers')->user();
            $organizationIds = $user->Client->Organization->pluck('organization_id')->toArray();
            if (!in_array($org_id, $organizationIds)) {
                return response()->json([
                    'messages' => 'unauthorized'
                ], 401);
            }
            $request->merge(['organization_id' => $org_id, 'organization_work_shift_break_id' => $shift_id]);
            $validator = Validator::make($request->all(), [
                'organization_id' => 'required|integer|exists:organizations,organization_id',
              'organization_work_shift_break_id' => 'required|integer|exists:organization_work_shift_breaks,organization_work_shift_break_id',
            ]);
            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()], 422);
            }
            $workshiftbreak = OrganizationWorkShiftBreak::find($shift_id);
            $workshiftbreak->delete();
            return response()->json([
                'message' => 'Workshift Break Deleted Successfully'
            ], 200);


        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Something went wrong. Please try again later.',
                'details' => $e->getMessage()
            ], 500);
        }
    }
}
