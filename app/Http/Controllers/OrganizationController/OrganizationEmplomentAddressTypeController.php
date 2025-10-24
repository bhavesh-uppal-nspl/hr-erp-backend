<?php

namespace App\Http\Controllers\OrganizationController;

use Illuminate\Support\Facades\Validator;
use App\Http\Controllers\Controller;
use App\Models\OrganizationModel\Organization;
use App\Models\OrganizationModel\OrganizationEmpAddType;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Auth;

class OrganizationEmplomentAddressTypeController extends Controller
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

            $query = OrganizationEmpAddType::with('organization')->where('organization_id', $org_id);
            $per = $request->input('per_page', 10);
            $search = $request->input('search');
            if ($search) {
                $query->where(function ($q) use ($search) {
                    $q->where('employee_address_type_name', 'like', "%{$search}%");
                });
            }
            $data = $query->orderBy('created_at', 'desc')->paginate($per);
            return response()->json([
                'message' => 'OK',
                'addresstype' => $data,

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

                'employee_address_type_name' => [
                    'sometimes',
                    'nullable',
                    'string',
                    'max:255',
                    Rule::unique('organization_employee_address_types', 'employee_address_type_name')
                        ->where(function ($query) use ($org_id) {
                            return $query->where('organization_id', $org_id);
                        }),
                ],

            ]);
            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()], 422);
            }
            $data = $request->all();
            $addresstype = OrganizationEmpAddType::create($data);
            return response()->json([
                'message' => 'Organization Employee Address Type Added SuccessFully.',
                'addresstype' => $addresstype
            ], 201);
        } catch (\Exception $e) {

            return response()->json([
                'error' => 'Something went wrong. Please try again later.',
                'details' => $e->getMessage()
            ], 500);
        }
    }


    public function show(Request $request, $org_id, $address_type_id)
    {
        try {
            $user = Auth::guard('applicationusers')->user();
            $organizationIds = $user->Client->Organization->pluck('organization_id')->toArray();
            if (!in_array($org_id, $organizationIds)) {
                return response()->json([
                    'messages' => 'unauthorized'
                ], 401);

            }
            $request->merge(['organization_id' => $org_id, 'organization_employee_address_type_id' => $address_type_id]);
            $validator = Validator::make($request->all(), [
                'organization_id' => 'required|integer|exists:organizations,organization_id',
                'organization_employee_address_type_id' => 'required|integer|exists:organization_employee_address_types,organization_employee_address_type_id',
            ]);
            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()], 422);
            }
            $addresstype = OrganizationEmpAddType::find($address_type_id);
            return response()->json([
                'message' => "Orgaization Employee Address Type  Found",
                'addresstype' => $addresstype
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Something went wrong. Please try again later.',
                'details' => $e->getMessage()
            ], 500);
        }
    }

    public function update(Request $request, $org_id, $address_type_id)
    {
        try {
            $user = Auth::guard('applicationusers')->user();
            $organizationIds = $user->Client->Organization->pluck('organization_id')->toArray();
            if (!in_array($org_id, $organizationIds)) {
                return response()->json([
                    'messages' => 'unauthorized'
                ], 401);

            }
            // Merge route parameters into the request for validation
            $request->merge(['organization_id' => $org_id, 'organization_employee_address_type_id' => $address_type_id]);

            $rules = [
                'organization_id' => 'required|integer|exists:organizations,organization_id',
                'organization_employee_address_type_id' => 'required|integer|exists:organization_employee_address_types,organization_employee_address_type_id',
                'employee_address_type_name' => [
                    'sometimes',
                    'string',
                    'max:255',
                    Rule::unique('organization_employee_address_types', 'employee_address_type_name')
                        ->ignore($address_type_id, 'organization_employee_address_type_id')
                        ->where(function ($query) use ($request) {
                            return $query->where('organization_id', $request->organization_id);
                        }),
                ],



            ];

            // Run validation
            $validator = Validator::make($request->all(), $rules);

            if ($validator->fails()) {
                return response()->json([
                    'error' => 'Validation failed',
                    'message' => $validator->errors()
                ], 422);
            }

            // Find the location
            $addresstype = OrganizationEmpAddType::find($address_type_id);

            $addresstype->update($request->only([
                'employee_address_type_name'

            ]));

            return response()->json([
                'message' => 'Organization Employee Address Type updated successfully.',
                'addresstype' => $addresstype
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Something went wrong. Please try again later.',
                'details' => $e->getMessage()
            ], 500);
        }
    }

    public function destroy(Request $request, $org_id, $address_type_id)
    {
        try {
            $user = Auth::guard('applicationusers')->user();
            $organizationIds = $user->Client->Organization->pluck('organization_id')->toArray();
            if (!in_array($org_id, $organizationIds)) {
                return response()->json([
                    'messages' => 'unauthorized'
                ], 401);

            }
            $request->merge(['organization_id' => $org_id, 'organization_employee_address_type_id' => $address_type_id]);
            $validator = Validator::make($request->all(), [
                'organization_id' => 'required|integer|exists:organizations,organization_id',
                'organization_employee_address_type_id' => 'required|integer|exists:organization_employee_address_types,organization_employee_address_type_id',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'error' => 'Validation failed',
                    'message' => $validator->errors()
                ], 422);
            }

            $addresstype = OrganizationEmpAddType::find($address_type_id);
            $addresstype->delete();
            return response()->json([
                'message' => 'OrganizationEmployee Address Type Deleted Successfully'
            ], 200);


        } catch (Exception $e) {
            if ($e instanceof \Illuminate\Database\QueryException && $e->errorInfo[1] == 1451) {
                // Foreign key constraint violation
                return response()->json([
                    'error' => 'Cannot delete Business Unit type because it is linked with other records. Please delete dependent records first.'
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
