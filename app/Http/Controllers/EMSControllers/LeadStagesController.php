<?php

namespace App\Http\Controllers\EMSControllers;

use App\Http\Controllers\Controller;
use App\Models\EMSModels\LeadSource;
use App\Models\EMSModels\LeadStage;
use App\Models\EMSModels\Student;
use App\Models\EMSModels\StudentFees;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class LeadStagesController extends Controller
{

    public function index(Request $request)
    {

        try {
            $perPage = $request->get('per_page', 10); // Keep as string for "all" comparison
            $page = (int) $request->get('page', 1);
            $search = $request->get('search');
            $organizationId = $request->get('organization_id');

            $query = LeadStage::query();

            if (!empty($organizationId)) {
                $query->where('organization_id', $organizationId);
            }

            // Search filter
            if (!empty($search)) {
                $query->where(function ($q) use ($search) {
                    $q->where('lead_stage_name', 'like', '%' . $search . '%')
                        ->orWhere('lead_stage_short_name', 'like', '%' . $search . '%');
                });
            }

            // Handle pagination or return all
            $leadStage = ($perPage === 'all')
                ? $query->get()
                : $query->paginate((int) $perPage, ['*'], 'page', $page);

            return response()->json([
                'message' => 'Lead Stage fetched successfully',
                'data' => $leadStage
            ]);
        } catch (\Exception $e) {
            \Log::error('Error fetching Lead Stage: ' . $e->getMessage());
            return response()->json(['message' => 'Failed to fetch Lead Stage'], 500);
        }

    }


    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'organization_id' => 'required|integer|exists:organizations,organization_id',
            'organization_entity_id' => 'required|integer|exists:organization_entities,organization_entity_id',
            'lead_stage_name' => [
                'required',
                'string',
                'max:100',
                Rule::unique('organization_ems_lead_stages')
                    ->where(function ($query) use ($request) {
                        return $query->where('organization_id', $request->organization_id);
                    })
            ],
            'lead_stage_short_name' => 'nullable|string|max:10',
            'description' => 'nullable|string|max:255',
        ], [
            'lead_stage_name.required' => 'Lead stage name is required.',
            'lead_stage_name.unique' => 'This lead stage already exists for the selected organization.',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            // Calculate next sequence number for this organization
            $nextSequence = LeadStage::where('organization_id', $request->organization_id)
                ->max('lead_stage_sequence_number');

            $request->merge([
                'lead_stage_sequence_number' => $nextSequence ? $nextSequence + 1 : 1
            ]);

            $leadStage = LeadStage::create($request->all());

            return response()->json([
                'success' => true,
                'message' => 'Lead Stage created successfully.',
                'data' => $leadStage,
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to create Lead Stage.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }



    public function show($id)
    {
        try {
            $leadStage = LeadStage::findOrFail($id);

            return response()->json([
                'success' => true,
                'data' => $leadStage,
            ]);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Lead Stage not found.',
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error fetching Lead Stage .',
                'error' => $e->getMessage(),
            ], 500);
        }
    }


    public function update(Request $request, $id)
    {
        try {
            $leadStage = LeadStage::find($id);

            if (!$leadStage) {
                return response()->json([
                    'success' => false,
                    'message' => 'Lead Stage not found.',
                ], 404);
            }

            $validator = Validator::make($request->all(), [
                'organization_id' => 'required|integer|exists:organizations,organization_id',
                'organization_entity_id' => 'required|integer|exists:organization_entities,organization_entity_id',
                'lead_stage_name' => [
                    'required',
                    'string',
                    'max:100',
                    Rule::unique('organization_ems_lead_stages')
                        ->where(function ($query) use ($request) {
                            return $query->where('organization_id', $request->organization_id);
                        })
                        ->ignore($leadStage->organization_ems_lead_stage_id, 'organization_ems_lead_stage_id')
                ],
                'lead_stage_short_name' => 'nullable|string|max:10',
                'description' => 'nullable|string|max:255',
            ], [
                'lead_stage_name.required' => 'Lead stage name is required.',
                'lead_stage_name.unique' => 'This lead stage already exists for the selected organization.',
            ]);


            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'errors' => $validator->errors(),
                ], 422);
            }

            $request->merge([
                'lead_stage_sequence_number' => $leadStage->lead_stage_sequence_number
            ]);

            $leadStage->update($request->all());

            return response()->json([
                'success' => true,
                'message' => 'Lead Stage updated successfully.',
                'data' => $leadStage,
            ]);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Lead Stage not found.',
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update Lead Stage.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }


    public function destroy($id)
    {
        try {
            $leadStage = LeadStage::findOrFail($id);
            $leadStage->delete();

            return response()->json([
                'success' => true,
                'message' => 'Lead Stage deleted successfully.',
            ]);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Lead Stage not found.',
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete Lead Stage.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

}
