<?php

namespace App\Http\Controllers\OrganizationController;
use App\Models\OrganizationModel\OrganizationWorkshiftRotationDays;
use Illuminate\Support\Facades\Validator;
use App\Http\Controllers\Controller;
use Exception;
use Illuminate\Http\Request;
use Auth;
use Illuminate\Validation\Rule;

class OrganizationWorkshiftRotationDaysController extends Controller
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
                $workshift = OrganizationWorkshiftRotationDays::with('WorkshiftPattern', 'workshift')->where('organization_id', $org_id)->get();

                if ($workshift->isEmpty()) {
                    return response()->json([
                        'message' => 'workshift not found.'
                    ], 404);
                }
                $mappedWorkshift = $workshift->map(function ($dep) {
                    return [
                        'work_shift_rotation_pattern'=>$dep->WorkshiftPattern->pattern_name ?? '',
                        'day_number' => $dep->day_number  ?? '',
                        'work_shift' => $dep->workshift->work_shift_name ?? '',
                        'is_off_day' => $dep->is_off_day ?? '',
                    ];
                });
                return response()->json($mappedWorkshift);
            }

            $query = OrganizationWorkshiftRotationDays::with(['WorkshiftPattern', 'workshift'])->where('organization_id', $org_id);
            $per = $request->input('per_page', 10);
            $search = $request->input('search');
            if ($search) {
                $query->where(function ($q) use ($search) {
                    $q->where('work_shift_name', 'like', "%{$search}%");
                });
            }
            $data = $query->orderBy('created_at', 'desc')->paginate($per);
            return response()->json([
                'message' => 'OK',
                'rotationDays' => $data,
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
                'organization_work_shift_rotation_pattern_id' => 'required|integer|exists:organization_work_shift_rotation_patterns,organization_work_shift_rotation_pattern_id',
                'organization_work_shift_id' => 'required|integer|exists:organization_work_shifts,organization_work_shift_id',
                'day_number' => 'nullable|integer|min:1|max:366',
                'is_off_day' => 'nullable|integer|min:0|max:365',
            ]);
            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()], 422);
            }
            $data = $request->all();
            OrganizationWorkshiftRotationDays::create($data);
            return response()->json([
                'message' => 'Organization workshift Rotation Days added successfullly.',
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Something went wrong. Please try again later.',
                'details' => $e->getMessage()
            ], 500);
        }
    }

    public function show(Request $request, $org_id, $rotation_id)
    {
        try {
            $user = Auth::guard('applicationusers')->user();
            $organizationIds = $user->Client->Organization->pluck('organization_id')->toArray();
            if (!in_array($org_id, $organizationIds)) {
                return response()->json([
                    'messages' => 'unauthorized'
                ], 401);

            }
            $request->merge(['organization_id' => $org_id, 'organization_work_shift_rotation_day_id' => $rotation_id]);
            $validator = Validator::make($request->all(), [
                'organization_id' => 'required|integer|exists:organizations,organization_id',
                'organization_work_shift_rotation_day_id' => 'required|integer|exists:organization_work_shift_rotation_days,organization_work_shift_rotation_day_id'
            ]);
            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()], 422);
            }
            $rotationDays = OrganizationWorkshiftRotationDays::find($rotation_id);
            return response()->json($rotationDays);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Something went wrong. Please try again later.',
                'details' => $e->getMessage()
            ], 500);
        }
    }

    public function update(Request $request, $org_id, $rotaton_id)
    {
        try {
            $user = Auth::guard('applicationusers')->user();
            $organizationIds = $user->Client->Organization->pluck('organization_id')->toArray();
            if (!in_array($org_id, $organizationIds)) {
                return response()->json([
                    'messages' => 'unauthorized'
                ], 401);

            }
            $request->merge(['organization_id' => $org_id, 'organization_work_shift_rotation_day_id' => $rotaton_id]);
            $rules = [
                'organization_id' => 'required|integer|exists:organizations,organization_id',
                'organization_work_shift_rotation_day_id' => 'required|integer|exists:organization_work_shift_rotation_days,organization_work_shift_rotation_day_id',
                'organization_work_shift_rotation_pattern_id' => 'required|integer|exists:organization_work_shift_rotation_patterns,organization_work_shift_rotation_pattern_id',
                'organization_work_shift_id' => 'required|integer|exists:organization_work_shifts,organization_work_shift_id',
                'day_number' => 'nullable|integer|min:1|max:366',
                'is_off_day' => 'nullable|boolean',
            ];

            $validator = Validator::make($request->all(), $rules);

            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()], 422);
            }
            $rotationDays = OrganizationWorkshiftRotationDays::find($rotaton_id);
            $rotationDays->update($request->only([
                'organization_work_shift_rotation_pattern_id',
                'organization_work_shift_id',
                'day_number',
                'is_off_day',
            ]));
            return response()->json([
                'message' => 'Organization workshift Rotation Days  updated successfully.',
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Something went wrong. Please try again later.',
                'details' => $e->getMessage()
            ], 500);
        }
    }

    public function destroy(Request $request, $org_id, $rotation_id)
    {
        try {
            $user = Auth::guard('applicationusers')->user();
            $organizationIds = $user->Client->Organization->pluck('organization_id')->toArray();
            if (!in_array($org_id, $organizationIds)) {
                return response()->json([
                    'messages' => 'unauthorized'
                ], 401);

            }
            $request->merge(['organization_id' => $org_id, 'organization_work_shift_rotation_day_id' => $rotation_id]);
            $validator = Validator::make($request->all(), [
                'organization_id' => 'required|integer|exists:organizations,organization_id',
                'organization_work_shift_rotation_day_id' => 'required|integer|exists:organization_work_shift_rotation_days,organization_work_shift_rotation_day_id',
            ]);
            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()], 422);
            }
            $shiftRotation = OrganizationWorkshiftRotationDays::find($rotation_id);
            $shiftRotation->delete();
            return response()->json([
                'message' => 'Organization workshift rotation Deleted Successfully'
            ], 200); 

        } catch (Exception $e) {
            if ($e instanceof \Illuminate\Database\QueryException && $e->errorInfo[1] == 1451) {
                // Foreign key constraint violation
                return response()->json([
                    'error' => 'Cannot delete shifttype rotation days because it is linked with other records. Please delete dependent records first.'
                ], 409); 
            }
            return response()->json([
                'error' => 'Failed to delete work shift .',
                'exception' => $e->getMessage() 
            ], 500);
        }
    }

}
