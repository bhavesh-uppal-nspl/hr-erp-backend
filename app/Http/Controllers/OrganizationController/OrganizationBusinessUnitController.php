<?php

namespace App\Http\Controllers\OrganizationController;

use Auth;
use Illuminate\Support\Facades\Validator;
use App\Http\Controllers\Controller;
use App\Models\OrganizationModel\Organization;
use App\Models\OrganizationModel\OrganizationBusinessUnit;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class OrganizationBusinessUnitController extends Controller
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
            $businessunit = Organization::find($org_id)->businesUnit;
            return response()->json([
                'businessUnit' => $businessunit
            ], 200);

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

            'business_unit_name' => [
                'sometimes',
                'nullable',
                'string',
                'max:255',
                Rule::unique('business_units', 'business_unit_name')
                    ->where(function ($query) use ($org_id) {
                        return $query->where('organization_id', $org_id);
                    }),
            ],

            'business_unit_short_name' => [
                'sometimes',
                'nullable',
                'string',
                'max:100',
                Rule::unique('business_units', 'business_unit_short_name')
                    ->where(function ($query) use ($org_id) {
                        return $query->where('organization_id', $org_id);
                    }),
            ],

            'description' => 'nullable|string|max:1000',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'errors' => $validator->errors()
            ], 422);
        }

        $data = $request->only([
            'organization_id',
            'business_unit_name',
            'business_unit_short_name',
            'description',
        ]);

        $businessUnit = OrganizationBusinessUnit::create($data);

        return response()->json([
            'message' => 'Organization Business Unit Added Successfully.',
            'location' => $businessUnit
        ], 201);

    } catch (\Exception $e) {
        return response()->json([
            'error' => 'Something went wrong. Please try again later.',
            'details' => $e->getMessage()
        ], 500);
    }
}

    public function show(Request $request, $org_id, $business_unit_id)
    {
        try {
            $user = Auth::guard('applicationusers')->user();
            $organizationIds = $user->Client->Organization->pluck('organization_id')->toArray();
            if (!in_array($org_id, $organizationIds)) {
                return response()->json([
                    'messages' => 'unauthorized'
                ], 401);

            }
            $request->merge(['organization_id' => $org_id, 'business_unit_id' => $business_unit_id]);
            $validator = Validator::make($request->all(), [
                'organization_id' => 'required|integer|exists:organizations,organization_id',
                'business_unit_id' => 'required|integer|exists:organization_business_units,organization_business_unit_id',
            ]);
            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()], 422);
            }
            $businessunit = OrganizationBusinessUnit::find($business_unit_id);
            return response()->json([
                'message' => "Orgaization Business Unit",
                'businessunit' => $businessunit
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Something went wrong. Please try again later.',
                'details' => $e->getMessage()
            ], 500);
        }
    }

    public function update(Request $request, $org_id, $business_unit_id)
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
                'business_unit_id' => $business_unit_id
            ]);
            $rules = [
                'organization_id' => 'required|integer|exists:organizations,organization_id',
                'business_unit_id' => 'required|integer|exists:organization_business_units,organization_business_unit_id',
                'business_unit_name' => [
                    'sometimes',
                    'string',
                    'max:255',
                    Rule::unique('business_units', 'business_unit_name')
                        ->ignore($business_unit_id, 'business_unit_id')
                        ->where(function ($query) use ($request) {
                            return $query->where('organization_id', $request->organization_id);
                        }),
                ],

                'business_unit_short_name' => [
                    'sometimes',
                    'string',
                    'max:100',
                    Rule::unique('business_units', 'business_unit_short_name')
                        ->ignore($business_unit_id, 'business_unit_id')
                        ->where(function ($query) use ($request) {
                            return $query->where('organization_id', $request->organization_id);
                        }),
                ],
                'description' => 'sometimes|string|max:1000',
            ];
            $validator = Validator::make($request->all(), $rules);
            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()], 422);
            }
            $businessunit = OrganizationBusinessUnit::find($business_unit_id);
            $businessunit->update($request->only([
                'business_unit_name',
                'business_unit_short_name',
                'description'
            ]));
            return response()->json([
                'message' => 'Organization Business Unit updated successfully.',
                'businessunit' => $businessunit
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Something went wrong. Please try again later.',
                'details' => $e->getMessage()
            ], 500);
        }
    }

    public function destroy(Request $request, $org_id, $business_unit_id)
    {
        try {
            $user = Auth::guard('applicationusers')->user();
            $organizationIds = $user->Client->Organization->pluck('organization_id')->toArray();
            if (!in_array($org_id, $organizationIds)) {
                return response()->json([
                    'messages' => 'unauthorized'
                ], 401);

            }
            $request->merge(['organization_id' => $org_id, 'business_unit_id' => $business_unit_id]);
            $validator = Validator::make($request->all(), [
                'organization_id' => 'required|integer|exists:organizations,organization_id',
                'business_unit_id' => 'required|integer|exists:organization_business_units,organization_business_unit_id',
            ]);
            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()], 422);
            }
            $businessunit = OrganizationBusinessUnit::find($business_unit_id);
            $businessunit->delete();
            return response()->json([
                'message' => 'Organization Business Unit Deleted Successfully'
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
