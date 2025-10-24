<?php

namespace App\Http\Controllers\OrganizationController;

use Carbon\Carbon;
use Illuminate\Support\Facades\Validator;
use App\Http\Controllers\Controller;
use App\Models\OrganizationModel\Organization;
use App\Models\OrganizationModel\OrganizationHoliday;
use Exception;
use Illuminate\Http\Request;    use Auth;

class OrganizationHolidayController extends Controller
{


    public function index(Request $request, $org_id)
  {
        try {    $user = Auth::guard('applicationusers')->user();
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


            
            $query = OrganizationHoliday::with('holidayType')->where('organization_id', $org_id);
            $per = $request->input('per_page', 10);
            $search = $request->input('search');
            if ($search) {
                $query->where(function ($q) use ($search) {
                    $q->where('holiday_type_name', 'like', "%{$search}%");
                });
            }
            $data = $query->orderBy('created_at', 'desc')->paginate($per);
            return response()->json([
                'message' => 'OK',
                'holidays' => $data,

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
        try {    $user = Auth::guard('applicationusers')->user();
             $organizationIds = $user->Client->Organization->pluck('organization_id')->toArray();
            if (!in_array($org_id, $organizationIds)) {
                return response()->json([
                    'messages' => 'unauthorized'
                ], 401);

            }
            $request->merge(['organization_id' => $org_id]);
            $validator = Validator::make($request->all(), [
                'organization_id' => 'required|integer|exists:organizations,organization_id',
                'organization_holiday_calendar_id' => 'required|integer|exists:organization_holiday_calendars,organization_holiday_calendar_id',
                'organization_holiday_type_id' => 'required|integer|exists:organization_holiday_types,organization_holiday_type_id',
                'organization_entity_id ' => 'required|integer|exists:organization_entities,organization_entity_id',
                'holiday_date' => ['required', 'date'],
                'holiday_name' => ['required', 'string', 'max:100'],
                'description' => ['nullable', 'string', 'max:255'],
                'is_recurring' => ['nullable', 'boolean'],
            ]);
            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()], 422);
            }
            $data = $request->all();
            $holidays = OrganizationHoliday::create($data);
            return response()->json([
                'message' => 'Organization Holidays Added SuccessFully.',
                'holidays' => $holidays
            ], 201);

        } catch (\Exception $e) {
            // Handle any other errors and return a response
            return response()->json([
                'error' => 'Something went wrong. Please try again later.',
                'details' => $e->getMessage()
            ], 500);
        }
    }


    public function show(Request $request, $org_id, $holiday_id)
  {
        try {    $user = Auth::guard('applicationusers')->user();
             $organizationIds = $user->Client->Organization->pluck('organization_id')->toArray();
            if (!in_array($org_id, $organizationIds)) {
                return response()->json([
                    'messages' => 'unauthorized'
                ], 401);

            }
            $request->merge(['organization_id' => $org_id, 'organization_holiday_id' => $holiday_id]);
            $validator = Validator::make($request->all(), [
                'organization_id' => 'required|integer|exists:organizations,organization_id',
                'organization_holiday_id' => 'required|integer|exists:organization_holidays,organization_holiday_id',
            ]);
            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()], 422);
            }
            $holidays = OrganizationHoliday::with('holidayCalendar', 'holidayType')->find($holiday_id);
            $holidayData = $holidays->toArray();
            $holidayData['holiday_date'] = Carbon::parse($holidays->holiday_date)->format('Y-m-d');

    
            return response()->json([
                'message' => "Organization Holiday Found",
                'holiday' => $holidayData
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Something went wrong. Please try again later.',
                'details' => $e->getMessage()
            ], 500);
        }
    }

    // update the orgaization 
    public function update(Request $request, $org_id, $holiday_id)
  {
        try {    $user = Auth::guard('applicationusers')->user();
             $organizationIds = $user->Client->Organization->pluck('organization_id')->toArray();
            if (!in_array($org_id, $organizationIds)) {
                return response()->json([
                    'messages' => 'unauthorized'
                ], 401);

            }
            $request->merge(['organization_id' => $org_id, 'organization_holiday_id' => $holiday_id]);
            $rules = [
                'organization_id' => 'required|integer|exists:organizations,organization_id',
                'organization_holiday_calendar_id' => 'required|integer|exists:organization_holiday_calendars,organization_holiday_calendar_id',
                'organization_holiday_type_id' => 'required|integer|exists:organization_holiday_types,organization_holiday_type_id',
                'organization_holiday_id' => 'required|integer|exists:organization_holidays,organization_holiday_id',
                'holiday_date' => ['sometimes', 'date'],
                'holiday_name' => ['sometimes', 'string', 'max:100'],
                'description' => ['sometimes', 'string', 'max:255'],
                'is_recurring' => ['sometimes', 'boolean'],

            ];
            $validator = Validator::make($request->all(), $rules);
            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()], 422);
            }
            $holidays = OrganizationHoliday::find($holiday_id);
            $holidays->update($request->only([
                'holiday_date',
                'holiday_name',
                'description',
                'is_recurring',
                'organization_holiday_type_id',
                'organization_holiday_type_id'

            ]));

            return response()->json([
                'message' => 'Organization holidays updated successfully.',
                'holidays' => $holidays
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Something went wrong. Please try again later.',
                'details' => $e->getMessage()
            ], 500);
        }
    }



    public function destroy(Request $request, $org_id, $holiday_id)
  {
        try {    $user = Auth::guard('applicationusers')->user();
             $organizationIds = $user->Client->Organization->pluck('organization_id')->toArray();
            if (!in_array($org_id, $organizationIds)) {
                return response()->json([
                    'messages' => 'unauthorized'
                ], 401);

            }
            $request->merge(['organization_id' => $org_id, 'organization_holiday_id' => $holiday_id]);
            $validator = Validator::make($request->all(), [
                'organization_id' => 'required|integer|exists:organizations,organization_id',
                'organization_holiday_id' => 'required|integer|exists:organization_holidays,organization_holiday_id',
            ]);
            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()], 422);
            }
            $holidays = OrganizationHoliday::find($holiday_id);
            $holidays->delete();
            return response()->json([
                'message' => 'Organization holiday Deleted Successfully'
            ], 200); // or just remove 200 — it's the default
        } catch (Exception $e) {
            if ($e instanceof \Illuminate\Database\QueryException && $e->errorInfo[1] == 1451) {
                // Foreign key constraint violation
                return response()->json([
                    'error' => 'Cannot delete Business Unit type because it is linked with other records. Please delete dependent records first.'
                ], 409); // 409 Conflict
            }
            return response()->json([
                'error' => 'Failed to delete Address Type type.',
                'exception' => $e->getMessage() // Optional: remove in production
            ], 500);
        }
    }
}
