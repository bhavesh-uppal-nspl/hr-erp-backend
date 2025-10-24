<?php

namespace App\Http\Controllers\OrganizationController;
use App\Models\OrganizationModel\OrganizationEducationDegree;
use App\Models\OrganizationModel\OrganizationEducationLevel;
use App\Models\OrganizationModel\OrganizationEducationStreams;
use Illuminate\Support\Facades\Validator;
use App\Http\Controllers\Controller;

use Exception;
use Illuminate\Http\Request;
use Auth;


class OrganizationEducationStreamController extends Controller
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

        $query = OrganizationEducationStreams::where('organization_id', $org_id);

        $search = $request->input('search');
        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('education_stream_name', 'like', "%{$search}%")
                  ->orWhere('education_stream_short_name', 'like', "%{$search}%");
            });
        }

        $data = $query->orderBy('created_at', 'desc')->get(); // ✅ NO pagination

        return response()->json([
            'message' => 'OK',
            'EducationStream' => $data,
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
                'organization_entity_id' => 'nullable|integer|exists:organization_entities,organization_entity_id',
                'organization_configuration_template_id' => 'nullable|integer|exists:organization_configuration_templates,organization_configuration_template_id',
                'education_stream_name' => 'required|string|max:100',
                'education_stream_short_name' => 'nullable|string|max:20',
                'description' => 'nullable|string|max:255',
                'sort_order' => 'nullable|integer|min:0',
                'is_active' => 'required|boolean',
                'created_at' => 'nullable|date',
            ]);
            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()], 422);
            }
            $stream = OrganizationEducationStreams::create($request->all());
            return response()->json([
                'message' => 'Organization Education Stream Added SuccessFully.',
                'streams' => $stream
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Something went wrong. Please try again later.',
                'details' => $e->getMessage()
            ], 500);
        }
    }

    // display specific organization 
    public function show(Request $request, $org_id, $stream_id)
    {
        try {
            $user = Auth::guard('applicationusers')->user();
            $organizationIds = $user->Client->Organization->pluck('organization_id')->toArray();
            if (!in_array($org_id, $organizationIds)) {
                return response()->json([
                    'messages' => 'unauthorized'
                ], 401);
            }
            $request->merge(['organization_id' => $org_id, 'organization_education_stream_id' => $stream_id]);
            $validator = Validator::make($request->all(), [
                'organization_id' => 'required|integer|exists:organizations,organization_id',
                'organization_education_stream_id' => 'required|integer|exists:organization_education_streams,organization_education_stream_id',
            ]);
            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()], 422);
            }
            $degree = OrganizationEducationStreams::find($stream_id);
            return response()->json([
                'message' => 'Organization Education Streams Found',
                'streams' => $degree
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Something went wrong. Please try again later.',
                'details' => $e->getMessage()
            ], 500);
        }
    }

    // update the orgaization 
    public function update(Request $request, $org_id, $stream_id)
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
                'organization_education_stream_id' => $stream_id,
            ]);
            $rules = [
                'organization_id' => 'required|integer|exists:organizations,organization_id',
                 'organization_education_stream_id' => 'required|integer|exists:organization_education_streams,organization_education_stream_id',
                'education_stream_name' =>'nullable|string|max:100',
                'education_stream_short_name' =>'nullable|string|max:20',
                'description' =>'nullable|string|max:255',
                'sort_order' =>'nullable|integer|min:0',
                'is_active' =>'required|boolean',
            ];
            $validator = Validator::make($request->all(), $rules);
            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()], 422);
            }
            $degree = OrganizationEducationLevel::find($stream_id);
            $degree->update($request->only([
                'education_stream_short_name',
                'education_stream_name',
                'description',
                'sort_order',
                'is_active'
            ]));
            return response()->json([
                'message' => 'Education Stream Update successfully.',
                'level' => $degree
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Something went wrong. Please try again later.',
                'details' => $e->getMessage()
            ], 500);
        }
    }

    // delete the orgaization  
    public function destroy(Request $request, $org_id, $stream_id)
    {
        try {
            $user = Auth::guard('applicationusers')->user();
            $organizationIds = $user->Client->Organization->pluck('organization_id')->toArray();
            if (!in_array($org_id, $organizationIds)) {
                return response()->json([
                    'messages' => 'unauthorized'
                ], 401);
            }
            $request->merge(['organization_id' => $org_id, 'organization_education_stream_id' => $stream_id]);

            $validator = Validator::make($request->all(), [
                'organization_id' => 'required|integer|exists:organizations,organization_id',
                'organization_education_stream_id' => 'required|integer|exists:organization_education_streams,organization_education_stream_id',
            ]);
            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()], 422);
            }
            $degree = OrganizationEducationStreams::find($stream_id);
            $degree->delete();

            return response()->json([
                'message' => 'Education Stream Deleted Successfully'
            ]);

        } catch (\Exception $e) {

            return response()->json([
                'error' => 'Something went wrong. Please try again later.',
                'details' => $e->getMessage()
            ], 500);
        }
    }

}
