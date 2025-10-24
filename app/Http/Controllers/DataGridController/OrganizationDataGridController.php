<?php

namespace App\Http\Controllers\DataGridController;
use App\Http\Controllers\Controller;
use App\Models\GeneralDataGrid\OrganizationDataGrid;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class OrganizationDataGridController extends Controller
{
    public function index()
    {
        try {
            $datagrids = OrganizationDataGrid::all();
            return response()->json([
                'message' => 'All Organization DataGrid Configurations',
                'datagrids' => $datagrids
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Failed to fetch Organization Data Grid',
                'details' => $e->getMessage()
            ], 500);
        }
    }

    public function store(Request $request)
{
    try {
        $validator = Validator::make($request->all(), [
            'organization_id' => 'required|integer|exists:organizations,organization_id',
            'organization_entity_id' => 'nullable|integer',
            'datagrid_key' => 'required|string|max:100',
            'datagrid_default_configuration' => 'nullable|array',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        // **UPDATED: Use updateOrCreate instead of create**
        $datagrid = OrganizationDataGrid::updateOrCreate(
            [
                'organization_id' => $request->organization_id,
                'datagrid_key' => $request->datagrid_key,
            ],
            [
                'organization_entity_id' => $request->organization_entity_id,
                'datagrid_default_configuration' => $request->datagrid_default_configuration,
            ]
        );

        return response()->json([
            'message' => 'Organization Data Grid saved successfully',
            'datagrid' => $datagrid
        ], 200);
    } catch (\Exception $e) {
        return response()->json([
            'error' => 'Failed to save Organization Data Grid',
            'details' => $e->getMessage()
        ], 500);
    }
}


    public function show(Request $request, $id)
    {
        try {
            $request->merge(['organization_datagrid_default_configuration_id' => $id]);

            $datagrid = OrganizationDataGrid::findOrFail($id);

            return response()->json([
                'message' => 'Organization Data Grid fetched successfully',
                'datagrid' => $datagrid
            ], 200);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'error' => 'Organization Data Grid configuration not found with the provided ID.'
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Failed to fetch Organization Data Grid',
                'details' => $e->getMessage()
            ], 500);
        }
    }

    public function update(Request $request, $id)
    {
        try {
            $request->merge(['organization_datagrid_default_configuration_id' => $id]);

            $validator = Validator::make($request->all(), [
                'organization_datagrid_default_configuration_id' => 'required|integer|exists:organization_datagrid_default_configurations,organization_datagrid_default_configuration_id',
                'organization_id' => 'nullable|integer|exists:organizations,organization_id',
                'organization_entity_id' => 'nullable|integer',
                'datagrid_key' => 'nullable|string|max:100',
                'datagrid_default_configuration' => 'nullable|array',
            ]);

            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()], 422);
            }

            $datagrid = OrganizationDataGrid::findOrFail($id);
            $datagrid->update($request->only(['organization_id', 'organization_entity_id', 'datagrid_key', 'datagrid_default_configuration']));

            return response()->json([
                'message' => 'Organization Data Grid updated successfully',
                'datagrid' => $datagrid
            ], 200);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'error' => 'Organization Data Grid configuration not found with the provided ID.'
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Failed to update Organization Data Grid',
                'details' => $e->getMessage()
            ], 500);
        }
    }

    public function destroy(Request $request, $id)
    {
        try {
            $request->merge(['organization_datagrid_default_configuration_id' => $id]);

            $datagrid = OrganizationDataGrid::findOrFail($id);
            $datagrid->delete();

            return response()->json([
                'message' => 'Organization Data Grid deleted successfully'
            ], 200);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'error' => 'Organization Data Grid configuration not found with the provided ID.'
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
                'error' => 'Failed to delete Organization Data Grid',
                'details' => $e->getMessage()
            ], 500);
        }
    }

    // Add this method to your existing controller
public function getByOrganizationAndKey(Request $request, $organization_id)
{
    try {
        $validator = Validator::make([
            'organization_id' => $organization_id,
            'datagrid_key' => $request->query('datagrid_key')
        ], [
            'organization_id' => 'required|integer',
            'datagrid_key' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $datagrid = OrganizationDataGrid::where('organization_id', $organization_id)
            ->where('datagrid_key', $request->query('datagrid_key'))
            ->first();

        if (!$datagrid) {
            return response()->json([
                'message' => 'No saved configuration found'
            ], 404);
        }

        return response()->json([
            'message' => 'Organization Data Grid fetched successfully',
            'datagrid' => $datagrid
        ], 200);
    } catch (\Exception $e) {
        return response()->json([
            'error' => 'Failed to fetch Organization Data Grid',
            'details' => $e->getMessage()
        ], 500);
    }
}

}
