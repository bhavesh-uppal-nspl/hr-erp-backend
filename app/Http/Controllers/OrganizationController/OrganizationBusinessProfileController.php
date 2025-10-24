<?php

namespace App\Http\Controllers\OrganizationController;

use App\Models\OrganizationModel\OrganizationBusinessOwnnershipType;
use App\Models\OrganizationModel\OrganizationBusinessProfile;
use Illuminate\Support\Facades\Validator;
use App\Http\Controllers\Controller;
use Exception;
use Illuminate\Http\Request;
use Auth;

class OrganizationBusinessProfileController extends Controller
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
            $businessprofile = OrganizationBusinessProfile::where('organization_id', $org_id)
                ->get();

            $businessprofile->load('generalCategory', 'generalIndustry','businessOwnership');

            return response()->json([
                'message' => 'Organization Business profile Type',
                'businessprofile' => $businessprofile
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Something went wrong. Please try again later.',
                'details' => $e->getMessage()
            ], 500);
        }
    }


    public function show(Request $request, $org_id, $profile_id)
    {
        try {
            
            $user = Auth::guard('applicationusers')->user();
            $organizationIds = $user->Client->Organization->pluck('organization_id')->toArray();
            if (!in_array($org_id, $organizationIds)) {
                return response()->json([
                    'messages' => 'unauthorized'
                ], 401);

            }
            $request->merge(['organization_id' => $org_id, 'organization_business_profile_id' => $profile_id]);
            $validator = Validator::make($request->all(), [
                'organization_id' => 'required|integer|exists:organizations,organization_id',
                'organization_business_profile_id' => 'required|integer|exists:organization_business_profiles,organization_business_profile_id',
              

            ]);
            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()], 422);
            }
            $businessprofile = OrganizationBusinessProfile::find($profile_id);
            $businessprofile->load('generalCategory', 'generalIndustry','businessOwnership');
           
            return response()->json([
                'message' => 'Organization Business Profile',
                'businessprofile' => $businessprofile
            ], status: 200);

        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Something went wrong. Please try again later.',
                'details' => $e->getMessage()
            ], 500);
        }
    }


    // update the orgaization 
    public function update(Request $request, $org_id, $profile_id)
    {
        try {
          
            $user = Auth::guard('applicationusers')->user();
            $organizationIds = $user->Client->Organization->pluck('organization_id')->toArray();
            if (!in_array($org_id, $organizationIds)) {
                return response()->json([
                    'messages' => 'unauthorized'
                ], 401);

            }
            $request->merge(['organization_id' => $org_id, 'organization_business_profile_id' => $profile_id]);

            $rules = [
                'organization_id' => 'required|integer|exists:organizations,organization_id',
                'organization_business_profile_id' => 'required|integer|exists:organization_business_profiles,organization_business_profile_id',
                'organization_business_ownership_type_id' => 'sometimes|integer|exists:organization_business_ownership_types,organization_business_ownership_type_id',
                 'establishment_date' => 'nullable|date_format:d-m-Y|before_or_equal:today',
                'number_of_employees' => 'nullable|integer|min:1',
                ];
            $validator = Validator::make($request->all(), $rules);
            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()], 422);
            }
            $businessprofile = OrganizationBusinessProfile::find($profile_id);
            $businessprofile->update($request->only([
                'organization_business_ownership_type_id',
                'establishment_date',
                'number_of_employees'
            ]));

            $businessprofile->load('generalCategory', 'generalIndustry','businessOwnership');

           
            return response()->json([
                'message' => 'Organization Business Profile updated successfully.',
                'businessprofile' => $businessprofile
            ], 201);

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
            $request->merge(['organization_id' => $org_id,]);
            $validator = Validator::make($request->all(), [
                'organization_id' => 'required|integer|exists:organizations,organization_id',
                'establishment_date' => 'nullable|date_format:d-m-Y|before_or_equal:today',
                'number_of_employees' => 'nullable|integer|min:1',
                'organization_business_ownership_type_id' => 'nullable|integer|exists:organization_business_ownership_types,organization_business_ownership_type_id',
            ]);
            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()], 422);
            }
            $data = $request->all();
            $businessprofile = OrganizationBusinessProfile::create(array_merge(['organization_id' => $org_id], $data));
            return response()->json([
                'message' => 'Business Profile Added SuccessFully.',
                'businessprofile' => $businessprofile
            ], 201);




        } catch (\Exception $e) {
            // Handle any other errors and return a response
            return response()->json([
                'error' => 'Something went wrong. Please try again later.',
                'details' => $e->getMessage()
            ], 500);
        }
    }

}
