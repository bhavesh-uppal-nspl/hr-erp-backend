<?php

namespace App\Http\Controllers\DataGridController;
use App\Models\GeneralDataGrid\OrganizationDataGrid;
use App\Models\GeneralDataGrid\OrganizationUserDataGrid;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use App\Http\Controllers\Controller;
use App\Models\GeneralDataGrid\GeneralDataGrid;
use Illuminate\Http\Request;


class GeneralDataGridController extends Controller
{
    public function index()
    {
        try {
            $datagrids = GeneralDataGrid::all();
            return response()->json([
                "message" => "All General DataGrid Configurations",
                'datagrids' => $datagrids
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Something went wrong. Please try again later.',
                'details' => $e->getMessage()
            ], 500);
        }
    }

    public function store(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'datagrid_key' => 'required|string|max:255|unique:general_datagrid_default_configurations,datagrid_key',
                'datagrid_default_configuration' => 'required|array',
            ]);

            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()], 422);
            }

            $datagrid = GeneralDataGrid::create($request->all());

            return response()->json([
                'datagrid' => $datagrid,
                'message' => 'General DataGrid configuration created successfully.'
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Something went wrong. Please try again later.',
                'details' => $e->getMessage()
            ], 500);
        }
    }

    public function show(Request $request, $datagrid_id)
    {
        try {
            $request->merge(['general_datagrid_default_configuration_id' => $datagrid_id]);

            $datagrid = GeneralDataGrid::findOrFail($datagrid_id);

            return response()->json([
                'message' => 'General DataGrid Configuration Found',
                'datagrid' => $datagrid
            ], 200);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'error' => 'General DataGrid configuration not found with the provided ID.'
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Something went wrong. Please try again later.',
                'details' => $e->getMessage()
            ], 500);
        }
    }

    public function update(Request $request, $datagrid_id)
    {
        try {
            $request->merge(['general_datagrid_default_configuration_id' => $datagrid_id]);

            $validator = Validator::make($request->all(), [
                'general_datagrid_default_configuration_id' => 'required|integer|exists:general_datagrid_default_configurations,general_datagrid_default_configuration_id',
                'datagrid_key' => 'nullable|string|max:255|unique:general_datagrid_default_configurations,datagrid_key,' . $datagrid_id . ',general_datagrid_default_configuration_id',
                'datagrid_default_configuration' => 'nullable|array',
            ]);

            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()], 422);
            }

            $datagrid = GeneralDataGrid::find($datagrid_id);
            $datagrid->update($request->only(['datagrid_key', 'datagrid_default_configuration']));

            return response()->json([
                'message' => 'General DataGrid configuration updated successfully.',
                'datagrid' => $datagrid
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Something went wrong. Please try again later.',
                'details' => $e->getMessage()
            ], 500);
        }
    }

    public function destroy(Request $request, $datagrid_id)
    {
        try {
            $request->merge(['general_datagrid_default_configuration_id' => $datagrid_id]);

            $datagrid = GeneralDataGrid::findOrFail($datagrid_id);
            $datagrid->delete();

            return response()->json([
                'message' => 'General DataGrid configuration deleted successfully'
            ], 200);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'error' => 'General DataGrid configuration not found with the provided ID.'
            ], 404);
        } catch (\Illuminate\Database\QueryException $e) {
            if ($e->getCode() == 23000) {
                return response()->json([
                    'error' => 'Cannot delete this DataGrid configuration because it is linked with other records.'
                ], 409);
            }
            return response()->json([
                'error' => 'Database error: ' . $e->getMessage()
            ], 500);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Something went wrong. Please try again later.',
                'details' => $e->getMessage()
            ], 500);
        }
    }

     public function getDataGridByContext(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'organization_user_id' => 'nullable|integer|exists:organization_users,organization_user_id',
                'organization_id' => 'nullable|integer|exists:organizations,organization_id',
                'datagrid_key' => 'required|string|max:255',
            ]);

            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()], 422);
            }

            $organizationUserId = $request->input('organization_user_id');
            $organizationId = $request->input('organization_id');
            $datagridKey = $request->input('datagrid_key');

            // Priority 1: Check for Organization User DataGrid
            if ($organizationUserId) {
                $datagrid = OrganizationUserDataGrid::where('organization_user_id', $organizationUserId)
                    ->where('datagrid_key', $datagridKey)
                    ->first();

                if ($datagrid) {
                    return response()->json([
                        'message' => 'Organization User DataGrid Configuration Found',
                        'type' => 'organization_user',
                        'context' => [
                            'organization_user_id' => $organizationUserId,
                            'organization_id' => $organizationId
                        ],
                        'datagrid' => $datagrid
                    ], 200);
                }
            }

            // Priority 2: Check for Organization DataGrid
            if ($organizationId) {
                $datagrid = OrganizationDataGrid::where('organization_id', $organizationId)
                    ->where('datagrid_key', $datagridKey)
                    ->first();

                if ($datagrid) {
                    return response()->json([
                        'message' => 'Organization DataGrid Configuration Found',
                        'type' => 'organization',
                        'context' => [
                            'organization_id' => $organizationId
                        ],
                        'datagrid' => $datagrid
                    ], 200);
                }
            }

            // Priority 3: Fallback to General DataGrid
            $datagrid = GeneralDataGrid::where('datagrid_key', $datagridKey)->first();

            if ($datagrid) {
                return response()->json([
                    'message' => 'General DataGrid Configuration Found',
                    'type' => 'general',
                    'context' => [],
                    'datagrid' => $datagrid
                ], 200);
            }

            // No datagrid found
            return response()->json([
                'error' => 'No DataGrid configuration found for the provided context.',
                'datagrid_key' => $datagridKey
            ], 404);

        } catch (\Exception $e) {
            Log::error('Error fetching datagrid by context', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'error' => 'Something went wrong. Please try again later.',
                'details' => $e->getMessage()
            ], 500);
        }
    }

    public function getDataGridByContextDelete(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'organization_user_id' => 'nullable|integer|exists:organization_users,organization_user_id',
                'organization_id' => 'nullable|integer|exists:organizations,organization_id',
                'datagrid_key' => 'required|string|max:255',
            ]);

            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()], 422);
            }

            $organizationUserId = $request->input('organization_user_id');
            $organizationId = $request->input('organization_id');
            $datagridKey = $request->input('datagrid_key');

            // Priority 1: Check for Organization User DataGrid
            if ($organizationUserId) {
                $datagrid = OrganizationUserDataGrid::where('organization_user_id', $organizationUserId)
                    ->where('datagrid_key', $datagridKey)
                    ->first();

                if ($datagrid) {
                    $datagrid->delete();
                    return response()->json([
                        'message' => 'Organization User DataGrid Configuration Deleted Successfully',
                        'type' => 'organization_user',
                        'context' => [
                            'organization_user_id' => $organizationUserId,
                            'organization_id' => $organizationId
                        ]
                    ], 200);
                }
            }

            // Priority 2: Check for Organization DataGrid
            if ($organizationId) {
                $datagrid = OrganizationDataGrid::where('organization_id', $organizationId)
                    ->where('datagrid_key', $datagridKey)
                    ->first();

                if ($datagrid) {
                    $datagrid->delete();
                    return response()->json([
                        'message' => 'Organization DataGrid Configuration Deleted Successfully',
                        'type' => 'organization',
                        'context' => [
                            'organization_id' => $organizationId
                        ]
                    ], 200);
                }
            }
        } catch (\Exception $e) {
            Log::error('Error deleting datagrid by context', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'error' => 'Something went wrong. Please try again later.',
                'details' => $e->getMessage()
            ], 500);
        }
        return response()->json([
            'error' => 'No DataGrid configuration found to delete for the provided context.',
            'datagrid_key' => $datagridKey
        ], 404);
    }
}