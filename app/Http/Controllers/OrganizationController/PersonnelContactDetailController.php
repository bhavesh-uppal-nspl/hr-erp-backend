<?php

namespace App\Http\Controllers\OrganizationController;

use Illuminate\Support\Facades\Validator;
use App\Http\Controllers\Controller;
use Illuminate\Validation\ValidationException;
use App\Models\OrganizationModel\PersonnelContactDetail;
use Illuminate\Http\Request;
use Exception;

class PersonnelContactDetailController extends Controller
{

    
    public function index(Request $request, $personnel_type_id)
    {
        try {
            
            // Merge org_id from route into request for validation
            $request->merge(['personnel_id' => $personnel_type_id]);
            
            $validator = Validator::make($request->all(), [
               'personnel_id' => 'required|integer|exists:personnel_types,personnel_type_id',
                
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'error' => 'Validation failed',
                    'messages' => $validator->errors()
                ], 422);
            }

            $personnelcontact = PersonnelContactDetail::all();
            return response()->json([
                'status' => 'success',
                'personnelcontact' => $personnelcontact
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Something went wrong. Please try again later.',
                'details' => $e->getMessage()
            ], 500);
        }
    }

    
    public function store(Request $request, $personnel_type_id)
    {
        try {
            $request->merge(['personnel_id' => $personnel_type_id]);
    
            $rules = [
                'personnel_id' => 'required|integer|exists:personnel_types,personnel_type_id',
                'personal_email' => 'required|email|max:100',
                'work_email' => 'required|email|max:100',
                'personal_phone_no' => 'required|string|max:20',
                'alternate_personal_email' => 'required|nullable|email|max:100',
                'alternate_personal_phone_no' => 'required|nullable|string|max:20',
                'emergency_contact_name' => 'required|nullable|string|max:50',
                'emergency_personal_contact_phone_no' => 'required|required|string|max:20',
                'emergency_contact_relation' => 'required|nullable|in:Parent,Spouse,Sibling,Child,Friend', 
                'preferred_contact_method' => 'required|nullable|in:Email,Phone,Both',
            ];
    
            // Validate the request
            $validator = Validator::make($request->all(), $rules);
    
            if ($validator->fails()) {
                return response()->json([
                    'error' => 'Validation failed',
                    'messages' => $validator->errors()
                ], 422);
            }
    
            // Hash the password before saving
            $data = $request->only(['personal_email', 'work_email', 'personal_phone_no', 'alternate_personal_email','alternate_personal_phone_no','emergency_contact_name',
             'emergency_contact_relation','emergency_personal_contact_phone_no','preferred_contact_method']);
            
             $data['personnel_id'] = $personnel_type_id;  // Add role_id from the request input to the data
    
            // Create the user with hashed password
            $personnelconatct = PersonnelContactDetail::create($data);
    
            return response()->json([
                'message' => 'Personnelconatct Details created successfully.',
                'data' => $personnelconatct
            ], 201);
    
        } catch (\Illuminate\Database\QueryException $e) {
            return response()->json([
                'error' => 'Database error: ' . $e->getMessage()
            ], 500);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Something went wrong: ' . $e->getMessage()
            ], 500);
        }
    }
    

   
    public function show(Request $request, $personnel_type_id, $contact_id)
    {
        try {

            $request->merge(['personnel_id' => $personnel_type_id,'contact_id'=>$contact_id]);
           
            $request->validate([
              'personnel_id' => 'required|integer|exists:personnel_types,personnel_type_id',
                'contact_id' => 'required|exists:personnel_contact_details,contact_id',
              
            ]);
    
            // Fetch the user
            $personnelconatct = PersonnelContactDetail::findOrFail($contact_id);
            return response()->json($personnelconatct, 200);
    
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'error' => 'Validation failed',
                'messages' => $e->errors()
            ], 422);
    
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Something went wrong: ' . $e->getMessage()
            ], 500);
        }
    }
    




   
    public function destroy(Request $request ,$personnel_type_id, $contact_id)
    {
        try{   
            $request->merge(['personnel_id' => $personnel_type_id,'contact_id'=>$contact_id]);


                $request->validate([
                   'personnel_id' => 'required|integer|exists:personnel_types,personnel_type_id',
                    'contact_id' => 'required|exists:personnel_contact_details,contact_id',
                ]);   

        
                $personelcontact = PersonnelContactDetail::findOrFail($contact_id);
                $personelcontact->delete();
            
                return response()->json([
                    'message' => '  Personnel Details deleted successfully.'
                ], 200);
        
        } catch (\Illuminate\Validation\ValidationException $e) {
            // Handle validation errors
            return response()->json([
                'error' => 'Validation failed',
                'messages' => $e->errors()
            ], 422);
    
        } catch (\Exception $e) {
            // Catch any other exceptions
            return response()->json([
                'error' => 'Something went wrong: ' . $e->getMessage()
            ], 500);
        }
    }


    
    public function update(Request $request,$personnel_type_id, $contact_id)
    {
        try{   
            $request->merge(['personnel_id' => $personnel_type_id,'contact_id'=>$contact_id]);
            
            $rules = [
               'personnel_id' => 'required|integer|exists:personnel_types,personnel_type_id',
                'contact_id' => 'required|exists:personnel_contact_details,contact_id',
               
                'personal_email' => 'sometimes|email|max:100',
                'work_email' => 'sometimes|email|max:100',
                'personal_phone_no' => 'sometimes|string|max:20',
                'alternate_personal_email' => 'sometimes|nullable|email|max:100',
                'alternate_personal_phone_no' => 'sometimes|nullable|string|max:20',
                'emergency_contact_name' => 'sometimes|nullable|string|max:50',
                'emergency_personal_contact_phone_no' => 'sometimes|string|max:20',
                'emergency_contact_relation' => 'sometimes|nullable|in:Parent,Spouse,Sibling,Child,Friend', 
                'preferred_contact_method' => 'sometimes|nullable|in:Email,Phone,Both',
               
            ];


            // Run validation
            $validator = Validator::make($request->all(), $rules);

            if ($validator->fails()) {
                return response()->json([
                    'error' => 'Validation failed',
                    'message' => $validator->errors()
                ], 422);
            }


            $personnelconatct = PersonnelContactDetail::find($contact_id);

            $personnelconatct->update($request->only([
              
                'personal_email',
                'work_email',
                'personal_phone_no',
                'alternate_personal_email',
                'alternate_personal_phone_no',
                'emergency_contact_name',
                'emergency_personal_contact_phone_no',
                'emergency_contact_relation', 
                'preferred_contact_method',
        
            ]));
        
            return response()->json([
                'message' => 'Personnelconatct Details updated successfully.',
                'personnelconatct' => $personnelconatct
            ], 201);
                
                
               
        } catch (ValidationException $e) {
            return response()->json([
                'message' => 'Validation error',
                'errors' => $e->errors()
            ], 422); // 422 Unprocessable Entity
        } catch (Exception $e) {
            return response()->json([
                'message' => 'An error occurred while updating the role.',
                'error' => $e->getMessage()
            ], 500); // 500 Internal Server Error
        }
    }
    
}



