<?php

namespace App\Http\Controllers\OrganizationController;

use Illuminate\Support\Facades\Validator;
use App\Http\Controllers\Controller;
use Illuminate\Validation\ValidationException;
use App\Models\OrganizationModel\PersonnelType;
use Illuminate\Http\Request;
use Exception;

class PersonnelTypeController extends Controller
{

    
    public function index()
    {
        try {

           $personnelType = PersonnelType::all();
            return response()->json([
                'status' => 'success',
                'personnelType' =>$personnelType
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
                'type_name' => 'required|string|max:50',
                'is_active' => 'nullable|boolean'
            ];
    
            // Validate the request
            $validator = Validator::make($request->all(), $rules);
    
            if ($validator->fails()) {
                return response()->json([
                    'error' => 'Validation failed',
                    'messages' => $validator->errors()
                ], 422);
            }
    
            $data = $request->only(['type_name', 'is_active']);
            $personnelType = PersonnelType::create($data);
    
            return response()->json([
                'message' => 'personnel Type  created successfully.',
                'data' => $personnelType
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
    

   
    public function show(Request $request, $personnel_type_id)
    {
        try {

            $request->merge(['personnel_type_id' => $personnel_type_id]);
            $request->validate([
               
                'personnel_type_id' => 'required|exists:personnel_types,personnel_type_id',
               
            ]);
            $personnel_type = PersonnelType::findOrFail($personnel_type_id);
            return response()->json($personnel_type, 200);
    
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
    




   
    public function destroy(Request $request , $personnel_type_id)
    {
        try{   
            $request->merge(['personnel_type_id' => $personnel_type_id]); 
                $request->validate([
                   'personnel_type_id' => 'required|exists:personnel_types,personnel_type_id',
                ]);   

        
                $personneltype = PersonnelType::findOrFail($personnel_type_id);
                $personneltype->delete();
            
                return response()->json([
                    'message' => '  Personnel Type deleted successfully.'
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


    
    public function update(Request $request,$personnel_type_id)
    {
        try{   
            $request->merge(['personnel_type_id' => $personnel_type_id]);
            $rules = [
              
                'personnel_type_id' => 'required|exists:personnel_types,personnel_type_id',
                'type_name' => 'sometimes|string|max:50',
                'is_active' => 'sometimes|nullable|boolean'
               
            ];


            // Run validation
            $validator = Validator::make($request->all(), $rules);

            if ($validator->fails()) {
                return response()->json([
                    'error' => 'Validation failed',
                    'message' => $validator->errors()
                ], 422);
            }


            $personneltype = PersonnelType::find($personnel_type_id);

            $personneltype->update($request->only([  
                'type_name', 
                'is_active'
            ]));
        
            return response()->json([
                'message' => 'Personnel Type  updated successfully.',
                'personnelType' => $personneltype
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



