<?php

namespace App\Http\Controllers\OrganizationController;

use Illuminate\Support\Facades\Validator;
use App\Http\Controllers\Controller;
use Illuminate\Validation\ValidationException;
use App\Models\OrganizationModel\PersonnelEmploymentStatus;
use Illuminate\Http\Request;
use Exception;

class PersonnelEmploymentStatusController extends Controller
{

    
    public function index()
    {
        try {
           $employestatus = PersonnelEmploymentStatus::all();
            return response()->json([
                'status' => 'success',
                'employestatus' =>$employestatus
            ], 200);
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
              
            $rules = [
                'status_name' => 'required|string|max:50',
                'status_description' => 'required|string',
            ];
    
            // Validate the request
            $validator = Validator::make($request->all(), $rules);
    
            if ($validator->fails()) {
                return response()->json([
                    'error' => 'Validation failed',
                    'messages' => $validator->errors()
                ], 422);
            }
    
            $employeestatus = $request->only(['status_name', 'status_description']);
            $personnelEmployeestatus = PersonnelEmploymentStatus::create( $employeestatus);
    
            return response()->json([
                'message' => 'personnel Employee Status created successfully.',
                'personnelEmployeestatus' => $personnelEmployeestatus
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
    

   
    public function show(Request $request, $empoyment_status_id)
    {
        try {

            $request->merge(['empoyment_status_id' => $empoyment_status_id]);
            $request->validate([
               
                'empoyment_status_id' => 'required|exists:personnel_employee_status,empoyment_status_id',
               
            ]);
            $personnel_status = PersonnelEmploymentStatus::findOrFail($empoyment_status_id);
            return response()->json($personnel_status, 200);
    
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
    

   
    public function destroy(Request $request , $empoyment_status_id)
    {
        try{   
            $request->merge(['empoyment_status_id' => $empoyment_status_id]);
                $request->validate([
                  'empoyment_status_id' => 'required|exists:personnel_employee_status,empoyment_status_id',
                ]);   

        
                $personnelemployeestatus = PersonnelEmploymentStatus::findOrFail($empoyment_status_id);
                $personnelemployeestatus->delete();
            
                return response()->json([
                    'message' => '  Personnel Employee Status deleted successfully.'
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


    
    public function update(Request $request,$empoyment_status_id)
    {
        try{   
            $request->merge(['empoyment_status_id' => $empoyment_status_id]);
            $rules = [
              
                'empoyment_status_id' => 'required|exists:personnel_employee_status,empoyment_status_id',
                'status_name' => 'sometimes|string|max:50',
                'status_description' => 'sometimes|string',
               
            ];
            // Run validation
            $validator = Validator::make($request->all(), $rules);

            if ($validator->fails()) {
                return response()->json([
                    'error' => 'Validation failed',
                    'message' => $validator->errors()
                ], 422);
            }


            $personnelemployeestatus = PersonnelEmploymentStatus::find($empoyment_status_id);

            $personnelemployeestatus->update($request->only([  
               'status_name',
               'status_description',
            ]));
        
            return response()->json([
                'message' => 'Personnel Employement status  updated successfully.',
                'personnelemployeestatus' => $personnelemployeestatus
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



