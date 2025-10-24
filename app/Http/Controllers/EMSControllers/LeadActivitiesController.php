<?php

namespace App\Http\Controllers\EMSControllers;

use App\Http\Controllers\Controller;
use App\Models\EMSModels\LeadActivities;
use App\Models\EMSModels\LeadStage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class LeadActivitiesController extends Controller
{

    public function index(Request $request)
    {

        try {
            $perPage = $request->get('per_page', 10); // Keep as string for "all" comparison
            $page = (int) $request->get('page', 1);
            $search = $request->get('search');
            $organizationId = $request->get('organization_id');

            $query = LeadActivities::with('lead', 'employee');

            if (!empty($organizationId)) {
                $query->where('organization_id', $organizationId);
            }

            // Search filter
            if (!empty($search)) {
                $query->where(function ($q) use ($search) {
                    $q->where('activity_type', 'like', '%' . $search . '%');
                });
            }

            // Handle pagination or return all
            $leadActivity = ($perPage === 'all')
                ? $query->get()
                : $query->paginate((int) $perPage, ['*'], 'page', $page);

            return response()->json([
                'message' => 'Lead Activity fetched successfully',
                'data' => $leadActivity
            ]);
        } catch (\Exception $e) {


            return response()->json(['message' => 'Failed to fetch Lead Activity'], 500);
        }

    }


    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'organization_id' => 'required|integer|exists:organizations,organization_id',
            'organization_entity_id' => 'required|integer|exists:organization_entities,organization_entity_id',
            'organization_ems_lead_id' => 'required|integer|exists:organization_ems_leads,organization_ems_lead_id',
            'employee_id' => 'required|integer|exists:employees,employee_id',
            'activity_type' => 'required|in:Call,Email,WhatsApp,SMS,Telegram,Meeting,Other',
            'activity_datetime' => 'required|date_format:Y-m-d H:i:s',
            'activity_summary' => 'nullable|string|max:255',
            'remarks' => 'nullable|string',
            'was_within_preferred_time' => 'required|boolean',
            'call_status' => 'nullable|in:Attended,Missed,Rejected,No Answer',
            'email_read_flag' => 'nullable|boolean',
            'email_response_flag' => 'nullable|boolean',
            'whatsapp_read_flag' => 'nullable|boolean',
            'whatsapp_response_flag' => 'nullable|boolean',
        ], [
            'organization_id.required' => 'Organization is required.',
            'organization_entity_id.required' => 'Organization Entity is required.',
            'organization_ems_lead_id.required' => 'Lead is required.',
            'employee_id.required' => 'Employee (Counsellor) is required.',
            'activity_type.required' => 'Activity type is required.',
            'activity_datetime.required' => 'Activity date & time is required.',
        ]);


        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        try {

            $leadActivity = LeadActivities::create($request->all());

            return response()->json([
                'success' => true,
                'message' => 'Lead Activity created successfully.',
                'data' => $leadActivity,
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to create Lead Activity.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }



    public function show($id)
    {
        try {
            $leadActivity = LeadActivities::with('lead', 'employee')->findOrFail($id);

            return response()->json([
                'success' => true,
                'data' => $leadActivity,
            ]);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Lead Activity not found.',
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error fetching Lead Activity .',
                'error' => $e->getMessage(),
            ], 500);
        }
    }


    public function update(Request $request, $id)
    {
        try {
            $leadActivity = LeadActivities::find($id);

            if (!$leadActivity) {
                return response()->json([
                    'success' => false,
                    'message' => 'Lead Stage not found.',
                ], 404);
            }

            $validator = Validator::make($request->all(), [
                'organization_id' => 'sometimes|integer|exists:organizations,organization_id',
                'organization_entity_id' => 'sometimes|integer|exists:organization_entities,organization_entity_id',
                'organization_ems_lead_id' => 'sometimes|integer|exists:organization_ems_leads,organization_ems_lead_id',
                'employee_id' => 'sometimes|integer|exists:employees,employee_id',
                'activity_type' => 'sometimes|in:Call,Email,WhatsApp,SMS,Telegram,Meeting,Other',
                'activity_datetime' => 'sometimes|date_format:Y-m-d H:i:s',
                'activity_summary' => 'nullable|string|max:255',
                'remarks' => 'nullable|string',
                'was_within_preferred_time' => 'sometimes|boolean',

                'call_status' => 'nullable|in:Attended,Missed,Rejected,No Answer',
                'email_read_flag' => 'nullable|boolean',
                'email_response_flag' => 'nullable|boolean',
                'whatsapp_read_flag' => 'nullable|boolean',
                'whatsapp_response_flag' => 'nullable|boolean',
            ]);


            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'errors' => $validator->errors(),
                ], 422);
            }

            $leadActivity->update($request->all());

            return response()->json([
                'success' => true,
                'message' => 'Lead Activity updated successfully.',
                'data' => $leadActivity,
            ]);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Lead Activity not found.',
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update Lead Activity.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }


    public function destroy($id)
    {
        try {
            $leadActivity = LeadActivities::findOrFail($id);
            $leadActivity->delete();

            return response()->json([
                'success' => true,
                'message' => 'Lead Activity deleted successfully.',
            ]);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Lead Activity not found.',
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete Lead Activity.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

}
