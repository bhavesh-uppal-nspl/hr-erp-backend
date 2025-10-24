<?php

namespace App\Http\Controllers\OrganizationController;

use App\Models\OrganizationModel\OrganizationResidentailOwnershipType;
use Illuminate\Support\Facades\Validator;
use App\Http\Controllers\Controller;
use App\Models\OrganizationModel\Organization;

use Exception;
use Illuminate\Http\Request;
use Auth;
use Illuminate\Validation\Rule;

class OrganizatioRegidentialOwnerTypeController extends Controller
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

            $query = OrganizationResidentailOwnershipType::where('organization_id', $org_id);
            $per = $request->input('per_page', 10);
            $search = $request->input('search');
            if ($search) {
                $query->where(function ($q) use ($search) {
                    $q->where('residential_ownership_type_name', 'like', "%{$search}%")
                        ->orWhere('description', 'like', "%{$search}%");
                });
            }
               $data = $query->orderBy('created_at', 'desc')->paginate($per);
            return response()->json([
                'message' => 'OK',
                'residentialtype' => $data,
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
                'messages' => 'Unauthorized'
            ], 401);
        }

        $request->merge(['organization_id' => $org_id]);

        $validator = Validator::make($request->all(), [
            'organization_id' => 'required|integer|exists:organizations,organization_id',
            'residential_ownership_type_name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('organization_residential_ownership_types', 'residential_ownership_type_name')
                    ->where('organization_id', $org_id)
            ],
            'description' => 'nullable|string|max:1000',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $ownershipType = OrganizationResidentailOwnershipType::create($request->all());

        return response()->json([
            'message' => 'Residential ownership type created successfully.',
            'data' => $ownershipType
        ], 201);
    } catch (\Exception $e) {
        return response()->json([
            'error' => 'Something went wrong. Please try again later.',
            'details' => $e->getMessage()
        ], 500);
    }
}

    // display specific organization 
    public function show(Request $request, $org_id, $residential_type_id)
    {
        try {
            $user = Auth::guard('applicationusers')->user();
            $organizationIds = $user->Client->Organization->pluck('organization_id')->toArray();
            if (!in_array($org_id, $organizationIds)) {
                return response()->json([
                    'messages' => 'unauthorized'
                ], 401);

            }
            $request->merge(['organization_id' => $org_id, 'organization_residential_ownership_type_id' => $residential_type_id]);
            $validator = Validator::make($request->all(), [
                'organization_id' => 'required|integer|exists:organizations,organization_id',
                'organization_residential_ownership_type_id' => 'required|integer|exists:organization_residential_ownership_types,organization_residential_ownership_type_id',
            ]);
            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()], 422);
            }
            $residentialtype = OrganizationResidentailOwnershipType::find($residential_type_id);
            return response()->json([
                'message' => "Residential Ownership type  Found",
                'residentialtype' => $residentialtype
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Something went wrong. Please try again later.',
                'details' => $e->getMessage()
            ], 500);
        }
    }

    // update the orgaization 
    public function update(Request $request, $org_id, $residential_type_id)
    {
        try {
            $user = Auth::guard('applicationusers')->user();
            $organizationIds = $user->Client->Organization->pluck('organization_id')->toArray();
            if (!in_array($org_id, $organizationIds)) {
                return response()->json([
                    'messages' => 'unauthorized'
                ], 401);

            }
            $request->merge(['organization_id' => $org_id, 'organization_residential_ownership_type_id' => $residential_type_id]);
            $rules = [
                'organization_id' => 'required|integer|exists:organizations,organization_id',
                'organization_residential_ownership_type_id' => 'required|integer|exists:organization_residential_ownership_types,organization_residential_ownership_type_id',
                'residential_ownership_type_name' => [
                    'sometimes',
                    'string',
                    'max:255',
                    Rule::unique('organization_residential_ownership_types', 'residential_ownership_type_name')
                        ->where(function ($query) use ($org_id) {
                            $query->where('organization_id', $org_id);
                        })
                        ->ignore($residential_type_id, 'organization_residential_ownership_type_id')
                ],
                'description' => 'nullable|string|max:1000',
            ];
            $validator = Validator::make($request->all(), $rules);
            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()], 422);
            }
            $residentialtype = OrganizationResidentailOwnershipType::find($residential_type_id);
            $residentialtype->update($request->only([
                'residential_ownership_type_name',
                'description'
            ]));
            return response()->json([
                'message' => 'Residential Ownership Type updated successfully.',
                'residentialtype' => $residentialtype
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Something went wrong. Please try again later.',
                'details' => $e->getMessage()
            ], 500);
        }
    }


    // delete the orgaization  
    public function destroy(Request $request, $org_id, $residential_type_id)
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
                'organization_residential_ownership_type_id' => $residential_type_id
            ]);
            $rules = [
                'organization_id' => 'required|integer|exists:organizations,organization_id',
                'organization_residential_ownership_type_id' => 'required|integer|exists:organization_residential_ownership_types,organization_residential_ownership_type_id',
            ];
            $validator = Validator::make($request->all(), $rules);
            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()], 422);
            }
            $residentialtype = OrganizationResidentailOwnershipType::find($residential_type_id);
            $residentialtype->delete();
            return response()->json([
                'message' => 'Organization Residential Ownership Type  Deleted Successfully'
            ], 200); // or just remove 200 — it's the default
        } catch (Exception $e) {
            if ($e instanceof \Illuminate\Database\QueryException && $e->errorInfo[1] == 1451) {
                // Foreign key constraint violation
                return response()->json([
                    'error' => 'Cannot delete Residential ownership type because it is linked with other records. Please delete dependent records first.'
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
