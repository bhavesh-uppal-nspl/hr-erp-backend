<?php

namespace App\Http\Controllers\AttendenceController;
use App\Http\Controllers\Controller;
use App\Models\AttendenceModels\AttendenceSource;
use Auth;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class AttendenceSourceController extends Controller
{
    public function index(Request $request, $org_id)
    {
        try {


            $request->merge(['organization_id' => $org_id]);
            $user = Auth::guard('applicationusers')->user();
            // Get all organization IDs linked to the user
            $organizationIds = $user->Client->Organization->pluck('organization_id')->toArray();

            if (!in_array((int) $org_id, $organizationIds)) {
                return response()->json([
                    'message' => 'Unauthenticated'
                ], 401);
            }

            $perPage = $request->get('per_page');
            $page = (int) $request->get('page', 1);
            $search = $request->get('search');
            $organizationId = $request->get('organization_id');



            if ($request->input('mode') == 1) {
                $source = AttendenceSource::where('organization_id', $org_id)->get();
                if ($source->isEmpty()) {
                    return response()->json([
                        'message' => 'Source not found.'
                    ], 404);
                }
                $mappedSource = $source->map(function ($dep) {
                    return [
                        'attendance_source_name'=>$dep->attendance_source_name,
                        'description' => $dep->description ?? '',
                        'is_active' => $dep->is_active ?? '',
                    ];
                });
                return response()->json($mappedSource);
            }


            // Start query
            $query = AttendenceSource::query();

            // Filter by organization ID
            if (!empty($organizationId)) {
                $query->where('organization_id', $organizationId);
            }

            // Apply search filter
            if (!empty($search)) {
                $query->where(function ($q) use ($search) {
                    $q->where('attendance_source_name', 'like', '%' . $search . '%')
                        ->orWhere('description', 'like', '%' . $search . '%');
                });
            }

            // If per_page=all → fetch all records
            if ($perPage === 'all') {
                $statusTypes = $query->get();
            }
            // ✅ If per_page is provided & numeric → paginate
            elseif (!empty($perPage) && is_numeric($perPage)) {
                $perPage = (int) $perPage;
                $statusTypes = $query->paginate($perPage, ['*'], 'page', $page);
            }
            // ✅ If per_page is NOT provided → fetch ALL
            else {
                $statusTypes = $query->get();
            }


            return response()->json([
                'message' => 'Attendance Status Types fetched successfully',
                'attendance_source' => $statusTypes
            ]);
        } catch (\Exception $e) {
            \Log::error('Error fetching attendance status types: ' . $e->getMessage());
            return response()->json([
                'message' => 'Failed to fetch attendance status types'
            ], 500);
        }
    }


    public function store(Request $request, $org_id)
    {
        try {
            $request->merge(['organization_id' => $org_id]);
            $validator = Validator::make($request->all(), [
                'organization_id' => 'required|integer|exists:organizations,organization_id',
                'organization_entity_id' => 'nullable|integer|exists:organization_entities,organization_entity_id',
                'organization_configuration_template_id' => 'nullable|integer|exists:organization_configuration_templates,organization_configuration_template_id',
                'attendance_source_name' => 'required|string|max:20',
                'description' => 'nullable|string|max:255',
                'is_active' => 'nullable|boolean',
            ]);
            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()], 422);
            }
            $data = $request->all();
            $source = AttendenceSource::create(array_merge($data));
            return response()->json([
                'message' => 'Employees Attendence Source  Added SuccessFully.',
                'AttendenceSource' => $source
            ], 201);
        } catch (\Exception $e) {
            // Handle any other errors and return a response
            return response()->json([
                'error' => 'Something went wrong. Please try again later.',
                'details' => $e->getMessage()
            ], 500);
        }
    }

    public function show(Request $request, $org_id, $source_id)
    {
        try {
            $request->merge(['organization_id' => $org_id, 'organization_attendance_source_id' => $source_id]);
            $validator = Validator::make($request->all(), [
                'organization_id' => 'required|integer|exists:organizations,organization_id',
                'organization_attendance_source_id' => 'required|integer|exists:organization_attendance_sources,organization_attendance_source_id',
            ]);
            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()], 422);
            }
            $source = AttendenceSource::find($source_id);
            return response()->json([
                'message' => 'Employee Attendence Source Found',
                'AttendenceSource' => $source
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Something went wrong. Please try again later.',
                'details' => $e->getMessage()
            ], 500);
        }
    }

    public function update(Request $request, $org_id, $source_id)
    {
        try {
            $request->merge([
                'organization_id' => $org_id,
                'organization_attendance_source_id' => $source_id
            ]);
            $rules = [
                'organization_id' => 'required|integer|exists:organizations,organization_id',
                'organization_attendance_source_id' => 'required|integer|exists:organization_attendance_sources,organization_attendance_source_id',
                'organization_entity_id' => 'nullable|integer|exists:organization_entities,organization_entity_id',
                'organization_configuration_template_id' => 'nullable|integer|exists:organization_configuration_templates,organization_configuration_template_id',
                'attendance_source_name' => 'required|string|max:20',
                'description' => 'nullable|string|max:255',
                'is_active' => 'nullable|boolean',
            ];
            $validator = Validator::make($request->all(), $rules);
            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()], 422);
            }
            $source = AttendenceSource::find($source_id);
            $source->update($request->only([
                'attendance_source_name',
                'description',
                'is_active',
            ]));
            return response()->json([
                'message' => 'Employee Attendence Source Updated Successfully.',
                'AttendenceSource' => $source
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Something went wrong. Please try again later.',
                'details' => $e->getMessage()
            ], 500);
        }
    }

    public function destroy(Request $request, $org_id, $source_id)
    {
        try {
            $request->merge([
                'organization_id' => $org_id,
                'organization_attendance_source_id' => $source_id
            ]);
            $validator = Validator::make($request->all(), [
                'organization_id' => 'required|integer|exists:organizations,organization_id',
                'organization_attendance_source_id' => 'required|integer|exists:organization_attendance_sources,organization_attendance_source_id',
            ]);
            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()], 422);
            }
            $source = AttendenceSource::find($source_id);
            $source->delete();
            return response()->json([
                'message' => 'Employee Attendence Source Deleted Successfully'
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Something went wrong. Please try again later.',
                'details' => $e->getMessage()
            ], 500);
        }
    }



}
