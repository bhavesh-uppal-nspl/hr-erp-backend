<?php

namespace App\Http\Controllers\EMSControllers;

use App\Http\Controllers\Controller;
use App\Models\EMSModels\LeadSource;
use App\Models\EMSModels\Student;
use App\Models\EMSModels\StudentFees;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class LeadSourcesController extends Controller
{

    public function index(Request $request)
    {

        try {
            $perPage = $request->get('per_page', 10); // Keep as string for "all" comparison
            $page = (int) $request->get('page', 1);
            $search = $request->get('search');
            $organizationId = $request->get('organization_id');

            $query = LeadSource::query();

            if (!empty($organizationId)) {
                $query->where('organization_id', $organizationId);
            }

            // Search filter
            if (!empty($search)) {
                $query->where(function ($q) use ($search) {
                    $q->where('lead_source_name', 'like', '%' . $search . '%')
                        ->orWhere('lead_source_short_name', 'like', '%' . $search . '%');
                });
            }

            // Handle pagination or return all
            $leadSource = ($perPage === 'all')
                ? $query->get()
                : $query->paginate((int) $perPage, ['*'], 'page', $page);

            return response()->json([
                'message' => 'Lead Source fetched successfully',
                'data' => $leadSource
            ]);
        } catch (\Exception $e) {
            \Log::error('Error fetching Lead Source: ' . $e->getMessage());
            return response()->json(['message' => 'Failed to fetch Lead Source'], 500);
        }

    }


    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'organization_id' => 'required|integer|exists:organizations,organization_id',
            'organization_entity_id' => 'required|integer|exists:organization_entities,organization_entity_id',
            'lead_source_name' => [
                'required',
                'string',
                'max:100',
                Rule::unique('organization_ems_lead_sources')
                    ->where(function ($query) use ($request) {
                        return $query->where('organization_id', $request->organization_id);
                    })
            ],
            'lead_source_short_name' => 'nullable|string|max:10',
            'description' => 'nullable|string|max:255',
        ], [
            'lead_source_name.required' => 'Lead source name is required.',
            'lead_source_name.unique' => 'This lead source already exists for the selected organization.',
        ]);


        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }


        try {
            $leadSource = LeadSource::create($request->all());

            return response()->json([
                'success' => true,
                'message' => 'Lead Source created successfully.',
                'data' => $leadSource,
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to create student.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }


    public function show($id)
    {
        try {
            $leadSource = LeadSource::findOrFail($id);

            return response()->json([
                'success' => true,
                'data' => $leadSource,
            ]);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Lead Source not found.',
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error fetching Lead Source .',
                'error' => $e->getMessage(),
            ], 500);
        }
    }


    public function update(Request $request, $id)
    {
        try {
            $leadSource = LeadSource::find($id);

            if (!$leadSource) {
                return response()->json([
                    'success' => false,
                    'message' => 'Lead Source not found.',
                ], 404);
            }

            $validator = Validator::make($request->all(), [
                'organization_id' => 'required|integer|exists:organizations,organization_id',
                'organization_entity_id' => 'required|integer|exists:organization_entities,organization_entity_id',
                'lead_source_name' => [
                    'required',
                    'string',
                    'max:100',
                    Rule::unique('organization_ems_lead_sources')
                        ->where(function ($query) use ($request) {
                            return $query->where('organization_id', $request->organization_id);
                        })
                        ->ignore($leadSource->organization_ems_lead_source_id, 'organization_ems_lead_source_id')
                ],
                'lead_source_short_name' => 'nullable|string|max:10',
                'description' => 'nullable|string|max:255',
            ], [
                'lead_source_name.required' => 'Lead source name is required.',
                'lead_source_name.unique' => 'This lead source already exists for the selected organization.',
            ]);


            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'errors' => $validator->errors(),
                ], 422);
            }

            $leadSource->update($request->all());

            return response()->json([
                'success' => true,
                'message' => 'Lead Source updated successfully.',
                'data' => $leadSource,
            ]);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Lead Source not found.',
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update Lead Source.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }


    public function destroy($id)
    {
        try {
            $leadSource = LeadSource::findOrFail($id);
            $leadSource->delete();

            return response()->json([
                'success' => true,
                'message' => 'Lead Source deleted successfully.',
            ]);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Lead Source not found.',
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete Lead Source.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

}
