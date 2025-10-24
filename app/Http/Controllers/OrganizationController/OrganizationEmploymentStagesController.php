<?php
namespace App\Http\Controllers\OrganizationController;
use App\Models\OrganizationModel\OrganizationEmployementStatus;
use App\Models\OrganizationModel\OrganizationEmploymentStages;
use Illuminate\Support\Facades\Validator;
use App\Http\Controllers\Controller;
use Exception;
use Illuminate\Http\Request;
use Auth;
use Illuminate\Validation\Rule;

class OrganizationEmploymentStagesController extends Controller
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
                $stages = OrganizationEmploymentStages::with('Status')->where('organization_id', $org_id)->get();

                if ($stages->isEmpty()) {
                    return response()->json([
                        'message' => 'Stages not found.'
                    ], 404);
                }
                $mappedStages = $stages->map(function ($dep) {
                    return [
                        'employment_stage_name'=>$dep->employment_stage_name ?? '',
                        'description' => $dep->description ?? '',
                        'employment_status_name' => $dep->Status->employment_status_name ?? '',
                       
                    ];
                });
                return response()->json($mappedStages);
            }





            $query = OrganizationEmploymentStages::with('Status')->where('organization_id', $org_id);
            $per = $request->input('per_page', 10);
            $search = $request->input('search');
            if ($search) {
                $query->where(function ($q) use ($search) {
                    $q->where('employment_status_name', 'like', "%{$search}%");

                });
            }
            $data = $query->orderBy('created_at', 'desc')->paginate($per);
            return response()->json([
                'message' => 'OK',
                'stages' => $data,

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
                'organization_employment_status_id' => 'required|integer|exists:organization_employment_statuses,organization_employment_status_id',
                
                'employment_stage_name' => [
                    'required',
                    'string',
                    'max:255',
                    Rule::unique('organization_employment_stages', 'employment_stage_name')
                        ->where(fn($query) => $query->where('organization_id', $request->organization_id)),
                ],
                'description' => 'nullable|string',

            ]);
            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()], 422);
            }
            $data = $request->all();
            $status = OrganizationEmploymentStages::create($data);
            return response()->json([
                'message' => 'Organization Employment stages  Added SuccessFully.',
                'status' => $status
            ], 201);
        } catch (\Exception $e) {
            // Handle any other errors and return a response
            return response()->json([
                'error' => 'Something went wrong. Please try again later.',
                'details' => $e->getMessage()
            ], 500);
        }
    }

    public function show(Request $request, $org_id, $status_id)
    {
        try {
            $user = Auth::guard('applicationusers')->user();
            $organizationIds = $user->Client->Organization->pluck('organization_id')->toArray();
            if (!in_array($org_id, $organizationIds)) {
                return response()->json([
                    'messages' => 'unauthorized'
                ], 401);

            }
            $request->merge(['organization_id' => $org_id, 'organization_employment_status_id' => $status_id]);
            $validator = Validator::make($request->all(), [
                'organization_id' => 'required|integer|exists:organizations,organization_id',
                'organization_employment_stage_id' => 'required|integer|exists:organization_employment_stages,organization_employment_stage_id',
            ]);
            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()], 422);
            }
            $status = OrganizationEmploymentStages::find($status_id);
            $status->load('organization');
            return response()->json([
                'message' => "Orgaization work model  Found",
                'stages' => $status
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Something went wrong. Please try again later.',
                'details' => $e->getMessage()
            ], 500);
        }
    }


    public function update(Request $request, $org_id, $status_id)
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
                'organization_employment_stage_id' => $status_id
            ]);
            $rules = [
                'organization_id' => 'required|integer|exists:organizations,organization_id',
                  'organization_employment_stage_id' => 'required|integer|exists:organization_employment_stages,organization_employment_stage_id',
                'organization_employment_status_id' => 'required|integer|exists:organization_employment_statuses,organization_employment_status_id',
                'employment_stage_name' => [
                    'sometimes',
                    'string',
                    'max:255',
                    Rule::unique('organization_employment_stages', 'employment_stage_name')
                        ->where(function ($query) use ($org_id) {
                            return $query->where('organization_id', $org_id);
                        })
                        ->ignore($status_id, 'organization_employment_status_id'),
                ],
                'description' => 'nullable|string',
            ];
            $validator = Validator::make($request->all(), $rules);
            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()], 422);
            }
            $status = OrganizationEmploymentStages::find($status_id);
            $status->update($request->only([
                'employment_stage_name',
                'description',
                'organization_employment_status_id'
            ]));
            return response()->json([
                'message' => 'Organization employment stages updated successfully.',
                'stages' => $status
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Something went wrong. Please try again later.',
                'details' => $e->getMessage()
            ], 500);
        }
    }

    public function destroy(Request $request, $org_id, $status_id)
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
                'organization_employment_stage_id' => $status_id
            ]);
            $validator = Validator::make($request->all(), [
                'organization_id' => 'required|integer|exists:organizations,organization_id',
               'organization_employment_stage_id' => 'required|integer|exists:organization_employment_stages,organization_employment_stage_id',
            ]);
            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()], 422);
            }
            $status = OrganizationEmploymentStages::find($status_id);
            $status->delete();
            return response()->json([
                'message' => 'Organization Employment Stages Deleted Successfully'
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
