<?php

namespace App\Http\Controllers\OrganizationController;

use App\Models\OrganizationModel\OrganizationMaster;
use Illuminate\Support\Facades\Validator;
use App\Http\Controllers\Controller;
use Illuminate\Validation\ValidationException;
use App\Models\OrganizationModel\PersonnelMaster;
use Illuminate\Http\Request;
use Exception;

class PersonnelMasterController extends Controller
{

    
    public function index(Request $request,$org_id, $department_id)
    {
        try {

            $request->merge(['org_id'=>$org_id,'department_id'=>$department_id]);
            $personnel_type_id = $request->input('personnel_type_id');
            
            $validator = Validator::make($request->all(), [
                'org_id' => 'required|integer|exists:organization_master,organization_id',
                'department_id'=>'required|integer|exists:organization_departments,department_id',
                'personnel_type_id' => 'required|integer|exists:personnel_types,personnel_type_id',   
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'error' => 'Validation failed',
                    'messages' => $validator->errors()
                ], 422);
            }

             $personalmaster = PersonnelMaster::find($org_id);
             $personalprofile = $personalmaster->personalData;
     
            return response()->json([
                'status' => 'success',
                'PersonalMster' => $personalprofile
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Something went wrong. Please try again later.',
                'details' => $e->getMessage()
            ], 500);
        }
    }

    
    public function store(Request $request,$org_id, $department_id)
    {
        try {
            

            $request->merge(['org_id'=>$org_id,'department_id'=>$department_id]);
            $personnel_type_id = $request->input('personnel_type_id');
          
            $rules = [
                'org_id' => 'required|integer|exists:organization_master,organization_id',
                'department_id'=>'required|integer|exists:organization_departments,department_id',
                'personnel_type_id' => 'required|integer|exists:personnel_types,personnel_type_id',
                'reporting_manager_id' => 'nullable|integer',
                'first_name' => 'nullable|string|max:50',
                'middle_name' => 'nullable|string|max:50',
                'last_name' => 'nullable|string|max:50',
                'date_of_birth' => 'nullable|date',
                'gender' => 'nullable|in:Male,Female,Other',
                'marital_status' => 'nullable|in:Single,Married,Divorced,Widowed',
                'disability_status' => 'required|boolean',
                'profile_image_url' => 'nullable|string|max:255',
                'designation_id' => 'nullable|string|max:50',
                'date_of_joining' => 'nullable|date',
                'employment_status' => 'nullable|in:Full-Time,Part-Time',
                'work_mode' => 'nullable|in:Onsite,Remote,Hybrid',
                'is_active' => 'required|boolean'
                
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
            $data = $request->only(['reporting_manager_id', 'first_name', 'middle_name', 'last_name','date_of_birth','gender',
             'marital_status','disability_status','profile_image_url','designation_id','date_of_joining','employment_status','work_mode','is_active']);
            
             $data['organization_id'] = $org_id;
             $data['department_id'] = $department_id;
             $data['personnel_type_id'] = $personnel_type_id; 
            
            $personnelmaster = PersonnelMaster::create($data);
    
            return response()->json([
                'message' => 'Personnelconatct Details created successfully.',
                'data' => $personnelmaster
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
    

   
    public function show(Request $request,$org_id, $department_id,$id)
    {
        try {

          
            $request->merge(['org_id'=>$org_id,'department_id'=>$department_id,'id'=>$id]);
            $personnel_type_id = $request->input('personnel_type_id');

            $request->validate([
                'org_id' => 'required|integer|exists:organization_master,organization_id',
                'department_id'=>'required|integer|exists:organization_departments,department_id',
                'personnel_type_id' => 'required|integer|exists:personnel_types,personnel_type_id',
                'id' => 'required|integer|exists:personnel_master,id',
              
            ]);
    
            // Fetch the user
            $personnelconatct = PersonnelMaster::findOrFail($id);
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
    

    public function destroy(Request $request ,$org_id, $department_id,$id)
    {
        try{ 

           
            $request->merge(['org_id'=>$org_id,'department_id'=>$department_id,'id'=>$id]); 
            $personnel_type_id = $request->input('personnel_type_id'); 
           
            $request->validate([
                'org_id' => 'required|integer|exists:organization_master,organization_id',
                'department_id'=>'required|integer|exists:organization_departments,department_id',
                'personnel_type_id' => 'required|integer|exists:personnel_types,personnel_type_id',
                'id' => 'required|integer|exists:personnel_master,id',
              
            ]);
        
                $personelcontact = PersonnelMaster::findOrFail($id);
                $personelcontact->delete();
            
                return response()->json([
                    'message' => '  Employee Details deleted successfully.'
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


    
    public function update(Request $request,$org_id, $department_id,$id)
    {
        try{   
              
            $request->merge(['org_id'=>$org_id,'department_id'=>$department_id,'id'=>$id]);
            $personnel_type_id = $request->input('personnel_type_id');

            $rules = [
                'org_id' => 'required|integer|exists:organization_master,organization_id',
                'department_id'=>'required|integer|exists:organization_departments,department_id',
                'personnel_type_id' => 'required|integer|exists:personnel_types,personnel_type_id',
                'id' => 'required|integer|exists:personnel_master,id',
                'reporting_manager_id' => 'sometimes|nullable|integer',
                'first_name' => 'sometimes|nullable|string|max:50',
                'middle_name' => 'sometimes|nullable|string|max:50',
                'last_name' => 'sometimes|nullable|string|max:50',
                'date_of_birth' => 'sometimes|nullable|date',
                'gender' => 'sometimes|nullable|in:Male,Female,Other',
                'marital_status' => 'sometimes|nullable|in:Single,Married,Divorced,Widowed',
                'disability_status' => 'sometimes|boolean',
                'profile_image_url' => 'sometimes|nullable|string|max:255',
                'designation_id' => 'sometimes|nullable|string|max:50',
                'date_of_joining' => 'sometimes|nullable|date',
                'employment_status' => 'sometimes|nullable|in:Full-Time,Part-Time',
                'work_mode' => 'sometimes|nullable|in:Onsite,Remote,Hybrid',
                'is_active' => 'sometimes|boolean'
               
            ];


            // Run validation
            $validator = Validator::make($request->all(), $rules);

            if ($validator->fails()) {
                return response()->json([
                    'error' => 'Validation failed',
                    'message' => $validator->errors()
                ], 422);
            }


            $personnelconatct = PersonnelMaster::find($id);

            $personnelconatct->update($request->only([
              
                'reporting_manager_id',
                'first_name',
                'middle_name',
                'last_name',
                'date_of_birth',
                'gender',
                'marital_status',
                'disability_status',
                'profile_image_url',
                'designation_id',
                'date_of_joining',
                'employment_status',
                'work_mode',
                'is_active'
        
            ]));
        
            return response()->json([
                'message' => 'Personnel conatct Details updated successfully.',
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



