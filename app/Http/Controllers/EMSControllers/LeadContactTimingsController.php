<?php

namespace App\Http\Controllers\EMSControllers;

use App\Http\Controllers\Controller;
use App\Models\EMSModels\LeadActivities;
use App\Models\EMSModels\LeadContactTimings;
use App\Models\EMSModels\LeadStage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class LeadContactTimingsController extends Controller
{

    public function index(Request $request)
    {

        try {
            $perPage = $request->get('per_page', 10); // Keep as string for "all" comparison
            $page = (int) $request->get('page', 1);
            $search = $request->get('search');
            $organizationId = $request->get('organization_id');

            $query = LeadContactTimings::with('lead');

            if (!empty($organizationId)) {
                $query->where('organization_id', $organizationId);
            }

            // Search filter
            if (!empty($search)) {
                $query->where(function ($q) use ($search) {
                    $q->where('preferred_contact_mode', 'like', '%' . $search . '%');
                });
            }

            // Handle pagination or return all
            $leadContactTiming = ($perPage === 'all')
                ? $query->get()
                : $query->paginate((int) $perPage, ['*'], 'page', $page);

            return response()->json([
                'message' => 'Lead Contact Timing fetched successfully',
                'data' => $leadContactTiming
            ]);
        } catch (\Exception $e) {
            \Log::error('Error fetching Lead Contact Timing: ' . $e->getMessage());
            return response()->json(['message' => 'Failed to fetch Lead Contact Timing'], 500);
        }
    }


    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'organization_id' => 'required|integer|exists:organizations,organization_id',
            'organization_entity_id' => 'required|integer|exists:organization_entities,organization_entity_id',
            'organization_ems_lead_id' => 'required|integer|exists:organization_ems_leads,organization_ems_lead_id',

            'preferred_contact_mode' => 'nullable|in:Call,WhatsApp,Email,SMS,Telegram,Other',
            'preferred_contact_timezone' => [
                'nullable',
                'string',
                'max:100',
                function ($attribute, $value, $fail) {
                    if (!in_array($value, \DateTimeZone::listIdentifiers())) {
                        $fail("The {$attribute} must be a valid timezone identifier.");
                    }
                }
            ],

            'morning_student_time_start' => 'nullable|date_format:H:i',
            'morning_student_time_end' => 'nullable|date_format:H:i|after:morning_student_time_start',
            'morning_ist_time_start' => 'required|date_format:H:i',
            'morning_ist_time_end' => 'required|date_format:H:i|after:morning_ist_time_start',
            'evening_student_time_start' => 'nullable|date_format:H:i',
            'evening_student_time_end' => 'nullable|date_format:H:i|after:evening_student_time_start',
            'evening_ist_time_start' => 'required|date_format:H:i',
            'evening_ist_time_end' => 'required|date_format:H:i',
        ], [
            // Custom messages
            'preferred_contact_mode.in' => 'Preferred contact mode must be one of Call, WhatsApp, Email, SMS, Telegram, or Other.',
            'preferred_contact_timezone.string' => 'Preferred contact timezone must be a valid string.',
            'morning_student_time_end.after' => 'Morning student end time must be after start time.',
            'morning_ist_time_end.after' => 'Morning IST end time must be after start time.',
            'evening_student_time_end.after' => 'Evening student end time must be after start time.',
            'evening_ist_time_end.after' => 'Evening IST end time must be after start time.',
        ]);


        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        try {

            $leadContactTimings = LeadContactTimings::create($request->all());

            return response()->json([
                'success' => true,
                'message' => 'Lead Contact Timing created successfully.',
                'data' => $leadContactTimings,
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to create Lead Contact Timing.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }



    public function show($id)
    {
        try {
            $leadContactTiming = LeadContactTimings::with('lead')->findOrFail($id);

            return response()->json([
                'success' => true,
                'data' => $leadContactTiming,
            ]);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Lead Contact Timing not found.',
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error fetching Lead Contact Timing .',
                'error' => $e->getMessage(),
            ], 500);
        }
    }


    public function update(Request $request, $id)
    {
        try {
            $leadContactTiming = LeadContactTimings::find($id);

            if (!$leadContactTiming) {
                return response()->json([
                    'success' => false,
                    'message' => 'Lead Stage not found.',
                ], 404);
            }

            $validator = Validator::make($request->all(), [
                'organization_id' => 'sometimes|integer|exists:organizations,organization_id',
                'organization_entity_id' => 'sometimes|integer|exists:organization_entities,organization_entity_id',
                'organization_ems_lead_id' => 'sometimes|integer|exists:organization_ems_leads,organization_ems_lead_id',

                'preferred_contact_mode' => 'sometimes|in:Call,WhatsApp,Email,SMS,Telegram,Other',
                'preferred_contact_timezone' => [
                    'sometimes',
                    'string',
                    'max:100',
                    function ($attribute, $value, $fail) {
                        if ($value && !in_array($value, \DateTimeZone::listIdentifiers())) {
                            $fail("The {$attribute} must be a valid timezone identifier.");
                        }
                    }
                ],

                'morning_student_time_start' => 'sometimes|date_format:H:i:s',
                'morning_student_time_end' => 'sometimes|date_format:H:i:s|after:morning_student_time_start',

                'morning_ist_time_start' => 'sometimes|date_format:H:i:s',
                'morning_ist_time_end' => 'sometimes|date_format:H:i:s|after:morning_ist_time_start',

                'evening_student_time_start' => 'sometimes|date_format:H:i:s',
                'evening_student_time_end' => 'sometimes|date_format:H:i:s|after:evening_student_time_start',

                'evening_ist_time_start' => 'sometimes|date_format:H:i:s',
                'evening_ist_time_end' => 'sometimes|date_format:H:i:s|after:evening_ist_time_start',
            ], [
                'preferred_contact_mode.in' => 'Preferred contact mode must be one of Call, WhatsApp, Email, SMS, Telegram, or Other.',
                'preferred_contact_timezone.string' => 'Preferred contact timezone must be a valid string.',
                'morning_student_time_end.after' => 'Morning student end time must be after start time.',
                'morning_ist_time_end.after' => 'Morning IST end time must be after start time.',
                'evening_student_time_end.after' => 'Evening student end time must be after start time.',
                'evening_ist_time_end.after' => 'Evening IST end time must be after start time.',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'errors' => $validator->errors(),
                ], 422);
            }

            $leadContactTiming->update($request->all());

            return response()->json([
                'success' => true,
                'message' => 'Lead Contact Timing updated successfully.',
                'data' => $leadContactTiming,
            ]);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Lead Contact Timing not found.',
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update Lead Contact Timing.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }


    public function destroy($id)
    {
        try {
            $leadContactTimings = LeadContactTimings::findOrFail($id);
            $leadContactTimings->delete();

            return response()->json([
                'success' => true,
                'message' => 'Lead Contact Timing deleted successfully.',
            ]);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Lead Contact Timing not found.',
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete Lead Contact Timing.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

}
