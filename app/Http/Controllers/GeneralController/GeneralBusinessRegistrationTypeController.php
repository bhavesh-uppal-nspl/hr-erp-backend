<?php

namespace App\Http\Controllers\GeneralController;
use Illuminate\Support\Facades\Validator;
use Illuminate\Database\QueryException;
use App\Http\Controllers\Controller;
use App\Models\GeneralModel\GeneralBusinessRegistrationType;
use Illuminate\Http\Request;



class GeneralBusinessRegistrationTypeController extends Controller
{

    
    public function index(Request $request)
    {
        try { 
            $businessregtype = GeneralBusinessRegistrationType::all();
            return response()->json([
                'message'=>'General Business Registration Types',
                'businessRegistrationtype' => $businessregtype
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
            // Validate inputs
            $validator = Validator::make($request->all(), [
                 'business_registration_type_name' => 'required|string|max:60',
                 'business_registration_type_short_name' => 'required|string|max:10',
            ]);

            // Return validation errors if any
            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()], 422);
            }
            $data = $request->all();
            $businessregistrationtype = GeneralBusinessRegistrationType::create(array_merge($data));
            return response()->json([
                'message' => 'Added SuccessFully.',
                'BusinessRegistrationType' => $businessregistrationtype
            ], 201);

        } catch (\Exception $e) {
            // Handle any other errors and return a response
            return response()->json([
                'error' => 'Something went wrong. Please try again later.',
                'details' => $e->getMessage()
            ], 500);
        }
    }
 
    public function show(Request $request, $business_reg_type_id)
    {
        try {
            $request->merge(['general_business_registration_type_id' =>$business_reg_type_id]);
            $validator = Validator::make($request->all(), [
                'general_business_registration_type_id' => 'required|integer|exists:general_business_registration_types,general_business_registration_type_id',

            ]);

            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()], 422);
            }
            $businessregistrationtype = GeneralBusinessRegistrationType::find($business_reg_type_id);
            return response()->json([
                'message'=>"General Business Registration Type",
                'BusinessRegistrationType' => $businessregistrationtype
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Something went wrong. Please try again later.',
                'details' => $e->getMessage()
            ], 500);
        }
    }

    public function update(Request $request,$business_reg_type_id)
    {
        try {
            $request->merge([
                'general_business_registration_type_id' => $business_reg_type_id
            ]);
            $rules = [
                'general_business_registration_type_id' => 'required|integer|exists:general_business_registration_types,general_business_registration_type_id',
                'business_registration_type_name' => 'sometimes|string|max:60',
                'business_registration_type_short_name' => 'sometimes|string|max:10',
            ];

            // Run validation
            $validator = Validator::make($request->all(), $rules);

            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()], 422);
            }
        
            // Find the location
            $businessregistrationType = GeneralBusinessRegistrationType::find($business_reg_type_id);
            $businessregistrationType->update($request->only([
               'business_registration_type_name',
                'business_registration_type_short_name',
            ]));

            return response()->json([
                'message' => 'General Business Registration Type updated successfully.',
                'BusinessRegistrationType' => $businessregistrationType
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Something went wrong. Please try again later.',
                'details' => $e->getMessage()
            ], 500);
        }
    }

    public function destroy(Request $request, $business_reg_type_id)
    {
        try {
            // Merge route parameters into request for validation
            $request->merge([
              'general_business_registration_type_id' => $business_reg_type_id
            ]);
    
            // Validate the input
            $validator = Validator::make($request->all(), [
                'general_business_registration_type_id' => 'required|integer|exists:general_business_registration_types,general_business_registration_type_id',
            ]);
            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()], 422);
            }        
            $businessRegsitrationType = GeneralBusinessRegistrationType::find($business_reg_type_id);
            $businessRegsitrationType->delete();
    
            return response()->json([
                'message' => 'Business Regsitration Deleted Successfully.'
            ], 200);
    
        } catch (QueryException $e) {
            // Foreign key constraint violation
            if ($e->getCode() === '23000' && str_contains($e->getMessage(), '1451')) {
                return response()->json([
                    'error' => 'Cannot delete this Business registration type because it is linked to organization records.'
                ], 409); // 409 Conflict
            }
    
            return response()->json([
                'error' => 'A database error occurred.',
                'details' => $e->getMessage()
            ], 500);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Something went wrong. Please try again later.',
                'details' => $e->getMessage()
            ], 500);
        }
    }
}
