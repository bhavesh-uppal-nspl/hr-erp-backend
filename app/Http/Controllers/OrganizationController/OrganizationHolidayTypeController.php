<?php

namespace App\Http\Controllers\OrganizationController;

use Illuminate\Support\Facades\Validator;
use App\Http\Controllers\Controller;
use App\Models\OrganizationModel\OrganizationHolidayTypes;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Auth;

class OrganizationHolidayTypeController extends Controller
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
            $query = OrganizationHolidayTypes::where('organization_id', $org_id);
            $per = $request->input('per_page', 10);
            $search = $request->input('search');
            if ($search) {
                $query->where(function ($q) use ($search) {
                    $q->where('holiday_type_name', 'like', "%{$search}%")
                        ->orWhere('description', 'like', "%{$search}%");
                });
            }
            $data = $query->orderBy('created_at', 'desc')->paginate($per);
            return response()->json([
                'message' => 'OK',
                'holidaytypes' => $data,
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
            $organizationIds =
                $user->Client->Organization->pluck('organization_id')->toArray();
            if (!in_array($org_id, $organizationIds)) {
                return response()->json([
                    'messages' => 'unauthorized'
                ], 401);

            }
            $request->merge(['organization_id' => $org_id]);
            $validator = Validator::make($request->all(), [
                'organization_id' =>
                    'required|integer|exists:organizations,organization_id',


                'holiday_type_name' => [
                    'sometimes',
                    'nullable',
                    'string',
                    'max:50',
                    Rule::unique('organization_holiday_types', 'holiday_type_name')
                        ->where(function ($query) use ($org_id) {
                            return $query->where('organization_id', $org_id);
                        }),
                ],

            ]);
            if ($validator->fails()) {
                return response()->json([
                    'errors' =>
                        $validator->errors()
                ], 422);
            }
            $data = $request->all();
            $holidaytypes = OrganizationHolidayTypes::create($data);
            return response()->json([
                'message' => 'Organization Holiday Type Added SuccessFully.',
                'holidaytypes' => $holidaytypes
            ], 201);
        } catch (\Exception $e) {
            // Handle any other errors and return a response
            return response()->json([
                'error' => 'Something went wrong. Please try again later.',
                'details' => $e->getMessage()
            ], 500);
        }
    }



    // display specific organization 
    public function show(Request $request, $org_id, $holiday_type_id)
    {
        try {
            $user = Auth::guard('applicationusers')->user();
            $organizationIds = $user->Client->Organization->pluck('organization_id')->toArray();
            if (!in_array($org_id, $organizationIds)) {
                return response()->json([
                    'messages' => 'unauthorized'
                ], 401);

            }
            $request->merge(['organization_id' => $org_id, 'organization_holiday_type_id' => $holiday_type_id]);
            $validator = Validator::make($request->all(), [
                'organization_id' => 'required|integer|exists:organizations,organization_id',
                'organization_holiday_type_id' => 'required|integer|exists:organization_holiday_types,organization_holiday_type_id',
            ]);
            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()], 422);
            }
            $holidaytype = OrganizationHolidayTypes::find($holiday_type_id);
            return response()->json([
                'message' => 'Organization Holiday Type Found',
                'holidaytype' => $holidaytype
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Something went wrong. Please try again later.',
                'details' => $e->getMessage()
            ], 500);
        }
    }



    public function update(Request $request, $org_id, $holiday_type_id)
    {
        try {
            $user = Auth::guard('applicationusers')->user();
            $organizationIds =
                $user->Client->Organization->pluck('organization_id')->toArray();
            if (!in_array($org_id, $organizationIds)) {
                return response()->json([
                    'messages' => 'unauthorized'
                ], 401);

            }
            $request->merge([
                'organization_id' => $org_id,
                'organization_holiday_type_id' => $holiday_type_id
            ]);
            $rules = [
                'organization_id' =>
                    'required|integer|exists:organizations,organization_id',
                'organization_holiday_type_id' =>
                    'required|integer|exists:organization_holiday_types,organization_holiday_type_id',
                'holiday_type_name' => [
                    'sometimes',
                    'string',
                    'max:50',
                    Rule::unique(
                        'organization_holiday_types',
                        'holiday_type_name'
                    )
                        ->ignore(
                            $holiday_type_id,
                            'organization_holiday_type_id'
                        )
                        ->where(function ($query) use ($request) {
                            return $query->where(
                                'organization_id',
                                $request->organization_id
                            );
                        }),
                ],

            ];
            $validator = Validator::make($request->all(), $rules);
            if ($validator->fails()) {
                return response()->json([
                    'errors' =>
                        $validator->errors()
                ], 422);
            }
            $holidaytype = OrganizationHolidayTypes::find($holiday_type_id);
            $holidaytype->update($request->only([
                'holiday_type_name',
            ]));
            return response()->json([
                'message' => 'Organization holiday type updated successfully.',
                'holidaytype' => $holidaytype
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Something went wrong. Please try again later.',
                'details' => $e->getMessage()
            ], 500);
        }
    }



    public function destroy(Request $request, $org_id, $holiday_type_id)
    {
        try {
            $user = Auth::guard('applicationusers')->user();
            $organizationIds = $user->Client->Organization->pluck('organization_id')->toArray();
            if (!in_array($org_id, $organizationIds)) {
                return response()->json([
                    'messages' => 'unauthorized'
                ], 401);

            }
            $request->merge(['organization_id' => $org_id, 'organization_holiday_type_id' => $holiday_type_id]);
            $validator = Validator::make($request->all(), [
                'organization_id' => 'required|integer|exists:organizations,organization_id',
                'organization_holiday_type_id' => 'required|integer|exists:organization_holiday_types,organization_holiday_type_id',
            ]);
            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()], 422);
            }
            $holidaytype = OrganizationHolidayTypes::find($holiday_type_id);
            $holidaytype->delete();
            return response()->json([
                'message' => 'Organization holiday Type Deleted Successfully'
            ], 200); // or just remove 200 — it's the default

        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Something went wrong. Please try again later.',
                'details' => $e->getMessage()
            ], 500);
        }
    }
}
