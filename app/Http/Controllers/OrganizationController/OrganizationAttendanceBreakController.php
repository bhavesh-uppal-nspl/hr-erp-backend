<?php
namespace App\Http\Controllers\OrganizationController;
use App\Models\OrganizationModel\OrganizationAttendanceBreak;
use Auth;
use Illuminate\Support\Facades\Validator;
use App\Http\Controllers\Controller;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class OrganizationAttendanceBreakController extends Controller
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


            if ($request->input('mode') == 1) {
                $break = OrganizationAttendanceBreak::with('BreakType')->where('organization_id', $org_id)->get();

                if ($break->isEmpty()) {
                    return response()->json([
                        'message' => 'Attendance Break not found.'
                    ], 404);
                }
                $mappedBreak = $break->map(function ($dep) {
                    return [
                        'attendance_break_type' => $dep->BreakType->attendance_break_type_name ?? '',
                        'attendance_break_name' => $dep->attendance_break_name ?? '',
                        'description' => $dep->description ?? '',
                        'break_duration_minutes' => $dep->break_duration_minutes ?? '',
                        'break_start_time' => $dep->break_start_time ?? '',
                        'break_end_time' => $dep->break_end_time ?? '',
                        'is_paid' => $dep->is_paid ?? '',
                    ];
                });
                return response()->json($mappedBreak);
            }

            // Build query for attendance break types
            $query = OrganizationAttendanceBreak::with('BreakType')->where('organization_id', $org_id);

            // Apply search filter if provided
            if (!empty($search)) {
                $query->where(function ($q) use ($search) {
                    $q->where('attendance_break_name', 'like', '%' . $search . '%')
                        ->orWhere('description', 'like', '%' . $search . '%');
                });
            }

            // Handle pagination and fetch data
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
                'message' => 'Attendance Breaks fetched successfully',
                'attendance_break' => $statusTypes
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
                'organization_attendance_break_type_id' => 'nullable|integer|exists:organization_attendance_break_types,organization_attendance_break_type_id',
                'organization_entity_id' => 'nullable|integer|exists:organization_entities,organization_entity_id',
                'attendance_break_name' => [
                    'required',
                    'string',
                    'max:50',
                    Rule::unique('organization_attendance_breaks', 'attendance_break_name')
                        ->where(function ($query) use ($request) {
                            return $query->where('organization_id', $request->organization_id);
                        }),
                ],
                'description' => 'nullable|string|max:255',
                'break_duration_minutes' => 'nullable|integer|min:1|max:1440', // max 24 hours
                'break_start_time' => 'nullable|date_format:H:i',
                'break_end_time' => 'nullable|date_format:H:i|after:break_start_time',
                'is_paid' => 'nullable|boolean'
            ]);
            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()], 422);
            }
            $data = $request->all();
            $AttendanceBreaks = OrganizationAttendanceBreak::create(array_merge(['organization_id' => $org_id], $data));
            return response()->json([
                'message' => 'Attendance break Added SuccessFully.',
                'Attendancebreak' => $AttendanceBreaks
            ], 201);

        } catch (\Exception $e) {
            // Handle any other errors and return a response
            return response()->json([
                'error' => 'Something went wrong. Please try again later.',
                'details' => $e->getMessage()
            ], 500);
        }
    }



    // display specific organization 
    public function show(Request $request, $org_id, $break_id)
    {
        try {
            $user = Auth::guard('applicationusers')->user();
            $organizationIds = $user->Client->Organization->pluck('organization_id')->toArray();
            if (!in_array($org_id, $organizationIds)) {
                return response()->json([
                    'messages' => 'unauthorized'
                ], 401);

            }
            $request->merge(['organization_id' => $org_id, 'organization_attendance_break_id' => $break_id]);
            $validator = Validator::make($request->all(), [
                'organization_id' => 'required|integer|exists:organizations,organization_id',
                'organization_attendance_break_id' => 'required|integer|exists:organization_attendance_breaks,organization_attendance_break_id',
            ]);
            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()], 422);
            }
            $attendancebreak = OrganizationAttendanceBreak::find($break_id);
            return response()->json([
                'Attendancebreak' => $attendancebreak
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Something went wrong. Please try again later.',
                'details' => $e->getMessage()
            ], 500);
        }
    }


    // update the orgaization 
    public function update(Request $request, $org_id, $break_id)
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
                'organization_attendance_break_id' => $break_id
            ]);
            $rules = [
                'organization_id' => 'required|integer|exists:organizations,organization_id',
                'organization_attendance_break_id' => 'required|integer|exists:organization_attendance_breaks,organization_attendance_break_id',
                'organization_attendance_break_type_id' => 'nullable|integer|exists:organization_attendance_break_types,organization_attendance_break_type_id',
                'organization_entity_id' => 'nullable|integer|exists:organization_entities,organization_entity_id',
                'attendance_break_name' => [
                    'nullable',
                    'string',
                    'max:255',
                    Rule::unique('organization_attendance_breaks', 'attendance_break_name')
                        ->where(function ($query) use ($request) {
                            return $query->where('organization_id', $request->organization_id);
                        })
                        ->ignore($break_id, 'organization_attendance_break_id'),
                ],

                'description' => 'nullable|string|max:255',
                'break_duration_minutes' => 'nullable|integer|min:1|max:1440', // max 24 hours
                'break_start_time' => 'nullable|date_format:H:i:s',
                'break_end_time' => 'nullable|date_format:H:i:s|after:break_start_time',
                'is_paid' => 'nullable|boolean'
            ];
            $validator = Validator::make($request->all(), $rules);
            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()], 422);
            }
            $Attendancebreak = OrganizationAttendanceBreak::find($break_id);
            $Attendancebreak->update($request->only([
                'organization_attendance_break_type_id',
                'attendance_break_name',
                'description',
                'break_duration_minutes',
                'break_start_time',
                'break_end_time',
                'is_paid'

            ]));

            return response()->json([
                'message' => 'Organization Attendance Break updated successfully.',
                'Attendancebreak' => $Attendancebreak
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Something went wrong. Please try again later.',
                'details' => $e->getMessage()
            ], 500);
        }
    }


    // delete the orgaization  
    public function destroy(Request $request, $org_id, $break_id)
    {
        try {
            $user = Auth::guard('applicationusers')->user();
            $organizationIds = $user->Client->Organization->pluck('organization_id')->toArray();
            if (!in_array($org_id, $organizationIds)) {
                return response()->json([
                    'messages' => 'unauthorized'
                ], 401);

            }

            $request->merge(['organization_id' => $org_id, 'organization_attendance_break_id' => $break_id]);
            $validator = Validator::make($request->all(), [
                'organization_id' => 'required|integer|exists:organizations,organization_id',
                'organization_attendance_break_id' => 'required|integer|exists:organization_attendance_breaks,organization_attendance_break_id',
            ]);
            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()], 422);
            }
            $attendancebreak = OrganizationAttendanceBreak::find($break_id);
            $attendancebreak->delete();
            return response()->json([
                'message' => 'Attendance Break Deleted Successfully'
            ], 200);


        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Something went wrong. Please try again later.',
                'details' => $e->getMessage()
            ], 500);
        }
    }
}
