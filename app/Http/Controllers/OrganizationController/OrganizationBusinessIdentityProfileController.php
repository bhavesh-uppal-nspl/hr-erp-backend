<?php

namespace App\Http\Controllers\OrganizationController;

use App\Models\OrganizationModel\OrganizationBusinessProfile;
use App\Models\OrganizationModel\OrganizationIdentityProfile;
use Auth;
use Illuminate\Support\Facades\Validator;
use App\Http\Controllers\Controller;
use Exception;
use Illuminate\Http\Request;

class OrganizationBusinessIdentityProfileController extends Controller
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


            // Step 5: Retrieve business and identity profiles
            $businessProfile = OrganizationBusinessProfile::where('organization_id', $org_id)->first();
            $businessProfile->load('generalCategory', 'generalIndustry', 'businessOwnership');
            $identityProfile = OrganizationIdentityProfile::where('organization_id', $org_id)->first();


            $mergedProfile = array_merge(
                optional($identityProfile)->toArray() ?? [],
                optional($businessProfile)->toArray() ?? []
            );

            return response()->json([
                'profile' => $mergedProfile,

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
            // store data in both business identity controller   
            $request->merge(['organization_id' => $org_id]);
            $validator = Validator::make($request->all(), [
                'organization_id' => 'required|integer|exists:organizations,organization_id',
                'establishment_date' => 'sometimes|nullable|date_format:Y-m-d|before_or_equal:today',
                'number_of_employees' => 'sometimes|nullable|integer|min:1',
                'organization_business_ownership_type_id' => 'sometimes|integer|exists:organization_business_ownership_types,organization_business_ownership_type_id',
                'website' => 'nullable|url|max:2083',
                'email' => 'nullable|email|max:100',
                'phone' => ['required', 'regex:/^[0-9]+$/', 'max:20'],

            ]);
            if ($validator->fails()) {
                return response()->json([
                    'errors' => $validator->errors()
                ], 422);
            }
            $businessData = $request->only([
                'organization_id',
                'establishment_date',
                'number_of_employees',
                'organization_business_ownership_type_id'
            ]);

            OrganizationBusinessProfile::updateOrCreate(
                ['organization_id' => $org_id],
                $businessData
            );


            // create profile identity 
            $identityData = $request->only([
                'organization_id',
                'website',
                'phone',
                'email'
            ]);

            // store the image url 
            if ($request->hasFile('logo_url')) {
                $img = $request->logo_url;
                $ext = $img->getClientOriginalExtension();
                $imageName = 'org_savings' . time() . '.' . $ext;
                $img->move(public_path() . '/org_img/', $imageName);
                $identityData['logo_url'] = asset('org_img/' . $imageName);
            }

            OrganizationIdentityProfile::updateOrCreate(
                ['organization_id' => $org_id],
                $identityData
            );

            return response()->json([
                'message' => 'Organization  Profile saved Successfully.',
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Something went wrong. Please try again later.',
                'details' => $e->getMessage()
            ], 500);
        }
    }

    // public function show(Request $request, $org_id, $business_unit_id)
    // {
    //     try {
    //         $user = Auth::guard('applicationusers')->user();
    //         $organizationIds = $user->Client->Organization->pluck('organization_id')->toArray();
    //         if (!in_array($org_id, $organizationIds)) {
    //             return response()->json([
    //                 'messages' => 'unauthorized'
    //             ], 401);

    //         }
    //         $request->merge(['organization_id' => $org_id, 'business_unit_id' => $business_unit_id]);
    //         $validator = Validator::make($request->all(), [
    //             'organization_id' => 'required|integer|exists:organizations,organization_id',
    //             'business_unit_id' => 'required|integer|exists:organization_business_units,organization_business_unit_id',
    //         ]);
    //         if ($validator->fails()) {
    //             return response()->json(['errors' => $validator->errors()], 422);
    //         }
    //         $businessunit = OrganizationBusinessUnit::find($business_unit_id);
    //         return response()->json([
    //             'message' => "Orgaization Business Unit",
    //             'businessunit' => $businessunit
    //         ], 200);

    //     } catch (\Exception $e) {
    //         return response()->json([
    //             'error' => 'Something went wrong. Please try again later.',
    //             'details' => $e->getMessage()
    //         ], 500);
    //     }
    // }

    public function update(Request $request, $org_id)
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

            $rules = [
                'organization_id' => 'required|integer|exists:organizations,organization_id',
                'organization_business_profile_id' => 'sometimes|integer|exists:organization_business_profiles,organization_business_profile_id',
                'organization_identity_profile_id' => 'sometimes|integer|exists:organization_identity_profiles,organization_identity_profile_id',

                'establishment_date' => 'sometimes|nullable|date_format:Y-m-d|before_or_equal:today',
                'number_of_employees' => 'sometimes|nullable|integer|min:1',
                'organization_business_ownership_type_id' => 'sometimes|integer|exists:organization_business_ownership_types,organization_business_ownership_type_id',

                'website' => 'nullable|url|max:2083',
                'logo_url' => 'sometimes|nullable|file',
                'email' => 'nullable|email|max:100',
                'phone' => ['nullable', 'regex:/^[0-9]+$/', 'max:20'],
            ];

            $validator = Validator::make($request->all(), $rules);
            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()], 422);
            }

            // Step 1: Update Business Profile if ID is provided
            $businessProfile = null;
            if ($request->has('organization_business_profile_id')) {
                $businessProfile = OrganizationBusinessProfile::where('organization_business_profile_id', $request->organization_business_profile_id)
                    ->where('organization_id', $org_id)
                    ->first();

                if (!$businessProfile) {
                    return response()->json(['error' => 'Business profile not found for this organization.'], 404);
                }

                $businessProfile->update($request->only([
                    'establishment_date',
                    'number_of_employees',
                    'organization_business_ownership_type_id'
                ]));
            }

            // Step 2: Update Identity Profile if ID is provided
            $identityProfile = null;
            if ($request->has('organization_identity_profile_id')) {
                $identityProfile = OrganizationIdentityProfile::where('organization_identity_profile_id', $request->organization_identity_profile_id)
                    ->where('organization_id', $org_id)
                    ->first();

                if (!$identityProfile) {
                    return response()->json(['error' => 'Identity profile not found for this organization.'], 404);
                }

                if ($request->hasFile('logo_url')) {
                    if ($identityProfile->logo_url) {
                        $oldFile = public_path(str_replace(asset('/'), '', $identityProfile->logo_url));
                        if (file_exists($oldFile)) {
                            unlink($oldFile);
                        }
                    }

                    $img = $request->logo_url;
                    $imageName = 'org_savings-' . time() . '.' . $img->getClientOriginalExtension();
                    $img->move(public_path('/org_img/'), $imageName);
                    $data['logo_url'] = asset('org_img/' . $imageName);
                }

                $identityProfile->update($request->only([
                    'website',
                    'email',
                    'phone',
                    'logo_url'
                ]));
            }

            return response()->json([
                'message' => 'Organization profiles updated successfully.',
                'businessProfile' => $businessProfile,
                'identityProfile' => $identityProfile
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Something went wrong. Please try again later.',
                'details' => $e->getMessage()
            ], 500);
        }
    }



}
