<?php

namespace App\Http\Controllers\AttendenceController;
use App\Http\Controllers\Controller;
use App\Models\AttendenceModels\AttendenceBreakTypes;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Auth;
use Illuminate\Support\Facades\Validator;

class AttendenceBreakTypeController extends Controller
{
    public function index(Request $request, $org_id)
    {
        try {
            // Get the authenticated user
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
                $break = AttendenceBreakTypes::where('organization_id', $org_id)->get();

                if ($break->isEmpty()) {
                    return response()->json([
                        'message' => 'Break not found.'
                    ], 404);
                }
                $mappedBreak = $break->map(function ($dep) {
                    return [
                        'attendance_break_type_name'=>$dep->attendance_break_type_name,
                        'description' => $dep->description ?? '',
                        'is_active' => $dep->is_active ?? '',
                    ];
                });
                return response()->json($mappedBreak);
            }







            // Build query for attendance break types
            $query = AttendenceBreakTypes::where('organization_id', $org_id);

            // Apply search filter if provided
            if (!empty($search)) {
                $query->where(function ($q) use ($search) {
                    $q->where('attendance_break_type_name', 'like', '%' . $search . '%')
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
                'message' => 'Attendance Break Types fetched successfully',
                'attendance_breaktype' => $statusTypes
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
            if (!in_array((int) $org_id, $organizationIds)) {
                return response()->json([
                    'message' => 'Unauthenticated'
                ], 401);
            }
            $request->merge(['organization_id' => $org_id]);

            $validator = Validator::make($request->all(), [
                'organization_id' => 'required|integer|exists:organizations,organization_id',
                'organization_entity_id' => 'nullable|integer|exists:organization_entities,organization_entity_id',
                'organization_configuration_template_id' => 'nullable|integer|exists:organization_configuration_templates,organization_configuration_template_id',
                'attendance_break_type_name' => 'required|string|max:50',
                'description' => 'nullable|string|max:255',
                'is_active' => 'nullable|boolean',
                'created_at' => 'nullable|date',
                'updated_at' => 'nullable|date',
            ]);

            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()], 422);
            }

            $data = $request->all();
            $breakTypes = AttendenceBreakTypes::create($data);

            return response()->json([
                'message' => 'Employee Attendance Break Type added successfully.',
                'breakTypes' => $breakTypes
            ], 201);

        } catch (\Exception $e) {
            \Log::error('Error creating attendance break type: ' . $e->getMessage());
            return response()->json([
                'error' => 'Something went wrong. Please try again later.',
                'details' => $e->getMessage()
            ], 500);
        }
    }


    public function show(Request $request, $org_id, $type_id)
    {
        try {


            $user = Auth::guard('applicationusers')->user();

            // Get all organization IDs linked to the user
            $organizationIds = $user->Client->Organization->pluck('organization_id')->toArray();

            // Check if the requested org_id belongs to the logged-in user's organizations
            if (!in_array((int) $org_id, $organizationIds)) {
                return response()->json([
                    'message' => 'Unauthenticated'
                ], 401);
            }



            $request->merge(['organization_id' => $org_id, 'organization_attendance_break_type_id' => $type_id]);
            $validator = Validator::make($request->all(), [
                'organization_id' => 'required|integer|exists:organizations,organization_id',
                'organization_attendance_break_type_id' => 'required|integer|exists:organization_attendance_break_types,organization_attendance_break_type_id',
            ]);
            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()], 422);
            }
            $breakTypes = AttendenceBreakTypes::find($type_id);
            return response()->json([
                'message' => 'Employee Document Link Found',
                'breakTypes' => $breakTypes
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Something went wrong. Please try again later.',
                'details' => $e->getMessage()
            ], 500);
        }
    }

    public function update(Request $request, $org_id, $type_id)
    {
        try {
            $request->merge([
                'organization_id' => $org_id,
                'organization_attendance_break_type_id' => $type_id
            ]);
            $rules = [


                'organization_id' => 'required|integer|exists:organizations,organization_id',
                'organization_attendance_break_type_id' => 'required|integer|exists:organization_attendance_break_types,organization_attendance_break_type_id',
                'organization_entity_id' => 'nullable|integer|exists:organization_entities,organization_entity_id',
                'organization_configuration_template_id' => 'nullable|integer|exists:organization_configuration_templates,organization_configuration_template_id',
                'attendance_break_type_name' => 'nullable|string|max:50',
                'description' => 'nullable|string|max:255',
                'is_active' => 'nullable|boolean',
                'created_at' => 'nullable|date',
                'updated_at' => 'nullable|date',
            ];
            $validator = Validator::make($request->all(), $rules);
            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()], 422);
            }
            $breakTypes = AttendenceBreakTypes::find($type_id);
            $breakTypes->update($request->only([
                'attendance_break_type_name',
                'description',
                'is_active',
            ]));
            return response()->json([
                'message' => 'Employee Break Type  Updated Successfully.',
                'breakTypes' => $breakTypes
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Something went wrong. Please try again later.',
                'details' => $e->getMessage()
            ], 500);
        }
    }

    public function destroy(Request $request, $org_id, $type_id)
    {
        try {
            $request->merge([
                'organization_id' => $org_id,
                'organization_attendance_break_type_id' => $type_id
            ]);
            $validator = Validator::make($request->all(), [
                'organization_id' => 'required|integer|exists:organizations,organization_id',
                'organization_attendance_break_type_id' => 'required|integer|exists:organization_attendance_break_types,organization_attendance_break_type_id',
            ]);
            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()], 422);
            }
            $breakTypes = AttendenceBreakTypes::find($type_id);
            $breakTypes->delete();
            return response()->json([
                'message' => 'Employee Attendence Break Type Deleted Successfully'
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Something went wrong. Please try again later.',
                'details' => $e->getMessage()
            ], 500);
        }
    }



}
