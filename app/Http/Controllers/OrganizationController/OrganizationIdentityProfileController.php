<?php

namespace App\Http\Controllers\OrganizationController;

use App\Models\OrganizationModel\OrganizationIdentityProfile;
use Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use App\Http\Controllers\Controller;
use App\Models\OrganizationModel\Organization;
use Illuminate\Http\Request;   

class OrganizationIdentityProfileController extends Controller
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
                return response()->json([
                    'error' => 'Validation failed',
                    'messages' => $validator->errors()
                ], 422);
            }

            $organization = Organization::find($org_id)->profile;

            return response()->json([
                "message" => 'Organization Profile',
                'Profile' => $organization
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

            $request->merge(['organization_id' => $org_id]);
            $rules = [
                'organization_id' => 'required|integer|exists:organizations,organization_id',
                'website' => 'nullable|url|max:2083',
                'email' => 'nullable|email|max:100',
                'phone' => ['required', 'regex:/^[0-9]+$/', 'max:20'],
                'logo_url' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048', // accept file or url
              
              
           
            ];

            $validator = Validator::make($request->all(), $rules);

            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()], 422);
            }

            $data = $request->only(['website', 'email', 'phone']);

            // Handle logo file upload
            if ($request->hasFile('logo_url') && $request->file('logo_url')->isValid()) {
                $file = $request->file('logo_url');
                $path = $file->store('logos', 'public');
                $data['logo_url'] = asset('storage/' . $path);
            } else {
                $data['logo_url'] = $request->input('logo_url'); // use direct URL if given
            }

            $data['organization_id'] = $org_id;
        

            $profile = OrganizationIdentityProfile::create($data);

            return response()->json([
                'message' => 'Organization profile information saved successfully.',
                'data' => $profile
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Something went wrong. Please try again later.',
                'details' => $e->getMessage()
            ], 500);
        }
    }

    public function update(Request $request, $org_id, $profile_id,)
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
                'organization_identity_profile_id' => $profile_id,
          
            ]);

            // Validation rules
            $rules = [
                'organization_id' => 'required|integer|exists:organizations,organization_id',
                'organization_identity_profile_id' => 'required|integer|exists:organization_identity_profiles,organization_identity_profile_id',
                'website' => 'sometimes|nullable|url|max:2083',
                'email' => 'sometimes|nullable|email|max:100',
                'phone' => 'sometimes|nullable|string|max:20',
                'logo_url' => 'sometimes|nullable|file|image|mimes:jpeg,png,jpg,gif|max:2048',
              
            
            ];

            // Validate request
            $validator = Validator::make($request->all(), $rules);

            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()], 422);
            }

            // Find the profile
            $organizationProfile = OrganizationIdentityProfile::find($profile_id);
            if (!$organizationProfile) {
                return response()->json([
                    'message' => 'Organization profile not found.'
                ], 404);
            }

            $data = $request->only(['website', 'email', 'phone']);

            // Handle file upload
            if ($request->hasFile('logo_url')) {
                $file = $request->file('logo_url');

                if ($file->isValid()) {
                    // Delete old logo if local
                    if ($organizationProfile->logo_url && str_starts_with($organizationProfile->logo_url, asset('storage'))) {
                        $oldPath = str_replace(asset('storage') . '/', '', $organizationProfile->logo_url);
                        Storage::disk('public')->delete($oldPath);
                    }

                    // Store and update new logo path
                    $path = $file->store('logos', 'public');
                    $data['logo_url'] = asset('storage/' . $path);
                } else {
                    return response()->json([
                        'error' => 'Uploaded logo file is invalid.'
                    ], 400);
                }

            } else {
                // Keep existing logo if not updated
                $data['logo_url'] = $organizationProfile->logo_url;
            }

            // Update the profile
            $organizationProfile->update($data);

            return response()->json([
                'message' => 'Organization Profile updated successfully.',
                'profile' => $organizationProfile
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


            $request->merge([
                'organization_id' => $org_id,
                'organization_identity_profile_id' => $profile_id,
                
            ]);

            $validator = Validator::make($request->all(), [
                'organization_id' => 'required|integer|exists:organizations,organization_id',
                'organization_identity_profile_id' => 'required|integer|exists:organization_identity_profiles,organization_identity_profile_id',
             
           
            ]);

            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()], 422);
            }

            $organizationProfile = OrganizationIdentityProfile::find($profile_id);

            return response()->json([
                'message' => "Organization Profile Data",
                'data' => $organizationProfile
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Something went wrong. Please try again later.',
                'details' => $e->getMessage()
            ], 500);
        }
    }

//     public function destroy(Request $request, $org_id, $profile_id)
//   {
//         try {    $user = Auth::guard('applicationusers')->user();
//              $organizationIds = $user->Client->Organization->pluck('organization_id')->toArray();
//             if (!in_array($org_id, $organizationIds)) {
//                 return response()->json([
//                     'messages' => 'unauthorized'
//                 ], 401);

//             }
//                $user = Auth::guard('applicationusers')->user();
//              $organizationIds = $user->Client->Organization->pluck('organization_id')->toArray();
//             if (!in_array($org_id, $organizationIds)) {
//                 return response()->json([
//                     'messages' => 'unauthorized'
//                 ], 401);

//             }

//             $request->merge([
//                 'organization_id' => $org_id,
//                 'organization_identity_profile_id' => $profile_id,
               
//             ]);
//             $validator = Validator::make($request->all(), [
//                 'organization_id' => 'required|integer|exists:organizations,organization_id',
//                 'organization_identity_profile_id' => 'required|integer|exists:organization_identity_profiles,organization_identity_profile_id',
            
//                  'organization_entity_id'=>'required|integer|exists:organization_entities,organization_entity_id'
            
//             ]);
//             if ($validator->fails()) {
//                 return response()->json(['errors' => $validator->errors()], 422);
//             }
//             $organizationProfile = OrganizationIdentityProfile::find($profile_id);
//             if (!$organizationProfile) {
//                 return response()->json([
//                     'message' => 'Organization profile not found.'
//                 ], 404);
//             }
//             $organizationProfile->delete();
//             return response()->json([
//                 'message' => 'Organization profile deleted successfully.'
//             ], 200);

//         } catch (\Exception $e) {
//             return response()->json([
//                 'error' => 'Something went wrong. Please try again later.',
//                 'details' => $e->getMessage()
//             ], 500);
//         }
//     }





}

