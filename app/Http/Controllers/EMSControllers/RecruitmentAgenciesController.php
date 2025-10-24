<?php

namespace App\Http\Controllers\EMSControllers;

use App\Http\Controllers\Controller;
use App\Models\EMSModels\RecruitmentAgency;
use App\Models\EMSModels\TrainingProgram;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class RecruitmentAgenciesController extends Controller
{

    public function index(Request $request)
    {

        try {
            $perPage = $request->get('per_page', 10); // Keep as string for "all" comparison
            $page = (int) $request->get('page', 1);
            $search = $request->get('search');
            $organizationId = $request->get('organization_id');

            $query = RecruitmentAgency::with('country', 'state', 'city');


            // Filter by organization_id
            if (!empty($organizationId)) {
                $query->where('organization_id', $organizationId);
            }

            // Search filter
            if (!empty($search)) {
                $query->where(function ($q) use ($search) {
                    $q->where('agency_name', 'like', '%' . $search . '%');
                });
            }

            // Handle pagination or return all
            $recruitmentAgency = ($perPage === 'all')
                ? $query->get()
                : $query->paginate((int) $perPage, ['*'], 'page', $page);

            return response()->json([
                'message' => 'Recruitment Agency fetched successfully',
                'data' => $recruitmentAgency
            ]);
        } catch (\Exception $e) {
            \Log::error('Error fetching Recruitment Agency: ' . $e->getMessage());
            return response()->json(['message' => 'Failed to fetch Recruitment Agency'], 500);
        }

    }


    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'organization_id' => 'required|integer|exists:organizations,organization_id',
            'organization_entity_id' => 'sometimes|nullable|integer|exists:organization_entities,organization_entity_id',
            'agency_name' => [
                'required',
                'string',
                'max:150',
                Rule::unique('organization_ems_recruitment_agencies')
                    ->where(fn($query) => $query->where('organization_id', $request->organization_id))
            ],
            'contact_person' => 'sometimes|nullable|string|max:100',
            'email' => 'sometimes|nullable|email|max:100',
            'phone' => 'sometimes|nullable|string|max:20',
            'address' => 'sometimes|nullable|string|max:255',
            'country_id' => 'sometimes|nullable|integer|exists:general_countries,general_country_id',
            'state_id' => 'sometimes|nullable|integer|exists:general_states,general_state_id',
            'city_id' => 'sometimes|nullable|integer|exists:general_cities,general_city_id',
            'status' => 'required|in:Active,Inactive',
            'remarks' => 'sometimes|nullable|string|max:1000',
        ]);


        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            $recruitmentAgency = RecruitmentAgency::create($request->all());

            return response()->json([
                'success' => true,
                'message' => 'Recruitment Agency created successfully.',
                'data' => $recruitmentAgency,
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to create Recruitment Agency.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function show($id)
    {
        try {
            $recruitmentAgency = RecruitmentAgency::with('country', 'state', 'city')->findOrFail($id);

            return response()->json([
                'success' => true,
                'data' => $recruitmentAgency,
            ]);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Recruitment Agency not found.',
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error fetching Recruitment Agency.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }


    public function update(Request $request, $id)
    {
        try {
            $recruitmentAgency = RecruitmentAgency::findOrFail($id);

            $validator = Validator::make($request->all(), [
                'organization_id' => 'sometimes|integer|exists:organizations,organization_id',
                'organization_entity_id' => 'sometimes|nullable|integer|exists:organization_entities,organization_entity_id',
                'agency_name' => [
                    'sometimes',
                    'string',
                    'max:150',
                    Rule::unique('organization_ems_recruitment_agencies')
                        ->where(fn($query) => $query->where('organization_id', $request->organization_id))
                        ->ignore($recruitmentAgency->organization_ems_recruitment_agency_id, 'organization_ems_recruitment_agency_id')
                ],
                'contact_person' => 'sometimes|nullable|string|max:100',
                'email' => 'sometimes|nullable|email|max:100',
                'phone' => 'sometimes|nullable|string|max:20',
                'address' => 'sometimes|nullable|string|max:255',
                'country_id' => 'sometimes|nullable|integer|exists:general_countries,general_country_id',
                'state_id' => 'sometimes|nullable|integer|exists:general_states,general_state_id',
                'city_id' => 'sometimes|nullable|integer|exists:general_cities,general_city_id',
                'status' => 'sometimes|in:Active,Inactive',
                'remarks' => 'sometimes|nullable|string|max:1000',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'errors' => $validator->errors(),
                ], 422);
            }

            $recruitmentAgency->update($request->all());

            return response()->json([
                'success' => true,
                'message' => 'Recruitment Agency updated successfully.',
                'data' => $recruitmentAgency,
            ]);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Recruitment Agency not found.',
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update Recruitment Agency.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function destroy($id)
    {
        try {
            $recruitmentAgency = RecruitmentAgency::findOrFail($id);
            $recruitmentAgency->delete();

            return response()->json([
                'success' => true,
                'message' => 'Recruitment Agency deleted successfully.',
            ]);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Recruitment Agency not found.',
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete Recruitment Agency.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
