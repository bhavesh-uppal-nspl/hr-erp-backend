<?php

;


namespace App\Http\Controllers\OrganizationController;

use Illuminate\Support\Facades\Validator;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;    use Auth;
use Exception;


use App\Models\OrganizationModel\OrganizationLocsOwShType;

class OrganizationLocsOwTypesController extends Controller
{
    
//     // GET /ownership
//     public function index()
//     {
       
//         try {

//             $organizations = OrganizationLocsOwShType::all();

//             return response()->json([

//                 'ownershipType' => $organizations
//             ], 200);

//             // Fetch documents for the specified registration
//         } catch (Exception $e) {
//             return response()->json(['error' => 'Failed to fetch ownership types.'], 500);
//         }
//     }

//     // POST /ownership
//     public function store(Request $request)
//     {
//         try {
//             // Merge org_id and reg_id into the request for validation

//             // Validate inputs
//             $validator = Validator::make($request->all(), [

//                 'ownership_tp_name' => 'nullable|string|max:20',

//             ]);

//             // Return validation errors if any
//             if ($validator->fails()) {
//                 return response()->json([
//                     'error' => 'Validation failed',
//                     'message' => $validator->errors()
//                 ], 422);
//             }


//             $data = $request->all();

//             $ownershiptype = OrganizationLocsOwShType::create(array_merge($data));
//             return response()->json([
//                 'message' => 'Added SuccessFully.',
//                 'location' => $ownershiptype
//             ], 201);




//         } catch (\Exception $e) {
//             // Handle any other errors and return a response
//             return response()->json([
//                 'error' => 'Something went wrong. Please try again later.',
//                 'details' => $e->getMessage()
//             ], 500);
//         }
//     }

//     // GET /ownership/{ownership_id}
//     public function show(Request $request, $ownership_type_id)
//     {
       
//         try {

          
//             // Merge org_id from route into request for validation
//             $request->merge(['ownership_type_id' => $ownership_type_id]);

//             // Validate the org_id
//             $validator = Validator::make($request->all(), [
//                 'ownership_type_id' => 'required|integer|exists:organization_locs_ow_sh_types,ownership_type_id',


//             ]);

           
//             if ($validator->fails()) {
//                 return response()->json([
//                     'error' => 'Validation failed',
//                     'message' => $validator->errors()
//                 ], 422);
//             }

          

//             $ownershipType = OrganizationLocsOwTypesController::find($ownership_type_id);
//             return response()->json([

//                 'ownershipType' => $ownershipType
//             ], 200);

//         } catch (\Exception $e) {
//             return response()->json([
//                 'error' => 'Something went wrong. Please try again later.',
//                 'details' => $e->getMessage()
//             ], 500);
//         }
//     }

//     // DELETE /ownership/{ownership_id}

//     public function destroy(Request $request, $ownership_type_id)
// {
//     try {
//         $request->merge(['ownership_type_id' => $ownership_type_id]);

//         // Validate the ownership_type_id
//         $validator = Validator::make($request->all(), [
//             'ownership_type_id' => 'required|integer|exists:organization_locs_ow_sh_types,ownership_type_id',
//         ]);

//         if ($validator->fails()) {
//             return response()->json([
//                 'error' => 'Validation failed',
//                 'message' => $validator->errors()
//             ], 422);
//         }

//         $ownershipType = OrganizationLocsOwShType::find($ownership_type_id);
//         if (!$ownershipType) {
//             return response()->json([
//                 'error' => 'Ownership type not found.'
//             ], 404);
//         }

//         $ownershipType->delete();

//         return response()->json([
//             'message' => 'Ownership type deleted successfully.'
//         ], 200);

//     } catch (Exception $e) {
//         if ($e instanceof \Illuminate\Database\QueryException && $e->errorInfo[1] == 1451) {
//             // Foreign key constraint violation
//             return response()->json([
//                 'error' => 'Cannot delete ownership type because it is linked with other records. Please delete dependent records first.'
//             ], 409); // 409 Conflict
//         }
    
//         // For other exceptions
//         return response()->json([
//             'error' => 'Failed to delete ownership type.',
//             'exception' => $e->getMessage() // Optional: remove in production
//         ], 500);
//     }
    
// }

  
//     // PUT or PATCH /ownership/{ownership_id}
//     public function update(Request $request,$ownership_type_id)
//     {
//         try {
//             // Merge org_id from route into request for validation
//             $request->merge([ 'ownership_type_id' => $ownership_type_id]);

//             // Validate the org_id
//             $validator = Validator::make($request->all(), [
//                 'ownership_type_id' => 'required|string|exists:organization_locs_ow_sh_types,ownership_type_id ',

//             ]);

//             $validator = Validator::make($request->all(), [
//                 'owner_tp_name' => 'required|string|max:255',
//             ]);



//             if ($validator->fails()) {
//                 return response()->json([
//                     'error' => 'Validation failed',
//                     'message' => $validator->errors()
//                 ], 422);
//             }


//             $ownershipType = OrganizationLocsOwShType::find($ownership_type_id);
//             $ownershipType->update($request->all());

//             return response()->json([
//                 'message' => 'Updated SuccessFully',
//                 'ownershipType' => $ownershipType
//             ], 200);
//         } catch (Exception $e) {
//             return response()->json(['error' => 'Failed to update ownership type.'], 500);
//         }
//     }

}
