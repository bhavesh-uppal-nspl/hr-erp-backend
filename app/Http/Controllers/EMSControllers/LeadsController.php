<?php

namespace App\Http\Controllers\EMSControllers;

use App\Http\Controllers\Controller;
use App\Models\EMSModels\Lead;
use App\Models\EMSModels\Student;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class LeadsController extends Controller
{

    public function index(Request $request)
    {

        try {
            $perPage = $request->get('per_page', 10); // Keep as string for "all" comparison
            $page = (int) $request->get('page', 1);
            $search = $request->get('search');
            $organizationId = $request->get('organization_id');

            $query = Lead::with('country', 'state', 'city', 'leadSource', 'leadStage', 'trainingProgram');

            // Filter by organization_id
            if (!empty($organizationId)) {
                $query->where('organization_id', $organizationId);
            }

            // Search filter
            if (!empty($search)) {
                $query->where(function ($q) use ($search) {
                    $q->where('person_full_name', 'like', '%' . $search . '%')
                        ->orWhere('email', 'like', '%' . $search . '%');
                });
            }

            // Handle pagination or return all
            $lead = ($perPage === 'all')
                ? $query->get()
                : $query->paginate((int) $perPage, ['*'], 'page', $page);

            return response()->json(data: [
                'message' => 'Lead fetched successfully',
                'data' => $lead
            ]);
        } catch (\Exception $e) {
            return $e;
            \Log::error('Error fetching Lead: ' . $e->getMessage());
            return response()->json(['message' => 'Failed to fetch Lead'], 500);
        }

    }


    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'organization_id' => 'required|integer|exists:organizations,organization_id',
            'organization_entity_id' => 'required|integer|exists:organization_entities,organization_entity_id',
            'lead_source_id' => 'sometimes|nullable|integer|exists:organization_ems_lead_sources,organization_ems_lead_source_id',
            'country_id' => 'sometimes|nullable|integer|exists:general_countries,general_country_id',
            'state_id' => 'sometimes|nullable|integer|exists:general_states,general_state_id',
            'city_id' => 'sometimes|nullable|integer|exists:general_cities,general_city_id',
            'training_program_id' => 'sometimes|nullable|integer|exists:organization_ems_training_programs,organization_ems_training_program_id',
            'lead_stage_id' => 'sometimes|nullable|integer|exists:organization_ems_lead_stages,organization_ems_lead_stage_id',

            'lead_datetime' => 'required|date_format:Y-m-d H:i:s',
            'person_full_name' => 'required|string|max:150',
            'email' => 'nullable|email|max:100',
            'phone' => 'nullable|string|max:20',
            'alternate_phone' => 'nullable|string|max:20',
            'interested_program_remarks' => 'nullable|string|max:150',
            'remarks' => 'nullable|string',
            'is_spam' => 'boolean',
            'spam_reason' => 'nullable|string|max:255',
        ], [
            'organization_id.required' => 'Organization ID is required.',
            'organization_entity_id.required' => 'Organization entity is required.',
            'lead_datetime.required' => 'Lead datetime is required.',
            'person_full_name.required' => 'Full name of the lead is required.',
        ]);


        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            $lead = Lead::create($request->all());

            return response()->json([
                'success' => true,
                'message' => 'Lead created successfully.',
                'data' => $lead,
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to create lead.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }


    public function show($id)
    {
        try {
            $lead = Lead::with('country', 'state', 'city', 'leadSource', 'leadStage', 'trainingProgram')->findOrFail($id);

            return response()->json([
                'success' => true,
                'data' => $lead,
            ]);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'lead not found.',
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error fetching lead.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }


    public function update(Request $request, $id)
    {
        try {
            $lead = Lead::find($id);

            if (!$lead) {
                return response()->json([
                    'success' => false,
                    'message' => 'Lead not found.',
                ], 404);
            }

            $validator = Validator::make($request->all(), [
                'organization_id' => 'required|integer|exists:organizations,organization_id',
                'organization_entity_id' => 'required|integer|exists:organization_entities,organization_entity_id',

                'lead_datetime' => 'required|date_format:Y-m-d H:i:s',

                'lead_source_id' => 'sometimes|nullable|integer|exists:organization_ems_lead_sources,organization_ems_lead_source_id',
                'person_full_name' => 'required|string|max:150',

                'email' => [
                    'nullable',
                    'email',
                    'max:100',
                    Rule::unique('organization_ems_leads')->ignore($lead->organization_ems_lead_id, 'organization_ems_lead_id')
                        ->where(function ($query) use ($request) {
                            return $query->where('organization_id', $request->organization_id);
                        }),
                ],
                'phone' => [
                    'nullable',
                    'string',
                    'max:20',
                    Rule::unique('organization_ems_leads')->ignore($lead->organization_ems_lead_id, 'organization_ems_lead_id')
                        ->where(function ($query) use ($request) {
                            return $query->where('organization_id', $request->organization_id);
                        }),
                ],
                'alternate_phone' => 'nullable|string|max:20',

                'country_id' => 'sometimes|nullable|integer|exists:general_countries,general_country_id',
                'state_id' => 'sometimes|nullable|integer|exists:general_states,general_state_id',
                'city_id' => 'sometimes|nullable|integer|exists:general_cities,general_city_id',

                'training_program_id' => 'sometimes|nullable|integer|exists:organization_ems_training_programs,organization_ems_training_program_id',
                'interested_program_remarks' => 'nullable|string|max:150',

                'remarks' => 'nullable|string',
                'lead_stage_id' => 'sometimes|nullable|integer|exists:organization_ems_lead_stages,organization_ems_lead_stage_id',

                'is_spam' => 'boolean',
                'spam_reason' => 'nullable|string|max:255',
            ], [
                'organization_id.required' => 'Organization ID is required.',
                'organization_entity_id.required' => 'Organization entity is required.',
                'lead_datetime.required' => 'Lead datetime is required.',
                'person_full_name.required' => 'Full name of the lead is required.',
                'email.unique' => 'This email already exists for the selected organization.',
                'phone.unique' => 'This phone already exists for the selected organization.',
            ]);


            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'errors' => $validator->errors(),
                ], 422);
            }

            $lead->update($request->all());

            return response()->json([
                'success' => true,
                'message' => 'Lead updated successfully.',
                'data' => $lead,
            ]);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Lead not found.',
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update Student Fee.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }


    public function destroy($id)
    {
        try {
            $lead = Lead::findOrFail($id);
            $lead->delete();

            return response()->json([
                'success' => true,
                'message' => 'Lead deleted successfully.',
            ]);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Lead not found.',
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete Lead.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

}
