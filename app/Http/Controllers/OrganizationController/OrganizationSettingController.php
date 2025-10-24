<?php

namespace App\Http\Controllers\OrganizationController;

use App\Models\OrganizationModel\OrganizationSetting;
use Illuminate\Support\Facades\Validator;
use App\Http\Controllers\Controller;
use App\Models\OrganizationModel\Organization;
use Exception;
use Illuminate\Http\Request;
use Auth;
use Illuminate\Validation\Rule;

class OrganizationSettingController extends Controller
{

    public function index(Request $request, $org_id)
    {
        try {

            $user = Auth::guard('applicationusers')->user();
            $organizationIds = $user->Client->Organization->pluck('organization_id')->toArray();
            if (!in_array((int) $org_id, $organizationIds)) {
                return response()->json([
                    'message' => 'Unauthenticated'
                ], 401);
            }
            $request->merge(['organization_id' => $org_id]);
            $validator = Validator::make($request->all(), [
                'organization_id' => 'required|integer|exists:organizations,organization_id',
            ]);
            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()], 422);
            }
            $perPage = $request->get('per_page');
            $page = (int) $request->get('page', 1);
            $search = $request->get('search');
            $query = OrganizationSetting::with('SettingType')->where('organization_id', $org_id);
            if (!empty($search)) {
                $query->where(function ($q) use ($search) {
                    $q->where('payroll_component_type_name', 'like', '%' . $search . '%')
                        ->orWhere('description', 'like', '%' . $search . '%');
                });
            }
            if ($perPage === 'all') {
                $statusTypes = $query->get();
            } elseif (!empty($perPage) && is_numeric($perPage)) {
                $perPage = (int) $perPage;
                $statusTypes = $query->paginate($perPage, ['*'], 'page', $page);
            } else {
                $statusTypes = $query->get();
            }
            // Return success response
            return response()->json([
                'message' => 'Payroll Adjustment fetched successfully',
                'settings' => $statusTypes
            ], 200);

        } catch (\Exception $e) {
            \Log::error('Error fetching attendance break types: ' . $e->getMessage());

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
                'organization_setting_type_id' => 'nullable|integer|exists:organization_setting_types,organization_setting_type_id',
                'setting_name' => [
                    'nullable',
                    'string',
                    'max:100',
                    Rule::unique('organization_settings')
                        ->where('organization_id', $request->organization_id)
                ],
                'setting_value' => 'nullable|string',
                'default_value' => 'nullable|string',
                'has_predefined_values' => 'nullable|boolean',
                'min_value' => 'nullable|numeric',
                'max_value' => 'nullable|numeric|gte:min_value',
                'predefined_values' => 'nullable|in:self,manager',
                'unit' => 'nullable|string|max:50',
                'organization_setting_data_type_id' => 'nullable|integer|exists:organization_setting_data_types,organization_setting_data_type_id',
                'min_date' => 'nullable|date',
                'max_date' => 'nullable|date|after_or_equal:min_date',
                'customizable' => 'nullable|boolean',
                'pattern' => 'nullable|string|max:255',
                'is_required' => 'nullable|boolean',
            ]);
            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()], 422);
            }
            $data = $request->all();
            $setting = OrganizationSetting::create($data);
            return response()->json([
                'message' => 'Organization setting  added successfullly.',
                'setting' => $setting
            ], 201);
        } catch (\Exception $e) {
            // Handle any other errors and return a response
            return response()->json([
                'error' => 'Something went wrong. Please try again later.',
                'details' => $e->getMessage()
            ], 500);
        }
    }

    public function show(Request $request, $org_id, $setting_id)
    {
        try {
            $user = Auth::guard('applicationusers')->user();
            $organizationIds = $user->Client->Organization->pluck('organization_id')->toArray();
            if (!in_array($org_id, $organizationIds)) {
                return response()->json([
                    'messages' => 'unauthorized'
                ], 401);
            }

            $request->merge(['organization_id' => $org_id, 'organization_setting_id' => $setting_id]);
            $validator = Validator::make($request->all(), [
                'organization_id' => 'required|integer|exists:organizations,organization_id',
                'organization_setting_id' => 'required|integer|exists:organization_settings,organization_setting_id',
            ]);

            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()], 422);
            }
            $setting = OrganizationSetting::find($setting_id);
            return response()->json([
                'message' => "Orgaization setting  found",
                'setting' => $setting
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Something went wrong. Please try again later.',
                'details' => $e->getMessage()
            ], 500);
        }
    }

    public function update(Request $request, $org_id, $setting_id)
    {
        try {
            $user = Auth::guard('applicationusers')->user();
            $organizationIds = $user->Client->Organization->pluck('organization_id')->toArray();
            if (!in_array($org_id, $organizationIds)) {
                return response()->json([
                    'messages' => 'unauthorized'
                ], 401);
            }
            $request->merge(['organization_id' => $org_id, 'organization_setting_id' => $setting_id]);
            $rules = [
                'organization_id' => 'required|integer|exists:organizations,organization_id',
                'organization_setting_id' => 'required|integer|exists:organization_settings,organization_setting_id',
                'setting_name' => [
                    'nullable',
                    'string',
                    'max:100',
                    Rule::unique('organization_settings')
                        ->where(function ($query) use ($request) {
                            return $query->where('organization_id', $request->organization_id);
                        })
                        ->ignore($request->organization_setting_id, 'organization_setting_id'),
                ],
                'setting_value' => 'nullable|string',
                'default_value' => 'nullable|string',
                'has_predefined_values' => 'nullable|boolean',
                'min_value' => 'nullable|numeric',
                'max_value' => 'nullable|numeric|gte:min_value',
               'predefined_values' => 'nullable|in:,self,manager',
                'unit' => 'nullable|string|max:50',
                'organization_setting_data_type_id' => 'nullable|integer|exists:organization_setting_data_types,organization_setting_data_type_id',
                'organization_setting_type_id' => 'nullable|integer|exists:organization_setting_types,organization_setting_type_id',
                'min_date' => 'nullable|date',
                'max_date' => 'nullable|date|after_or_equal:min_date',
                'customizable' => 'nullable|boolean',
                'pattern' => 'nullable|string|max:255',
                'is_required' => 'nullable|boolean',

            ];
            $validator = Validator::make($request->all(), $rules);
            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()], 422);
            }
            $setting = OrganizationSetting::find($setting_id);
            $setting->update($request->only([
                'setting_name',
                'setting_value',
                'default_value',
                'customizable',
                'min_value',
                'min_value',
                'predefined_values',
                'has_predefined_values',
                'max_date',
                'min_date',
                'is_required',
                'organization_setting_data_type_id',
                'organization_setting_type_id'
            ]));
            return response()->json([
                'message' => 'Organization setting updated successfully.',
                'setting' => $setting
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Something went wrong. Please try again later.',
                'details' => $e->getMessage()
            ], 500);
        }
    }


    public function destroy(Request $request, $org_id, $setting_id)
    {
        try {
            $user = Auth::guard('applicationusers')->user();
            $organizationIds = $user->Client->Organization->pluck('organization_id')->toArray();
            if (!in_array($org_id, $organizationIds)) {
                return response()->json([
                    'messages' => 'unauthorized'
                ], 401);

            }
            $request->merge(['organization_id' => $org_id, 'organization_setting_id' => $setting_id]);
            $validator = Validator::make($request->all(), [
                'organization_id' => 'required|integer|exists:organizations,organization_id',

                'organization_setting_id' => 'required|integer|exists:organization_settings,organization_setting_id',

            ]);
            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()], 422);
            }
            $setting = OrganizationSetting::find($setting_id);
            $setting->delete();
            return response()->json([
                'message' => 'Organization setting  Deleted Successfully'
            ], 200); // or just remove 200 — it's the default


        } catch (Exception $e) {
            if ($e instanceof \Illuminate\Database\QueryException && $e->errorInfo[1] == 1451) {
                // Foreign key constraint violation
                return response()->json([
                    'error' => 'Cannot delete setting type because it is linked with other records. Please delete dependent records first.'
                ], 409); // 409 Conflict
            }

            // For other exceptions
            return response()->json([
                'error' => 'Failed to delete Address Type type.',
                'exception' => $e->getMessage() // Optional: remove in production
            ], 500);
        }
    }
}
