<?php

namespace App\Http\Controllers\EmployeeController;
use App\Models\EmployeesModel\EmployeeAddress;
use Illuminate\Support\Facades\Validator;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Auth;

class EmployeeAddressController extends Controller
{
    public function index(Request $request, $org_id)
    {
        try {

             $user = Auth::guard('applicationusers')->user();
             $organizationIds = $user->Client->Organization->pluck('organization_id')->toArray();
            if (!in_array($org_id, $organizationIds)) {
                return response()->json([
                    'messages' => 'Unauthenticated'
                ], 401);
            }


            $request->merge(['organization_id' => $org_id]);
            $validator = Validator::make($request->all(), [
                'organization_id' => 'required|integer|exists:organizations,organization_id',
            ]);
            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()], 422);
            }
            $employeesaddress = EmployeeAddress::all();
            $employeesaddress->load('addressType','ownershipType','employee','city');
            return response()->json([
                'message' => ' Employees Address',
                'employeesaddress' => $employeesaddress
            ], 200);

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
                    'messages' => 'Unauthenticated'
                ], 401);
            }


            $request->merge(['organization_id' => $org_id]);
            $validator = Validator::make($request->all(), [
                'organization_id' => 'required|integer|exists:organizations,organization_id',
                'employee_id' => 'required|integer|exists:employees,employee_id',
                'organization_employee_address_type_id' => 'required|integer|exists:organization_employee_address_types,organization_employee_address_type_id',
                'organization_employee_residential_ownership_type_id' => 'required|integer|exists:organization_employee_residential_ownership_types,organization_employee_residential_ownership_type_id',
                'address_line1' => 'required|string|max:100',
                'address_line2' => 'nullable|string|max:100',
                'address_line3' => 'nullable|string|max:100',
                'general_city_id' => 'required|integer|exists:general_cities,general_city_id',
                'postal_code' => 'required|string|max:20',
            ]);
            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()], 422);
            }
            $data = $request->all();
            $employeeaddress = EmployeeAddress::create(array_merge($data));
            return response()->json([
                'message' => 'Employees Address  Added SuccessFully.',
                'employeeaddress' => $employeeaddress
            ], 201);
        } catch (\Exception $e) {
            // Handle any other errors and return a response
            return response()->json([
                'error' => 'Something went wrong. Please try again later.',
                'details' => $e->getMessage()
            ], 500);
        }
    }


    public function show(Request $request, $org_id, $address_id)
    {
        try {

             $user = Auth::guard('applicationusers')->user();
             $organizationIds = $user->Client->Organization->pluck('organization_id')->toArray();
            if (!in_array($org_id, $organizationIds)) {
                return response()->json([
                    'messages' => 'Unauthenticated'
                ], 401);
            }
            $request->merge(['organization_id' => $org_id,'employee_address_id' => $address_id]);
            $validator = Validator::make($request->all(), [
                'organization_id' => 'required|integer|exists:organizations,organization_id',
             
                'employee_address_id' => 'required|integer|exists:employee_addresses,employee_address_id',
            ]);
            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()], 422);
            }
            $employeaddress = EmployeeAddress::find($address_id);
               $employeaddress->load('addressType','ownershipType','employee','city');
            return response()->json([
                'message' => 'Organization Employee Address Found',
                'employeaddress' => $employeaddress
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Something went wrong. Please try again later.',
                'details' => $e->getMessage()
            ], 500);
        }
    }

    public function update(Request $request, $org_id, $address_id)
    {
        try {

             $user = Auth::guard('applicationusers')->user();
             $organizationIds = $user->Client->Organization->pluck('organization_id')->toArray();
            if (!in_array($org_id, $organizationIds)) {
                return response()->json([
                    'messages' => 'Unauthenticated'
                ], 401);
            }
            $request->merge([
                'organization_id' => $org_id,
                'employee_address_id' => $address_id
            ]);
            $rules = [
                'organization_id' => 'required|integer|exists:organizations,organization_id',
                'employee_id' => 'nullable|integer|exists:employees,employee_id',
                'organization_employee_address_type_id' => 'nullable|integer|exists:organization_employee_address_types,organization_employee_address_type_id',
                'organization_employee_residential_ownership_type_id' => 'nullable|integer|exists:organization_employee_residential_ownership_types,organization_employee_residential_ownership_type_id',
                'employee_address_id' => 'nullable|integer|exists:employee_addresses,employee_address_id',
                'address_line1' => 'nullable|string|max:100',
                'address_line2' => 'nullable|nullable|string|max:100',
                'address_line3' => 'nullable|nullable|string|max:100',
                'general_city_id' => 'required|integer|exists:general_cities,general_city_id',
                'postal_code' => 'nullable|string|max:20',
            ];
            $validator = Validator::make($request->all(), $rules);
            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()], 422);
            }
            $employeeaddress = EmployeeAddress::find($address_id);
            $employeeaddress->update($request->only([
                'address_line1',
                'address_line2',
                'address_line3',
                'postal_code',
                'general_city_id',
                'organization_employee_residential_ownership_type_id',
                'organization_employee_address_type_id',
                'employee_id'



            ]));

            return response()->json([
                'message' => 'Employee Address  updated successfully.',
                'employeeaddress' => $employeeaddress
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Something went wrong. Please try again later.',
                'details' => $e->getMessage()
            ], 500);
        }
    }


    public function destroy(Request $request, $org_id, $address_id)
    {
        try {

             $user = Auth::guard('applicationusers')->user();
             $organizationIds = $user->Client->Organization->pluck('organization_id')->toArray();
            if (!in_array($org_id, $organizationIds)) {
                return response()->json([
                    'messages' => 'Unauthenticated'
                ], 401);
            }
            $request->merge(['organization_id' => $org_id, 'employee_address_id' => $address_id]);
            $validator = Validator::make($request->all(), [
                'organization_id' => 'required|integer|exists:organizations,organization_id',
                'employee_address_id' => 'sometimes|integer|exists:employee_addresses,employee_address_id',
            ]);
            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()], 422);
            }
            $employeaddress = EmployeeAddress::find($address_id);
            $employeaddress->delete();
            return response()->json([
                'message' => 'Employee Address Deleted Successfully'
            ], 200);


        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Something went wrong. Please try again later.',
                'details' => $e->getMessage()
            ], 500);
        }
    }




}
