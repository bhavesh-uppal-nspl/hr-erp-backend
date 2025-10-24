<?php

namespace App\Http\Controllers\OrganizationController;

use App\Models\OrganizationModel\OrganizationBusinessOwnnershipType;
use Illuminate\Support\Facades\Validator;
use App\Http\Controllers\Controller;
use Exception;
use Illuminate\Http\Request;
use Auth;
use Illuminate\Validation\Rule;

class OrganizationBusinessOwnershipTypeController extends Controller
{


    public function index(Request $request, $org_id)
    {

        try {
            $user = Auth::guard('applicationusers')->user();
            $organizationIds = $user->Client->Organization->pluck('organization_id')->toArray();
            if (!in_array($org_id, $organizationIds)) {
                return response()->json([
                    'messages' => 'Unauthenticated'
                ], 401);
            }

            // Merge org_id from route into request for validation
            $request->merge(['organization_id' => $org_id,]);
            $validator = Validator::make($request->all(), [
                'organization_id' => 'required|integer|exists:organizations,organization_id',

            ]);
            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()], 422);
            }
           $query = OrganizationBusinessOwnnershipType::with('generalownershipCategory')->where('organization_id', $org_id);
           
            $search = $request->input('search');
             if ($search) {
                $query->where(function ($q) use ($search) {
                    $q->where('organization_business_ownership_type_name', 'like', "%{$search}%");
                });
            }
             $data = $query->orderBy('created_at', 'desc')->get();
            return response()->json([
                'message' => 'OK',
                'businessownershiptype' => $data,

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

            // Check if the user has access to this organization
            if (!in_array($org_id, $organizationIds)) {
                return response()->json([
                    'message' => 'Unauthorized'
                ], 401);
            }

            // Merge org ID into request so validation rules can use it
            $request->merge(['organization_id' => $org_id]);

            $validator = Validator::make($request->all(), [
                'organization_id' => 'required|integer|exists:organizations,organization_id',
                'organization_business_ownership_type_name' => [
                    'required',
                    'string',
                    'max:100',
                    Rule::unique('organization_business_ownership_types', 'organization_business_ownership_type_name')
                        ->where(function ($query) use ($request) {
                            return $query->where('organization_id', $request->organization_id);
                        }),
                ],

                'general_business_ownership_type_category_id' => 'required|integer|exists:general_business_ownership_type_categories,general_business_ownership_type_category_id',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'errors' => $validator->errors()
                ], 422);
            }

            $data = $request->only([
                'organization_id',
                'organization_business_ownership_type_name',
                'general_business_ownership_type_category_id',
            ]);

            $ownershipType = OrganizationBusinessOwnnershipType::create($data);

            return response()->json([
                'message' => 'Business Ownership Type Added Successfully.',
                'businessOwnershipType' => $ownershipType
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Something went wrong. Please try again later.',
                'details' => $e->getMessage()
            ], 500);
        }
    }

    public function show(Request $request, $org_id, $ownership_type_id)
    {
        try {
            $user = Auth::guard('applicationusers')->user();
            $organizationIds = $user->Client->Organization->pluck('organization_id')->toArray();
            if (!in_array($org_id, $organizationIds)) {
                return response()->json([
                    'messages' => 'unauthorized'
                ], 401);

            }

            $request->merge(['organization_id' => $org_id, 'organization_business_ownership_type_id' => $ownership_type_id]);
            $validator = Validator::make($request->all(), [
                'organization_id' => 'required|integer|exists:organizations,organization_id',
                'organization_business_ownership_type_id' => 'required|integer|exists:organization_business_ownership_types,organization_business_ownership_type_id',


            ]);
            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()], 422);
            }
            $businessownershiptype = OrganizationBusinessOwnnershipType::find($ownership_type_id);
            //   $businessownershiptype->load('generalownershipCategory');
            return response()->json([
                'message' => 'Organization Business Ownership Type',
                'businessownershiptype' => $businessownershiptype
            ], status: 200);

        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Something went wrong. Please try again later.',
                'details' => $e->getMessage()
            ], 500);
        }
    }

    // update the orgaization 
    public function update(Request $request, $org_id, $ownership_type_id)
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
            $request->merge(['organization_id' => $org_id, 'organization_business_ownership_type_id' => $ownership_type_id]);

            $rules = [
                'organization_id' => 'required|integer|exists:organizations,organization_id',
                'organization_business_ownership_type_id' => 'required|integer|exists:organization_business_ownership_types,organization_business_ownership_type_id',
                'organization_business_ownership_type_name' => [
                    'sometimes',
                    'string',
                    'max:100',
                    Rule::unique('organization_business_ownership_types', 'organization_business_ownership_type_name')
                        ->ignore($ownership_type_id, 'organization_business_ownership_type_id') // allow updating own name
                        ->where(function ($query) use ($request) {
                            return $query->where('organization_id', $request->organization_id);
                        }),
                ],
                'general_business_ownership_type_category_id' => 'required|integer|exists:general_business_ownership_type_categories,general_business_ownership_type_category_id',

            ];
            $validator = Validator::make($request->all(), $rules);
            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()], 422);
            }
            $businessownership = OrganizationBusinessOwnnershipType::find($ownership_type_id);
            $businessownership->update($request->only([
                'organization_business_ownership_type_name',
                'general_business_ownership_type_category_id',
            ]));

            return response()->json([
                'message' => 'Business Ownership Type updated successfully.',
                'businessownership' => $businessownership
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Something went wrong. Please try again later.',
                'details' => $e->getMessage()
            ], 500);
        }
    }

    // delete the orgaization  
    public function destroy(Request $request, $org_id, $ownership_type_id)
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
            $request->merge(['organization_id' => $org_id, 'organization_business_ownership_type_id' => $ownership_type_id]);
            $validator = Validator::make($request->all(), [
                'organization_id' => 'required|integer|exists:organizations,organization_id',
                   'organization_business_ownership_type_id' => 'required|integer|exists:organization_business_ownership_types,organization_business_ownership_type_id',
            ]);
            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()], 422);
            }
            $businessownership = OrganizationBusinessOwnnershipType::find($ownership_type_id);
            $businessownership->delete();
            return response()->json([
                'message' => 'Business Ownership Type Deleted Successfully'
            ], 200); // or just remove 200 — it's the default


        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Something went wrong. Please try again later.',
                'details' => $e->getMessage()
            ], 500);
        }
    }

}
