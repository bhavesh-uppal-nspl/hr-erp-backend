<?php

namespace App\Http\Controllers\OrganizationController;

use App\Models\OrganizationModel\OrganizationHoliday;
use Illuminate\Support\Facades\Validator;
use App\Http\Controllers\Controller;
use App\Models\OrganizationModel\Organization;
use App\Models\OrganizationModel\OrganizationHolidayCalendar;
use Exception;
use Illuminate\Http\Request;
use Auth;
use Illuminate\Validation\Rule;

class OrganizationHolidayCalendarController extends Controller
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
                return response()->json(['errors' => $validator->errors()], 422);
            }


            if ($request->input('mode') == 1) {
                $holiday = OrganizationHolidayCalendar::where('organization_id', $org_id)->get();

                if ($holiday->isEmpty()) {
                    return response()->json([
                        'message' => 'Holiday not found.'
                    ], 404);
                }
                $mappedHoliday = $holiday->map(function ($dep) {
                    return [
                        'holiday_calendar_name'=>$dep->holiday_calendar_name,
                        'holiday_calendar_year_start_date' => $dep->holiday_calendar_year_start_date ?? '',
                        'holiday_calendar_year_end_date' => $dep->holiday_calendar_year_end_date ?? '',
                        
                    ];
                });
                return response()->json($mappedHoliday);
            }

            $query = OrganizationHolidayCalendar::where('organization_id', $org_id);
            $per = $request->input('per_page', 999);
            $search = $request->input('search');
            if ($search) {
                $query->where(function ($q) use ($search) {
                    $q->where('holiday_calendar_name', 'like', "%{$search}%");

                });
            }
            $data = $query->orderBy('created_at', 'desc')->paginate($per);
            return response()->json([
                'message' => 'OK',
                'Holidaycalenders' => $data,

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
                'organization_id' => 'required|integer|exists:organizations,organization_id',
                'holiday_calendar_name' => [
                    'required',
                    'string',
                    'max:255',
                    Rule::unique('organization_holiday_calendars', 'holiday_calendar_name')
                        ->where('organization_id', $org_id),
                ],
                'holiday_calendar_year_start_date' => ['required', 'date'],
                'holiday_calendar_year_end_date' => ['required', 'date', 'after_or_equal:holiday_calendar_year_start_date'],

            ]);

            // Return validation errors if any
            if ($validator->fails()) {
                return response()->json([
                    'error' => 'Validation failed',
                    'message' => $validator->errors()
                ], 422);
            }
            $data = $request->all();
            $holidaycalendar = OrganizationHolidayCalendar::create(array_merge($data));
            return response()->json([
                'message' => 'Organization Holiday Calender Added SuccessFully.',
                'holidaycalendar' => $holidaycalendar
            ], 201);




        } catch (\Exception $e) {
            // Handle any other errors and return a response
            return response()->json([
                'error' => 'Something went wrong. Please try again later.',
                'details' => $e->getMessage()
            ], 500);
        }
    }


    public function show(Request $request, $org_id, $calendar_id)
    {
        try {
            $user = Auth::guard('applicationusers')->user();
            $organizationIds = $user->Client->Organization->pluck('organization_id')->toArray();
            if (!in_array($org_id, $organizationIds)) {
                return response()->json([
                    'messages' => 'unauthorized'
                ], 401);

            }
            $request->merge(['organization_id' => $org_id, 'organization_holiday_calendar_id' => $calendar_id]);
            $validator = Validator::make($request->all(), [
                'organization_id' => 'required|integer|exists:organizations,organization_id',
                'organization_holiday_calendar_id' => 'required|integer|exists:organization_holiday_calendars,organization_holiday_calendar_id',

            ]);
            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()], 422);
            }
            $holidaycalendar = OrganizationHolidayCalendar::find($calendar_id);
            return response()->json([
                'message' => 'Organization Holiday Calendar Found',
                'holidaycalendar' => $holidaycalendar
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Something went wrong. Please try again later.',
                'details' => $e->getMessage()
            ], 500);
        }
    }


    // update the orgaization 
    public function update(Request $request, $org_id, $calendar_id)
    {
        try {
            $user = Auth::guard('applicationusers')->user();
            $organizationIds = $user->Client->Organization->pluck('organization_id')->toArray();
            if (!in_array($org_id, $organizationIds)) {
                return response()->json([
                    'messages' => 'unauthorized'
                ], 401);

            }
            $request->merge(['organization_id' => $org_id, 'organization_holiday_calendar_id' => $calendar_id]);
            $rules = [
                'organization_id' => 'required|integer|exists:organizations,organization_id',
                'organization_holiday_calendar_id' => 'required|integer|exists:organization_holiday_calendars,organization_holiday_calendar_id',
                'holiday_calendar_name' => [
                    'sometimes',
                    'string',
                    'max:255',
                    Rule::unique('organization_holiday_calendars', 'holiday_calendar_name')
                        ->ignore($calendar_id, 'organization_holiday_calendar_id')
                        ->where('organization_id', $org_id),
                ],
                'holiday_calendar_year_start_date' => ['sometimes', 'date'],
                'holiday_calendar_year_end_date' => ['sometimes', 'date', 'after_or_equal:holiday_calendar_year_start_date'],
            ];
            $validator = Validator::make($request->all(), $rules);
            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()], 422);
            }
            $holidaycalendar = OrganizationHolidayCalendar::find($calendar_id);
            $holidaycalendar->update($request->only([
                'holiday_calendar_name',
                'holiday_calendar_year_start_date',
                'holiday_calendar_year_end_date',
            ]));
            return response()->json([
                'message' => 'Organization holiday calendar updated successfully.',
                'holidaycalendar' => $holidaycalendar
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Something went wrong. Please try again later.',
                'details' => $e->getMessage()
            ], 500);
        }
    }


    // delete the orgaization  
    public function destroy(Request $request, $org_id, $calendar_id)
    {
        try {
            $user = Auth::guard('applicationusers')->user();
            $organizationIds = $user->Client->Organization->pluck('organization_id')->toArray();
            if (!in_array($org_id, $organizationIds)) {
                return response()->json([
                    'messages' => 'unauthorized'
                ], 401);

            }
            $request->merge(['organization_id' => $org_id, 'organization_holiday_calendar_id' => $calendar_id]);
            $validator = Validator::make($request->all(), [
                'organization_id' => 'required|integer|exists:organizations,organization_id',
                'organization_holiday_calendar_id' => 'required|integer|exists:organization_holiday_calendars,organization_holiday_calendar_id',
            ]);
            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()], 422);
            }

            $holidays = OrganizationHoliday::where('organization_holiday_calendar_id', $calendar_id);
            $holidays->delete();
            $holidaycalendar = OrganizationHolidayCalendar::find($calendar_id);
            $holidaycalendar->delete();
            return response()->json([
                'message' => 'Organization holiday calendar Deleted Successfully'
            ], 200); // or just remove 200 — it's the default

        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Something went wrong. Please try again later.',
                'details' => $e->getMessage()
            ], 500);
        }
    }
}
