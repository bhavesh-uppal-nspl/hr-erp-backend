<?php

namespace App\Http\Controllers\OrganizationController;
use App\Models\OrganizationModel\OrganizationShiftRotationPattern;
use Illuminate\Support\Facades\Validator;
use App\Http\Controllers\Controller;
use Exception;
use Illuminate\Http\Request;
use Auth;


class OrganizationShiftRotationPatternController extends Controller
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
                $pattern = OrganizationShiftRotationPattern::where('organization_id', $org_id)->get();
                if ($pattern->isEmpty()) {
                    return response()->json([
                        'message' => 'Pattern not found.'
                    ], 404);
                }
                $mappedPattern = $pattern->map(function ($dep) {
                    return [
                        'pattern_name'=>$dep->pattern_name,
                        'cycle_days' => $dep->cycle_days ?? '',
                        'description' => $dep->description ?? '',
                    
                    ];
                });
                return response()->json($mappedPattern);
            }

            $query = OrganizationShiftRotationPattern::where('organization_id', $org_id);
            $per = $request->input('per_page', 10);
            $search = $request->input('search');
            if ($search) {
                $query->where(function ($q) use ($search) {
                    $q->where('pattern_name', 'like', "%{$search}%")
                        ->orWhere('description', 'like', "%{$search}%");
                });
            }
            $data = $query->orderBy('created_at', 'desc')->paginate($per);
            return response()->json([
                'message' => 'OK',
                'shiftPattern' => $data,
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
                'cycle_days' => 'nullable|integer|min:1|max:31',
                'pattern_name' => 'nullable|string|string|max:100',
                 'description' => 'nullable|string|string|max:255'

            ]);
            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()], 422);
            }
            $data = $request->all();
            OrganizationShiftRotationPattern::create($data);
            return response()->json([
                'message' => 'shift Rotation Pattern  added successfullly.',
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Something went wrong. Please try again later.',
                'details' => $e->getMessage()
            ], 500);
        }
    }

    public function show(Request $request, $org_id, $pattern_id)
    {
        try {
            $user = Auth::guard('applicationusers')->user();
            $organizationIds = $user->Client->Organization->pluck('organization_id')->toArray();
            if (!in_array($org_id, $organizationIds)) {
                return response()->json([
                    'messages' => 'unauthorized'
                ], 401);

            }
            $request->merge(['organization_id' => $org_id, 'organization_work_shift_rotation_pattern_id' => $pattern_id]);
            $validator = Validator::make($request->all(), [
                'organization_id' => 'required|integer|exists:organizations,organization_id',
                'organization_work_shift_rotation_pattern_id' => 'required|integer|exists:organization_work_shift_rotation_patterns,organization_work_shift_rotation_pattern_id',
            ]);
            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()], 422);
            }
            $patterns = OrganizationShiftRotationPattern::find($pattern_id);
            return response()->json([
                'message' => "Workshift Rotation Pattern  found",
                'shiftpatterns' => $patterns
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Something went wrong. Please try again later.',
                'details' => $e->getMessage()
            ], 500);
        }
    }

    public function update(Request $request, $org_id, $pattern_id)
    {
        try {
            $user = Auth::guard('applicationusers')->user();
            $organizationIds = $user->Client->Organization->pluck('organization_id')->toArray();
            if (!in_array($org_id, $organizationIds)) {
                return response()->json([
                    'messages' => 'unauthorized'
                ], 401);
            }
            $request->merge(['organization_id' => $org_id, 'organization_work_shift_rotation_pattern_id' => $pattern_id]);
            $rules = [
                'organization_id' => 'required|integer|exists:organizations,organization_id',
                'organization_work_shift_rotation_pattern_id' => 'required|integer|exists:organization_work_shift_rotation_patterns,organization_work_shift_rotation_pattern_id',
                'cycle_days' => 'nullable|integer|min:1|max:31',
                'pattern_name' => 'nullable|string|string|max:100',
                'description' => 'nullable|string|string|max:255'
            ];
            $validator = Validator::make($request->all(), $rules);

            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()], 422);
            }
            $shiftPattern = OrganizationShiftRotationPattern::find($pattern_id);
            $shiftPattern->update($request->only([
                'cycle_days',
                'pattern_name',
                'description',
            ]));
            return response()->json([
                'message' => 'Organization Shift Rotation Pattern updated successfully.',
                'shiftPattern' => $shiftPattern
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Something went wrong. Please try again later.',
                'details' => $e->getMessage()
            ], 500);
        }
    }

    public function destroy(Request $request, $org_id, $patern_id)
    {
        try {
            $user = Auth::guard('applicationusers')->user();
            $organizationIds = $user->Client->Organization->pluck('organization_id')->toArray();
            if (!in_array($org_id, $organizationIds)) {
                return response()->json([
                    'messages' => 'unauthorized'
                ], 401);
            }
            $request->merge(['organization_id' => $org_id, 'organization_work_shift_rotation_pattern_id' => $patern_id]);
            $validator = Validator::make($request->all(), [
                'organization_id' => 'required|integer|exists:organizations,organization_id',
                'organization_work_shift_rotation_pattern_id' => 'required|integer|exists:organization_work_shift_rotation_patterns,organization_work_shift_rotation_pattern_id',
            ]);
            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()], 422);
            }
            $shiftPattern = OrganizationShiftRotationPattern::find($patern_id);
            $shiftPattern->delete();
            return response()->json([
                'message' => 'Organization shift Pattern  Deleted Successfully'
            ], 200);


        } catch (Exception $e) {
            if ($e instanceof \Illuminate\Database\QueryException && $e->errorInfo[1] == 1451) {
                return response()->json([
                    'error' => 'Cannot delete setting type because it is linked with other records. Please delete dependent records first.'
                ], 409);
            }

            return response()->json([
                'error' => 'Failed to delete Address Type type.',
                'exception' => $e->getMessage() // Optional: remove in production
            ], 500);
        }
    }
}
