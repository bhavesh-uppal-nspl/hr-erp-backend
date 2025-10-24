<?php

namespace App\Http\Controllers\OrganizationController;

use Auth;
use Illuminate\Support\Facades\Validator;
use App\Http\Controllers\Controller;
use App\Models\OrganizationModel\Organization;
use App\Models\OrganizationModel\OrganizationBusinessRegistration;
use Illuminate\Http\Request;

class OrganizationBusinessRegistrationController extends Controller
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


            if ($request->input('mode') == 1) {
                $registartion = OrganizationBusinessRegistration::with('RegistrationType')->where('organization_id', $org_id)->get();

                if ($registartion->isEmpty()) {
                    return response()->json([
                        'message' => 'Registration not found.'
                    ], 404);
                }
                $businessRegistration = $registartion->map(function ($dep) {
                    return [
                        'business_registration_type'=>$dep->RegistrationType->registration_type_name ?? '',
                        'registration_applicable' => $dep->registration_applicable ?? '',
                        'registration_number' => $dep->registration_number ?? '',
                        'registration_date' => $dep->registration_date ?? '',
                        'registration_expiry_date_applicable' => $dep->registration_expiry_date_applicable ?? '',
                        'registration_expiry_date' => $dep->registration_expiry_date  ?? '',
                    ];
                });
                return response()->json($businessRegistration);
            }

            $query = OrganizationBusinessRegistration::with('RegistrationType')->where('organization_id', $org_id);
            $per = $request->input('per_page', 999);
            $search = $request->input('search');
            if ($search) {
                $query->where(function ($q) use ($search) {
                    $q->where('registration_number', 'like', "%{$search}%");
                });
            }
            $data = $query->orderBy('created_at', 'desc')->paginate($per);
            return response()->json([
                'message' => 'OK',
                'organizationbusinessregistration' => $data,

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
                'organization_id' => 'required|exists:organizations,organization_id',
                'organization_business_registration_type_id' => 'required|integer|exists:organization_business_registration_types,organization_business_registration_type_id',
                'registration_applicable' => 'required|boolean',
                // 'registration_document_url' => 'nullable|url',
                'registration_number' => 'nullable|string|max:255',
                'registration_date' => 'nullable|date',
                'registration_expiry_date_applicable' => 'required|boolean',
                'registration_expiry_date' => 'nullable|date|after_or_equal:registration_date',
            ]);
            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()], 422);
            }
            $data = $request->all();



            // Handle file upload
            if ($request->hasFile('registration_document_url') && $request->file('registration_document_url')->isValid()) {
                $file = $request->file('registration_document_url');
                $path = $file->store('registrationdocs', 'public');
                $data['registration_document_url'] = $path; // Store relative path
            } else {
                $data['registration_document_url'] = is_array($request->input('registration_document_url'))
                    ? null
                    : $request->input('registration_document_url', null);
            }


            $registration = OrganizationBusinessRegistration::create($data);

            return response()->json([
                'message' => 'Organization Business registration created successfully.',
                'data' => $registration
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

    public function show(Request $request, $org_id, $business_reg_id)
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
                'organization_business_registration_id' => $business_reg_id
            ]);
            $validator = Validator::make($request->all(), [
                'organization_id' => 'required|exists:organizations,organization_id',
                'organization_business_registration_id' => 'required|exists:organization_business_registrations,organization_business_registration_id',
            ]);
            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()], 422);
            }
            $organizationRegistration = OrganizationBusinessRegistration::findOrFail($business_reg_id);
            $organizationRegistration->load('RegistrationType');

              $registrationDocumentArray = $organizationRegistration->toArray();
          $registrationDocumentArray['registration_document_url'] = $organizationRegistration->registration_document_url;



            return response()->json([
                'message' => 'Organization Business Registration',
                'organizationBusinessRegistration' => $registrationDocumentArray
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Something went wrong: ' . $e->getMessage()
            ], 500);
        }
    }

    public function destroy(Request $request, $org_id, $business_reg_id)
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
                'organization_business_registration_id' => $business_reg_id,
            ]);
            $validator = Validator::make($request->all(), [
                'organization_id' => 'required|exists:organizations,organization_id',
                'organization_business_registration_id' => 'required|exists:organization_business_registrations,organization_business_registration_id',
            ]);
            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()], 422);
            }
            OrganizationBusinessRegistration::findOrFail($business_reg_id)->delete();
            return response()->json([
                'message' => 'Organization Business Registration deleted successfully.'
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Something went wrong: ' . $e->getMessage()
            ], 500);
        }
    }

    public function update(Request $request, $org_id, $business_reg_id)
    {
        try {
            $user = Auth::guard('applicationusers')->user();
            $organizationIds = $user->Client->Organization->pluck('organization_id')->toArray();
            if (!in_array($org_id, $organizationIds)) {
                return response()->json([
                    'messages' => 'unauthorized'
                ], 401);

            }
            $request->merge(['organization_id' => $org_id, 'organization_business_registration_id' => $business_reg_id]);
            $validator = Validator::make($request->all(), [
                'organization_id' => 'required|exists:organizations,organization_id',
                'organization_business_registration_id' => 'required|exists:organization_business_registrations,organization_business_registration_id',
                'organization_business_registration_type_id' => 'nullable|integer|exists:organization_business_registration_types,organization_business_registration_type_id',
                'registration_applicable' => 'nulllable|boolean',
                // 'registration_document_url' => 'nulllable|nullable|url',
                'registration_number' => 'nulllable|nullable|string|max:255',
                'registration_date' => 'nulllable|nullable|date',
                'registration_expiry_date_applicable' => 'nulllable|Boolean|nullable',
                'registration_expiry_date' => 'nulllable|nullable|date|after_or_equal:registration_date',
            ]);
            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()], 422);
            }

            $registration = OrganizationBusinessRegistration::findOrFail($business_reg_id);

            if ($request->hasFile('registration_document_url') && $request->file('registration_document_url')->isValid()) {
                $file = $request->file('registration_document_url');
                $path = $file->store('registrationdocs', 'public');
                $registration['registration_document_url'] = $path;
            }


            $registration->update($request->only([
                'registration_applicable',
                'registration_document_url',
                'registration_number',
                'registration_date',
                'registration_expiry_date',
                'registration_expiry_date_applicable',
                'organization_business_registration_type_id'
            ]));


            return response()->json([
                'message' => 'Organization Business registration updated successfully.',
                'data' => $registration
            ], 200);

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'error' => 'Registration record not found.'
            ], 404);
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

}












