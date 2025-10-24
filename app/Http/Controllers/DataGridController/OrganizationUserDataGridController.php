<?php
namespace App\Http\Controllers\DataGridController;
use App\Http\Controllers\Controller;
use App\Models\GeneralDataGrid\OrganizationUserDataGrid;
use Illuminate\Http\Request;
class OrganizationUserDataGridContoller extends Controller
{
       public function index()
    {
        try {
            $datagrids = OrganizationUserDataGrid::all();
            return response()->json(['message' => 'All Organization User DataGrid Configurations',
                'datagrids' => $datagrids
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Failed to fetch Organization User Data Grid',
                'details' => $e->getMessage()
            ], 500);
        }
    }

  
    public function store(Request $request)
    {
        try {
            $request->validate([
                'organization_user_id' => 'required|integer|exists:organization_users,organization_user_id',
                'datagrid_key' => 'required|string|max:100',
                'datagrid_configuration' => 'nullable|array',
            ]);

            $datagrid = OrganizationUserDataGrid::updateOrCreate(
                [
                    'organization_user_id' => $request->organization_user_id,
                    'datagrid_key' => $request->datagrid_key,
                ],
                [
                    'datagrid_configuration' => $request->datagrid_configuration,
                ]
            );

            return response()->json([
                'message' => 'Organization User Data Grid saved successfully',
                'datagrid' => $datagrid
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Failed to save Organization User Data Grid',
                'details' => $e->getMessage()
            ], 500);
        }
    }

   
    public function show($id)
    {
        try {
            $datagrid = OrganizationUserDataGrid::findOrFail($id);
            return response()->json([
                'message' => 'Organization User Data Grid Configuration',
                'datagrid' => $datagrid
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Failed to fetch Organization User Data Grid',
                'details' => $e->getMessage()
            ], 500);
        }
    }

   
    public function update(Request $request, $id)
    {
        try {
            $request->validate([
                'organization_user_id' => 'nullable|integer|exists:organization_users,organization_user_id',
                'datagrid_key' => 'nullable|string|max:100',
                'datagrid_configuration' => 'nullable|array',
            ]);

            $datagrid = OrganizationUserDataGrid::findOrFail($id);

            if ($request->has('organization_user_id')) {
                $datagrid->organization_user_id = $request->organization_user_id;
            }
            if ($request->has('datagrid_key')) {
                $datagrid->datagrid_key = $request->datagrid_key;
            }
            if ($request->has('datagrid_configuration')) {
                $datagrid->datagrid_configuration = $request->datagrid_configuration;
            }

            $datagrid->save();

            return response()->json([
                'message' => 'Organization User Data Grid updated successfully',
                'datagrid' => $datagrid
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Failed to update Organization User Data Grid',
                'details' => $e->getMessage()
            ], 500);
        }
    }

    
    public function destroy($id)
    {
        try {
            $datagrid = OrganizationUserDataGrid::findOrFail($id);
            $datagrid->delete();

            return response()->json([
                'message' => 'Organization User Data Grid deleted successfully'
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Failed to delete Organization User Data Grid',
                'details' => $e->getMessage()
            ], 500);
        }
    }
   
}