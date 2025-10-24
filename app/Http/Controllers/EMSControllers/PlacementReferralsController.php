<?php

namespace App\Http\Controllers\EMSControllers;

use App\Http\Controllers\Controller;
use App\Models\EMSModels\ClassAttendence;
use App\Models\EMSModels\FeeInstallments;
use App\Models\EMSModels\PlacementReferrals;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class PlacementReferralsController extends Controller
{

    public function index(Request $request)
    {

        try {
            $perPage = $request->get('per_page', 10); // Keep as string for "all" comparison
            $page = (int) $request->get('page', 1);
            $search = $request->get('search');
            $organizationId = $request->get('organization_id');

            $query = PlacementReferrals::with('student', 'admission', 'trainingProgram', 'agency', 'company');

            // Filter by organization_id
            if (!empty($organizationId)) {
                $query->where('organization_id', $organizationId);
            }

            // Search filter
            if (!empty($search)) {
                $query->where(function ($q) use ($search) {
                    $q->where('referral_status', 'like', '%' . $search . '%');
                });
            }

            // Handle pagination or return all
            $placementReferral = ($perPage === 'all')
                ? $query->get()
                : $query->paginate((int) $perPage, ['*'], 'page', $page);

            return response()->json([
                'message' => 'Placement Referral fetched successfully',
                'data' => $placementReferral
            ]);
        } catch (\Exception $e) {
            \Log::error('Error fetching Placement Referral: ' . $e->getMessage());
            return response()->json(['message' => 'Failed to fetch Placement Referral'], 500);
        }

    }


    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'organization_id' => 'required|integer|exists:organizations,organization_id',
            'organization_entity_id' => 'sometimes|nullable|integer|exists:organization_entities,organization_entity_id',

            'organization_ems_student_id' => 'required|integer|exists:organization_ems_students,organization_ems_student_id',
            'organization_ems_admission_id' => 'required|integer|exists:organization_ems_admissions,organization_ems_admission_id',
            'training_program_id' => 'required|integer|exists:organization_ems_training_programs,organization_ems_training_program_id',
            'organization_ems_recruitment_agency_id' => 'sometimes|nullable|integer|exists:organization_ems_recruitment_agencies,organization_ems_recruitment_agency_id',
            'organization_ems_company_id' => 'sometimes|nullable|integer|exists:organization_ems_companies,organization_ems_company_id',
            'referral_date' => 'required|date',
            'referral_status' => 'sometimes|in:Referred,In Process,Interview Scheduled,Offer Received,Rejected,Placed',
            'job_role' => 'nullable|string|max:100',
            'package_amount' => 'nullable|numeric|min:0',
            'currency_code' => 'required|string|max:10',
            'joining_date' => 'nullable|date|after_or_equal:referral_date',
            'remarks' => 'nullable|string|max:500',
        ]);


        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            $placementReferral = PlacementReferrals::create($request->all());

            return response()->json([
                'success' => true,
                'message' => 'Placement Referral created successfully.',
                'data' => $placementReferral,
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to create Placement Referral.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function show($id)
    {
        try {
            $placementReferral = PlacementReferrals::with('student', 'admission', 'trainingProgram', 'agency', 'company')->findOrFail($id);

            return response()->json([
                'success' => true,
                'data' => $placementReferral,
            ]);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Placement Referral not found.',
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error fetching Placement Referral.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }


    public function update(Request $request, $id)
    {
        try {
            $placementReferral = PlacementReferrals::findOrFail($id);

            $validator = Validator::make($request->all(), [
                'organization_id' => 'sometimes|integer|exists:organizations,organization_id',
                'organization_entity_id' => 'sometimes|integer|exists:organization_entities,organization_entity_id',
                'organization_ems_student_id' => 'sometimes|integer|exists:organization_ems_students,organization_ems_student_id',
                'organization_ems_admission_id' => 'sometimes|integer|exists:organization_ems_admissions,organization_ems_admission_id',
                'training_program_id' => 'sometimes|integer|exists:organization_ems_training_programs,organization_ems_training_program_id',
                'organization_ems_recruitment_agency_id' => 'sometimes|nullable|integer|exists:organization_ems_recruitment_agencies,organization_ems_recruitment_agency_id',
                'organization_ems_company_id' => 'sometimes|nullable|integer|exists:organization_ems_companies,organization_ems_company_id',
                'referral_date' => 'sometimes|date',
                'referral_status' => 'sometimes|in:Referred,In Process,Interview Scheduled,Offer Received,Rejected,Placed',
                'job_role' => 'nullable|string|max:100',
                'package_amount' => 'nullable|numeric|min:0',
                'currency_code' => 'sometimes|string|max:10',
                'joining_date' => 'nullable|date|after_or_equal:referral_date',
                'remarks' => 'nullable|string|max:500',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'errors' => $validator->errors(),
                ], 422);
            }

            $placementReferral->update($request->all());

            return response()->json([
                'success' => true,
                'message' => 'Placement Referral updated successfully.',
                'data' => $placementReferral,
            ]);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Placement Referral not found.',
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update Placement Referral.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function destroy($id)
    {
        try {
            $placementReferral = PlacementReferrals::findOrFail($id);
            $placementReferral->delete();

            return response()->json([
                'success' => true,
                'message' => 'Placement Referral deleted successfully.',
            ]);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Placement Referral not found.',
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete Placement Referral.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
