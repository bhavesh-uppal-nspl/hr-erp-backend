<?php

namespace App\Http\Controllers\OrganizationController;

use App\Models\OrganizationModel\OrganizationDesignation;
use App\Models\OrganizationModel\OrganizationEntities;
use Illuminate\Support\Facades\Validator;
use App\Http\Controllers\Controller;
use App\Models\OrganizationModel\Organization;
use App\Models\OrganizationModel\OrganizationDepartment;
use Exception;
use Illuminate\Http\Request;
use Auth;
use Illuminate\Validation\Rule;

class OrganizationEntityController extends Controller
{


    public function index(Request $request, $org_id, $entity_id)
    {
        try {

            $user = Auth::guard('applicationusers')->user();
            $organizationIds = $user->Client->Organization->pluck('organization_id')->toArray();
            if (!in_array($org_id, $organizationIds)) {
                return response()->json([
                    'messages' => 'unauthorized'
                ], 401);

            }
            $request->merge(['organization_id' => $org_id, 'organization_entity_id' => $entity_id]);
            $validator = Validator::make($request->all(), [
                'organization_id' => 'required|integer|exists:organizations,organization_id',
                'organization_entity_id' => 'required|integer|exists:organization_entities,organization_entity_id',
            ]);
            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()], 422);
            }
            // $departments = Organization::find($org_id)->departments;
            $entity = Organization::find($org_id)->Entities;
            return response()->json([
                'message' => 'All Organization Entities',
                'entity' => $entity
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Something went wrong. Please try again later.',
                'details' => $e->getMessage()
            ], 500);
        }

    }

    public function store(Request $request, $org_id, $entity_id)
    {
        try {
            $user = Auth::guard('applicationusers')->user();
            $organizationIds = $user->Client->Organization->pluck('organization_id')->toArray();
            if (!in_array($org_id, $organizationIds)) {
                return response()->json([
                    'messages' => 'unauthorized'
                ], 401);

            }
            $request->merge(['organization_id' => $org_id, 'organization_entity_id' => $entity_id]);
            $validator = Validator::make($request->all(), [
                'organization_id' => 'required|integer|exists:organizations,organization_id',
                'department_name' => 'required|string|max:255|unique:organization_departments,department_name',
                'department_short_name' => 'required|string|max:100|unique:organization_departments,department_short_name',
                'department_mail_id' => 'sometimes|nullable|string|max:100',
                'department_employees_count' => 'nullable|integer|min:0',
                'organization_entity_id' => 'required|integer|exists:organization_entities,organization_entity_id',
                'description' => 'sometimes|nullable|string|max:1000',
            ]);
            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()], 422);
            }
            $department = OrganizationDepartment::create($request->all());
            return response()->json([
                'message' => 'Organization Department Added SuccessFully.',
                'department' => $department
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
    public function show(Request $request, $org_id, $entity_id)
    {
        try {
            $user = Auth::guard('applicationusers')->user();
            $organizationIds = $user->Client->Organization->pluck('organization_id')->toArray();
            if (!in_array($org_id, $organizationIds)) {
                return response()->json([
                    'messages' => 'unauthorized'
                ], 401);

            }
            $request->merge(['organization_id' => $org_id, 'organization_entity_id' => $entity_id]);
            $validator = Validator::make($request->all(), [
                'organization_id' => 'required|integer|exists:organizations,organization_id',
                'organization_entity_id' => 'required|integer|exists:organization_entities,organization_entity_id',

            ]);
            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()], 422);
            }
            $entity = OrganizationEntities::find($entity_id);
            return response()->json([
                'message' => 'Organization Entity Found',
                'entity' => $entity
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Something went wrong. Please try again later.',
                'details' => $e->getMessage()
            ], 500);
        }
    }

    // update the orgaization 
    public function update(Request $request, $org_id, $entity_id)
    {
        try {
            $user = Auth::guard('applicationusers')->user();
            $organizationIds = $user->Client->Organization->pluck('organization_id')->toArray();
            if (!in_array($org_id, $organizationIds)) {
                return response()->json([
                    'messages' => 'unauthorized'
                ], 401);

            }
            $request->merge([
                'organization_id' => $org_id,
                'organization_entity_id' => $entity_id,

            ]);
            $rules = [
                'organization_id' => 'required|integer|exists:organizations,organization_id',

                'organization_entity_id' => 'required|integer|exists:organization_entities,organization_entity_id',

                'entity_name' => [
                    'sometimes',
                    'string',
                    'max:255',
                    Rule::unique('organization_entities', 'entity_name')->ignore($entity_id, 'organization_entity_id')
                ],
                'entity_short_name' => [
                    'sometimes',
                    'string',
                    'max:100',
                    Rule::unique('organization_entities', 'entity_short_name')->ignore($entity_id, 'organization_entity_id')
                ],

            ];
            $validator = Validator::make($request->all(), $rules);
            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()], 422);
            }
            $entity = OrganizationEntities::find($entity_id);
            $entity->update($request->only([
                'entity_short_name',
                'entity_name',
            ]));

            return response()->json([
                'message' => 'Department  updated successfully.',
                'entity' => $entity
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Something went wrong. Please try again later.',
                'details' => $e->getMessage()
            ], 500);
        }
    }

    // delete the orgaization  
//     public function destroy(Request $request, $org_id, $entity_id,$department_id)
//   {
//         try {    $user = Auth::guard('applicationusers')->user();
//              $organizationIds = $user->Client->Organization->pluck('organization_id')->toArray();
//             if (!in_array($org_id, $organizationIds)) {
//                 return response()->json([
//                     'messages' => 'unauthorized'
//                 ], 401);

    //             }
//             $request->merge(['organization_id' => $org_id,'organization_entity_id'=>$entity_id, 'organization_department_id' => $department_id]);

    //             $validator = Validator::make($request->all(), [
//                 'organization_id' => 'required|integer|exists:organizations,organization_id',
//                 'organization_department_id' => 'required|integer|exists:organization_departments,organization_department_id',
//                 'organization_entity_id' => 'required|integer|exists:organization_entities,organization_entity_id',
//             ]);

    //             if ($validator->fails()) {
//                 return response()->json(['errors' => $validator->errors()], 422);
//             }
//             // OrganizationDesignation::where('organization_department_id', $department_id)->delete();

    //             $department = OrganizationDepartment::find($department_id);
//             $department->delete();

    //             return response()->json([
//                 'message' => 'Department Deleted Successfully'
//             ]);

    //         } catch (\Exception $e) {

    //             return response()->json([
//                 'error' => 'Something went wrong. Please try again later.',
//                 'details' => $e->getMessage()
//             ], 500);
//         }
//     }

}
