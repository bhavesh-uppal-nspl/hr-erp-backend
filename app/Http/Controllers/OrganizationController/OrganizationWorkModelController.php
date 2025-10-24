<?php

namespace App\Http\Controllers\OrganizationController;
use App\Models\OrganizationModel\OrganizationWorkModel;
use Illuminate\Support\Facades\Validator;
use App\Http\Controllers\Controller;
use Exception;
use Illuminate\Http\Request;
use Auth;
use Illuminate\Validation\Rule;

class OrganizationWorkModelController extends Controller
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

            $query = OrganizationWorkModel::where('organization_id', $org_id);
            $per = $request->input('per_page', 10);
            $search = $request->input('search');
            if ($search) {
                $query->where(function ($q) use ($search) {
                    $q->where('work_model_name', 'like', "%{$search}%");
                });
            }
            $data = $query->orderBy('created_at', 'desc')->paginate($per);
            return response()->json([
                'message' => 'OK',
                'workmodel' => $data,

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
                'work_model_name' => [
                    'required',
                    'string',
                    'max:255',
                    Rule::unique('organization_work_models')
                        ->where(function ($query) use ($org_id) {
                            return $query->where('organization_id', $org_id);
                        }),
                ],

            ]);
            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()], 422);
            }
            $workmodel = OrganizationWorkModel::create($request->all());
            return response()->json([
                'message' => 'Organization Work Model Added SuccessFully.',
                'workmodel' => $workmodel
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
    public function show(Request $request, $org_id, $work_model__id)
    {
        try {
            $user = Auth::guard('applicationusers')->user();
            $organizationIds = $user->Client->Organization->pluck('organization_id')->toArray();
            if (!in_array($org_id, $organizationIds)) {
                return response()->json([
                    'messages' => 'unauthorized'
                ], 401);

            }
            $request->merge(['organization_id' => $org_id, 'organization_work_model_id' => $work_model__id]);
            $validator = Validator::make($request->all(), [
                'organization_id' => 'required|integer|exists:organizations,organization_id',
                'organization_work_model_id' => 'required|integer|exists:organization_work_models,organization_work_model_id',
            ]);
            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()], 422);
            }
            $workmodel = OrganizationWorkModel::find($work_model__id);
            return response()->json([
                'message' => 'Organization Work Model Found',
                'workmodel' => $workmodel
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Something went wrong. Please try again later.',
                'details' => $e->getMessage()
            ], 500);
        }
    }

    // update the orgaization 
    public function update(Request $request, $org_id, $work_model__id)
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
                'oragnization_work_model_id' => $work_model__id
            ]);
            $rules = [
                'organization_id' => 'required|integer|exists:organizations,organization_id',
                'organization_work_model_id' => 'required|integer|exists:organization_work_models,organization_work_model_id',
                'work_model_name' => [
                    'required',
                    'string',
                    'max:255',
                    Rule::unique('organization_work_models', 'work_model_name')
                        ->where(function ($query) use ($org_id) {
                            return $query->where('organization_id', $org_id);
                        })
                        ->ignore($work_model__id, 'organization_work_model_id'),
                ],
            ];
            $validator = Validator::make($request->all(), $rules);
            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()], 422);
            }
            $workmodel = OrganizationWorkModel::find($work_model__id);
            $workmodel->update($request->only([
                'work_model_name',

            ]));

            return response()->json([
                'message' => 'Work Model updated successfully.',
                'workmodel' => $workmodel
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Something went wrong. Please try again later.',
                'details' => $e->getMessage()
            ], 500);
        }
    }

    // delete the orgaization  
    public function destroy(Request $request, $org_id, $work_model_id)
    {
        try {
            $user = Auth::guard('applicationusers')->user();
            $organizationIds = $user->Client->Organization->pluck('organization_id')->toArray();

            if (!in_array($org_id, $organizationIds)) {
                return response()->json([
                    'message' => 'Unauthorized'
                ], 401);
            }

            $request->merge([
                'organization_id' => $org_id,
                'organization_work_model_id' => $work_model_id
            ]);

            $validator = Validator::make($request->all(), [
                'organization_id' => 'required|integer|exists:organizations,organization_id',
                'organization_work_model_id' => 'required|integer|exists:organization_work_models,organization_work_model_id',
            ]);

            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()], 422);
            }

            $workmodel = OrganizationWorkModel::find($work_model_id);

            if (!$workmodel) {
                return response()->json([
                    'message' => 'Work model not found.'
                ], 404);
            }

            $workmodel->delete();

            return response()->json([
                'message' => 'Work Model deleted successfully.'
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Something went wrong. Please try again later.',
                'details' => $e->getMessage()
            ], 500);
        }
    }
}
