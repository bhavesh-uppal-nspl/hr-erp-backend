<?php

namespace App\Http\Controllers\OrganizationController;

use App\Models\OrganizationModel\Organization;
use Auth;
use Illuminate\Support\Facades\Validator;
use App\Http\Controllers\Controller;
use App\Models\OrganizationModel\OrganizationBusinessDivision;

use Exception;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class OrganizationBusinessDivisionController extends Controller
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
            $request->merge(['organization_id' => $org_id]);
            $validator = Validator::make($request->all(), [
                'organization_id' => 'required|integer|exists:organizations,organization_id',
            ]);
            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()], 422);
            }
            $businessunitdivision = Organization::find($org_id)->divisions;

            return response()->json([
                'message' => 'Organization Business Division',
                'businessDivision' => $businessunitdivision
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
            $request->merge(['organization_id' => $org_id]);
            $validator = Validator::make($request->all(), [
                'organization_id' => 'required|integer|exists:organizations,organization_id',
                'business_division_name' => 'required|string|max:100|unique:organization_business_divisions,business_division_name',
                'business_division_short_name' => 'required|string|max:50|unique:organization_business_divisions,business_division_short_name',
                'description' => 'nullable|string|max:255',
            ]);
            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()], 422);
            }
            $data = $request->all();
            $businessunitdivision = OrganizationBusinessDivision::create(array_merge(['organization_id' => $org_id], $data));
            return response()->json([
                'message' => 'Business Division Added SuccessFully.',
                'businessDivision' => $businessunitdivision
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
    public function show(Request $request, $org_id, $business_division_id)
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
            $user = Auth::guard('applicationusers')->user();
             $organizationIds = $user->Client->Organization->pluck('organization_id')->toArray();
            if (!in_array($org_id, $organizationIds)) {
                return response()->json([
                    'messages' => 'Unauthenticated'
                ], 401);

            }

            $request->merge(['organization_id' => $org_id, 'business_division_id' => $business_division_id]);
            $validator = Validator::make($request->all(), [
                'organization_id' => 'required|integer|exists:organizations,organization_id',
                'business_division_id' => 'required|integer|exists:organization_business_divisions,organization_business_division_id',
            ]);
            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()], 422);
            }
            $businessdivision = OrganizationBusinessDivision::find($business_division_id);
            return response()->json([
                'message' => 'Organization Business Division',
                'businessDivision' => $businessdivision
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Something went wrong. Please try again later.',
                'details' => $e->getMessage()
            ], 500);
        }
    }


    // update the orgaization 
    public function update(Request $request, $org_id, $business_division_id)
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
            $request->merge([
                'organization_id' => $org_id,
                'business_division_id' => $business_division_id
            ]);
            $rules = [
                'organization_id' => 'required|integer|exists:organizations,organization_id',
                'business_division_id' => 'required|integer|exists:organization_business_divisions,organization_business_division_id',
                'business_division_name' => [
                    'sometimes',
                    'string',
                    'max:100',
                    Rule::unique('organization_business_divisions', 'business_division_name')->ignore($business_division_id, 'organization_business_division_id'),
                ],
                'business_division_short_name' => [
                    'sometimes',
                    'string',
                    'max:50',
                    Rule::unique('organization_business_divisions', 'business_division_short_name')->ignore($business_division_id, 'organization_business_division_id'),
                ],
                'description' => 'sometimes|string|max:1000',
            ];
            $validator = Validator::make($request->all(), $rules);
            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()], 422);
            }
            $businessdivision = OrganizationBusinessDivision::find($business_division_id);
            $businessdivision->update($request->only([
                'business_division_name',
                'business_division_short_name',
                'description'
            ]));

            return response()->json([
                'message' => 'Organization Business Division updated successfully.',
                'businessdivision' => $businessdivision
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Something went wrong. Please try again later.',
                'details' => $e->getMessage()
            ], 500);
        }
    }


    // delete the orgaization  
    public function destroy(Request $request, $org_id, $business_division_id)
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
            $request->merge(['organization_id' => $org_id, 'business_division_id' => $business_division_id]);
            $validator = Validator::make($request->all(), [
                'organization_id' => 'required|integer|exists:organizations,organization_id',
                'business_division_id' => 'required|integer|exists:organization_business_divisions,organization_business_division_id',
            ]);
            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()], 422);
            }
            $businessdivision = OrganizationBusinessDivision::find($business_division_id);
            $businessdivision->delete();
            return response()->json([
                'message' => 'Business Division Deleted Successfully'
            ], 200); // or just remove 200 — it's the default


        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Something went wrong. Please try again later.',
                'details' => $e->getMessage()
            ], 500);
        }
    }
}
