<?php

namespace App\Http\Controllers\OrganizationController;

use App\Models\OrganizationModel\OrganizationBusinessRegistration;
use App\Models\OrganizationModel\OrganizationBusinessRegsitrationType;
use Auth;
use Exception;
use Illuminate\Support\Facades\Validator;
use App\Http\Controllers\Controller;
use App\Models\OrganizationModel\Organization;
use Illuminate\Http\Request;

class OrganizationBusinessRegistrationTypeController extends Controller
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
                'organization_id' => 'required|integer|exists:organizations,organization_id'
            ]);
            if ($validator->fails()) {
                return response()->json([
                    'error' => 'Validation failed',
                    'messages' => $validator->errors()
                ], 422);
            }

            $query = OrganizationBusinessRegsitrationType::where('organization_id', $org_id);
            $per = $request->input('per_page', 10);
            $search = $request->input('search');
              if ($search) {
                $query->where(function ($q) use ($search) {
                    $q->where('business_registration_type_name', 'like', "%{$search}%")
                        ->orWhere('business_registration_type_code', 'like', "%{$search}%")
                        ->orWhere('description', 'like', "%{$search}%");
                });
            }
              $data = $query->orderBy('created_at', 'desc')->paginate($per);
            return response()->json([
                'message' => 'OK',
                'organizationbusinessregistrationtype' => $data,

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
                    'message' => 'Unauthorized'
                ], 401);
            }

            $request->merge(['organization_id' => $org_id]);

            $validator = Validator::make($request->all(), [
                'organization_id' => 'required|exists:organizations,organization_id',

                'business_registration_type_name' => [
                    'required',
                    'string',
                    function ($attribute, $value, $fail) use ($org_id) {
                        $exists = OrganizationBusinessRegsitrationType::where('organization_id', $org_id)
                            ->where('business_registration_type_name', $value)
                            ->exists();

                        if ($exists) {
                            $fail('registration type name already exists .');
                        }
                    }
                ],

                'business_registration_type_code' => [
                    'required',
                    'string',
                    function ($attribute, $value, $fail) use ($org_id) {
                        $exists = OrganizationBusinessRegsitrationType::where('organization_id', $org_id)
                            ->where('business_registration_type_code', $value)
                            ->exists();

                        if ($exists) {
                            $fail('registration type code already exists.');
                        }
                    }
                ],

                'description' => 'nullable|string|max:255',
            ]);

            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()], 422);
            }

            $registrationType = OrganizationBusinessRegsitrationType::create([
                'organization_id' => $org_id,
                'business_registration_type_name' => $request->business_registration_type_name,
                'business_registration_type_code' => $request->business_registration_type_code,
                'description' => $request->description,
            ]);

            return response()->json([
                'message' => 'Organization business registration type created successfully.',
                'data' => $registrationType
            ], 201);

        } catch (\Illuminate\Database\QueryException $e) {
            return response()->json([
                'error' => 'Database error: ' . $e->getMessage()
            ], 500);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Something went wrong: ' . $e->getMessage()
            ], 500);
        }
    }



    public function show(Request $request, $org_id, $business_reg_type_id)
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
                'organization_business_registration_type_id' => $business_reg_type_id
            ]);
            $validator = Validator::make($request->all(), [
                'organization_id' => 'required|exists:organizations,organization_id',
                'organization_business_registration_type_id' => 'required|exists:organization_business_registration_types,organization_business_registration_type_id',
            ]);
            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()], 422);
            }
            $organizationRegistrationtype = OrganizationBusinessRegsitrationType::findOrFail($business_reg_type_id);
            ;
            return response()->json([
                'message' => 'Organization Business Registration type',
                'organizationBusinessRegistrationtype' => $organizationRegistrationtype
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Something went wrong: ' . $e->getMessage()
            ], 500);
        }
    }



    public function update(Request $request, $org_id, $business_reg_type_id)
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
                'organization_business_registration_type_id' => $business_reg_type_id
            ]);

            $rules = [
                'organization_id' => 'required|integer|exists:organizations,organization_id',
                'organization_business_registration_type_id' => 'required|exists:organization_business_registration_types,organization_business_registration_type_id',

               'business_registration_type_name' => [
    'sometimes',
    'nullable',
    'string',
    function ($attribute, $value, $fail) use ($org_id, $business_reg_type_id) {
        $exists = OrganizationBusinessRegsitrationType::where('organization_id', $org_id)
            ->where('business_registration_type_name', $value)
            ->where('organization_business_registration_type_id', '!=', $business_reg_type_id)
            ->exists();
        if ($exists) {
            $fail('Registration type already exists.');
        }
    }
],


                'business_registration_type_code' => [
                    'sometimes',
                    'nullable',
                    'string',
                    'max:10', // optional limit
                    function ($attribute, $value, $fail) use ($org_id, $business_reg_type_id) {
                        if ($value === null) {
                            return;
                        }
                        $exists = OrganizationBusinessRegsitrationType::where('organization_id', $org_id)
                            ->where('business_registration_type_code', $value)
                            ->where('organization_business_registration_type_id', '!=', $business_reg_type_id)
                            ->exists();
                        if ($exists) {
                            $fail('Registration type code already exists.');
                        }
                    }
                ],

                'description' => 'nullable|string|max:255',
            ];

            $validator = Validator::make($request->all(), $rules);

           

            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()], 422);
            }

            $registerType = OrganizationBusinessRegsitrationType::findOrFail($business_reg_type_id);

            $registerType->update($request->only([
                'business_registration_type_name',
                'business_registration_type_code',
                'description'
            ]));

            return response()->json([
                'message' => 'Registration type updated successfully.',
                'registertype' => $registerType
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Something went wrong. Please try again later.',
                'details' => $e->getMessage()
            ], 500);
        }
    }


    public function destroy(Request $request, $org_id, $business_reg_type_id)
    {
        try {
            $user = Auth::guard('applicationusers')->user();
            $organizationIds = $user->Client->Organization->pluck('organization_id')->toArray();
            if (!in_array($org_id, $organizationIds)) {
                return response()->json([
                    'messages' => 'unauthorized'
                ], 401);

            }
            $request->merge(['organization_id' => $org_id, 'organization_business_registration_type_id' => $business_reg_type_id]);
            $validator = Validator::make($request->all(), [
                'organization_id' => 'required|integer|exists:organizations,organization_id',
                'organization_business_registration_type_id' => 'required|exists:organization_business_registration_types,organization_business_registration_type_id',
            ]);
            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()], 422);
            }
            $businessregisterationtype = OrganizationBusinessRegsitrationType::find($business_reg_type_id);
            $businessregisterationtype->delete();
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












