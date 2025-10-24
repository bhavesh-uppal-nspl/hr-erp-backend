<?php

namespace App\Http\Controllers\EmployeeController;
use App\Models\EmployeesModel\EmployeeContact;
use App\Models\OrganizationModel\Organization;
use Illuminate\Support\Facades\Validator;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class EmployeeContactController extends Controller
{
    public function index(Request $request, $org_id)
    {
        try {
            $request->merge(['organization_id' => $org_id]);
            $validator = Validator::make($request->all(), [
                'organization_id' => 'required|integer|exists:organizations,organization_id',
            ]);
            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()], 422);
            }
            $employeecontacts = Organization::find($org_id)->employeecontact;
            $employeecontacts->load('employee.designation');
            return response()->json([
                'message' => ' Employees Contact Details',
                'employeecontacts' => $employeecontacts
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
            $request->merge(['organization_id' => $org_id]);
            $validator = Validator::make($request->all(), [
                'organization_id' => 'required|integer|exists:organizations,organization_id',
                'employee_id' => 'required|integer|exists:employees,employee_id',
                'personal_phone_number' => 'nullable|string|max:20',
                'alternate_personal_phone_number' => 'nullable|string|max:20',
                'personal_email' => 'nullable|email|max:100',
                'alternate_personal_email' => 'nullable|email|max:100',
                'preferred_contact_method' => 'nullable|in:Email,Phone,Message',

                'emergency_person_phone_number_1' => 'nullable|string|max:20',
                'emergency_person_name_1' => 'required|string|max:50',
                'emergency_person_relation_1' => 'required|in:Parent,Spouse,Sibling,Child,Friend,Other',

                'emergency_person_phone_number_2' => 'nullable|string|max:45',
                'emergency_person_name_2' => 'nullable|string|max:45',
                'emergency_person_relation_2' => 'nullable|in:Parent,Spouse,Sibling,Child,Friend,Other',

                'work_phone_number' => 'nullable|string|max:20',
                'work_email' => 'nullable|email|max:100', // <-- main rule
            ]);
            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()], 422);
            }
            $data = $request->all();
            $employeecontact = EmployeeContact::create(array_merge($data));
            return response()->json([
                'message' => 'Employees Contact Added SuccessFully.',
                'employeecontact' => $employeecontact
            ], 201);
        } catch (\Exception $e) {
            // Handle any other errors and return a response
            return response()->json([
                'error' => 'Something went wrong. Please try again later.',
                'details' => $e->getMessage()
            ], 500);
        }
    }

    public function show(Request $request, $org_id, $contact_id)
    {
        try {
            $request->merge(['organization_id' => $org_id, 'employee_contact_id' => $contact_id]);
            $validator = Validator::make($request->all(), [
                'organization_id' => 'required|integer|exists:organizations,organization_id',

                'employee_contact_id' => 'required|integer|exists:employee_contacts,employee_contact_id',
            ]);
            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()], 422);
            }
            $employeecontact = EmployeeContact::find($contact_id);
            $employeecontact->load('employee.designation');
            return response()->json([
                'message' => 'Employee Contact Found',
                'employeecontact' => $employeecontact
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Something went wrong. Please try again later.',
                'details' => $e->getMessage()
            ], 500);
        }
    }

    public function update(Request $request, $org_id, $contact_id)
    {
        try {
            $request->merge([
                'organization_id' => $org_id,

                'employee_contact_id' => $contact_id
            ]);
            $rules = [
                'organization_id' => 'required|integer|exists:organizations,organization_id',
                'employee_id' => 'sometimes|integer|exists:employees,employee_id',
                'employee_contact_id' => 'required|integer|exists:employee_contacts,employee_contact_id',
                'personal_phone_no' => 'sometimes|string|max:20',
                'alternate_personal_phone_no' => 'sometimes|nullable|string|max:20',
                'personal_email' => 'sometimes|nullable|email|max:100',
                'alternate_personal_email' => 'sometimes|nullable|email|max:100',
                'preferred_contact_method' => 'sometimes|nullable|in:Email,Phone,Message',
                'emergency_person_phone_no_1' => 'sometimes|string|max:20',
                'emergency_person_name_1' => 'sometimes|string|max:50',
                'emergency_person_relation_1' => 'sometimes|in:Parent,Spouse,Inlaws,Sibling,Child,Friend,Other',
                'emergency_person_phone_no_2' => 'sometimes|nullable|string|max:45',
                'emergency_person_name_2' => 'sometimes|nullable|string|max:45',
                'emergency_person_relation_2' => 'sometimes|nullable|in:Parent,Inlaws,Spouse,Sibling,Child,Friend,Other',
                'work_phone_no' => 'sometimes|nullable|string|max:20',
                'work_email' => 'sometimes|nullable|email|max:100',
            ];
            $validator = Validator::make($request->all(), $rules);
            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()], 422);
            }
            $employeecontact = EmployeeContact::find($contact_id);
            $employeecontact->update($request->only([
                'personal_phone_no',
                'alternate_personal_phone_no',
                'personal_email',
                'alternate_personal_email',
                'preferred_contact_method',
                'emergency_person_phone_no_1',
                'emergency_person_name_1',
                'emergency_person_relation_1',
                'emergency_person_phone_no_2',
                'emergency_person_name_2',
                'emergency_person_relation_2',
                'work_phone_no',
                'work_email',
                'employee_id'
            ]));

            return response()->json([
                'message' => 'Employee Contact  Updated Successfully.',
                'employeecontact' => $employeecontact
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Something went wrong. Please try again later.',
                'details' => $e->getMessage()
            ], 500);
        }
    }




 
    public function destroy(Request $request, $org_id, $contact_id)
    {
        try {
            $request->merge([
                'organization_id' => $org_id,

                'employee_contact_id' => $contact_id
            ]);
            $validator = Validator::make($request->all(), [
                'organization_id' => 'required|integer|exists:organizations,organization_id',

                'employee_contact_id' => 'sometimes|integer|exists:employee_contacts,employee_contact_id',
            ]);
            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()], 422);
            }
            $employeecontact = EmployeeContact::find($contact_id);
            $employeecontact->delete();
            return response()->json([
                'message' => 'Employee Contact Deleted Successfully'
            ], 200);


        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Something went wrong. Please try again later.',
                'details' => $e->getMessage()
            ], 500);
        }
    }




}
