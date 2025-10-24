<?php

namespace App\Http\Controllers\OrganizationController;

use App\Models\OrganizationModel\OrganizationSetting;
use App\Models\OrganizationModel\OrganizationUnits;
use Illuminate\Support\Facades\Validator;
use App\Http\Controllers\Controller;
use App\Models\OrganizationModel\Organization;
use Exception;
use Illuminate\Http\Request;
use Auth;
use Illuminate\Validation\Rule;

class OrganizationUnitController extends Controller
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
                $units = OrganizationUnits::with('UnitTypes')->where('organization_id', $org_id)->get();
                if ($units->isEmpty()) {
                    return response()->json([
                        'message' => 'workshift not found.'
                    ], 404);
                }
                $mappedUnits = $units->map(function ($dep) {
                    return [
                        'unit_name'=>$dep->unit_name,
                        'unit_short_name' => $dep->unit_short_name ?? '',
                        'unit_type' => $dep->unit_types->unit_type_name ?? '',
                        
                    ];
                });
                return response()->json($mappedUnits);
            }
            $query = OrganizationUnits::with('UnitTypes')->where('organization_id', $org_id);
            $per = $request->input('per_page', 10);
            $search = $request->input('search');
            if ($search) {
                $query->where(function ($q) use ($search) {
                    $q->where('unit_name', 'like', "%{$search}%")
                        ->orWhere('unit_short_name', 'like', "%{$search}%");
                });
            }
            $data = $query->orderBy('created_at', 'desc')->paginate($per);
            return response()->json([
                'message' => 'OK',
                'Unit' => $data,
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
                'organization_unit_type_id' => 'required|integer|exists:organization_unit_types,organization_unit_type_id',
                'unit_name' => [
                    'required',
                    'string',
                    'max:255',
                    Rule::unique('organization_units', 'unit_name')
                        ->where(function ($query) use ($request) {
                            return $query->where('organization_id', $request->organization_id);
                        }),
                ],
                'unit_short_name' => [
                    'nullable',
                    'string',
                    'max:255',
                    Rule::unique('organization_units', 'unit_short_name')
                        ->where(function ($query) use ($request) {
                            return $query->where('organization_id', $request->organization_id);
                        }),
                ],


            ]);
            // Return validation errors if any
            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()], 422);
            }
            $data = $request->all();
            $units = OrganizationUnits::create($data);
            return response()->json([
                'message' => 'Organization unit  added successfullly.',
                'units' => $units
            ], 201);
        } catch (\Exception $e) {
            // Handle any other errors and return a response
            return response()->json([
                'error' => 'Something went wrong. Please try again later.',
                'details' => $e->getMessage()
            ], 500);
        }
    }

    public function show(Request $request, $org_id, $unit_id)
    {
        try {
            $user = Auth::guard('applicationusers')->user();
            $organizationIds = $user->Client->Organization->pluck('organization_id')->toArray();
            if (!in_array($org_id, $organizationIds)) {
                return response()->json([
                    'messages' => 'unauthorized'
                ], 401);

            }
            // Merge org_id from route into request for validation
            $request->merge(['organization_id' => $org_id, 'organization_unit_id' => $unit_id]);
            $validator = Validator::make($request->all(), [
                'organization_id' => 'required|integer|exists:organizations,organization_id',
                'organization_unit_id' => 'required|integer|exists:organization_units,organization_unit_id',
            ]);

            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()], 422);
            }
            $units = OrganizationUnits::with('UnitTypes')->find($unit_id);
            return response()->json([
                'message' => "Orgaization unit  found",
                'units' => $units
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Something went wrong. Please try again later.',
                'details' => $e->getMessage()
            ], 500);
        }
    }

    public function update(Request $request, $org_id, $unit_id)
    {
        try {
            $user = Auth::guard('applicationusers')->user();
            $organizationIds = $user->Client->Organization->pluck('organization_id')->toArray();
            if (!in_array($org_id, $organizationIds)) {
                return response()->json([
                    'messages' => 'unauthorized'
                ], 401);

            }
            $request->merge(['organization_id' => $org_id, 'organization_unit_id' => $unit_id]);
            $rules = [
                'organization_id' => 'required|integer|exists:organizations,organization_id',
                'organization_unit_id' => 'required|integer|exists:organization_units,organization_unit_id',
                'organization_unit_type_id' => 'sometimes|nullable|integer|exists:organization_unit_types,organization_unit_type_id',
                'unit_name' => [
                    'nullable',
                    'string',
                    'max:255',
                    Rule::unique('organization_units', 'unit_name')
                        ->where(function ($query) use ($request) {
                            return $query->where('organization_id', $request->organization_id);
                        })
                        ->ignore($unit_id, 'organization_unit_id'),
                ],

                'unit_short_name' => [
                    'nullable',
                    'string',
                    'max:255',
                    Rule::unique('organization_units', 'unit_short_name')
                        ->where(function ($query) use ($request) {
                            return $query->where('organization_id', $request->organization_id);
                        })
                        ->ignore($unit_id, 'organization_unit_id'),
                ],

            ];

            // Run validation
            $validator = Validator::make($request->all(), $rules);

            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()], 422);
            }
            $unit = OrganizationUnits::find($unit_id);
            $unit->update($request->only([
                'organization_unit_type_id',
                'unit_short_name',
                'unit_name',

            ]));

            return response()->json([
                'message' => 'Organization unit updated successfully.',
                'unit' => $unit
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Something went wrong. Please try again later.',
                'details' => $e->getMessage()
            ], 500);
        }
    }

    public function destroy(Request $request, $org_id, $unit_id)
    {
        try {
            $user = Auth::guard('applicationusers')->user();
            $organizationIds = $user->Client->Organization->pluck('organization_id')->toArray();
            if (!in_array($org_id, $organizationIds)) {
                return response()->json([
                    'messages' => 'unauthorized'
                ], 401);

            }
            $request->merge(['organization_id' => $org_id, 'organization_unit_id' => $unit_id]);
            $validator = Validator::make($request->all(), [
                'organization_id' => 'required|integer|exists:organizations,organization_id',

                'organization_unit_id' => 'required|integer|exists:organization_units,organization_unit_id',

            ]);
            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()], 422);
            }
            $units = OrganizationUnits::find($unit_id);
            $units->delete();
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
