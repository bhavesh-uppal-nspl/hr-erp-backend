<?php

namespace App\Http\Controllers\GeneralController;

use App\Models\GeneralModel\GeneralCountry;
use Illuminate\Support\Facades\Validator;
use Illuminate\Database\QueryException;
use App\Http\Controllers\Controller;
use App\Models\GeneralModel\GeneralState;
use Illuminate\Http\Request;

class GeneralStateController extends Controller
{



    public function indexV1(Request $request, $general_country_id)
    {
        try {
            $request->merge(['general_country_id' => $general_country_id]);
            $validator = Validator::make($request->all(), [
                'general_country_id' => 'required|integer|exists:general_countries,general_country_id',
            ]);
            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()], 422);
            }
            $generalstates = GeneralCountry::with('states')->find($general_country_id)->states;
            return response()->json([
                'message' => 'General States',
                'data' => $generalstates
            ], 200);

            // Fetch documents for the specified registration

        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Something went wrong. Please try again later.',
                'details' => $e->getMessage()
            ], 500);
        }

    }


    public function index(Request $request, $general_country_id)
    {
        try {
            $request->merge(['general_country_id' => $general_country_id]);
            $validator = Validator::make($request->all(), [
                'general_country_id' => 'required|integer|exists:general_countries,general_country_id',
            ]);
            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()], 422);
            }
            $generalstates = GeneralCountry::with('states.cities')->find($general_country_id)->states;
            return response()->json([
                'message' => 'General States',
                'generalstates' => $generalstates
            ], 200);

            // Fetch documents for the specified registration

        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Something went wrong. Please try again later.',
                'details' => $e->getMessage()
            ], 500);
        }

    }

    public function store(Request $request, $general_country_id)
    {
        try {
            // Merge org_id and reg_id into the request for validation
            $request->merge(['general_country_id' => $general_country_id]);
            // Validate inputs
            $validator = Validator::make($request->all(), [
                'general_country_id' => 'required|integer|exists:general_countries,general_country_id',
                'state_name' => 'required|string|max:60',
                'state_code' => 'required|string|max:20',
            ]);
            // Return validation errors if any
            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()], 422);
            }
            $data = $request->all();
            $generalstate = GeneralState::create(array_merge(['general_country_id' => $general_country_id], $data));
            return response()->json([
                'message' => 'state Added SuccessFully.',
                'generalstate' => $generalstate
            ], 201);

        } catch (\Exception $e) {
            // Handle any other errors and return a response
            return response()->json([
                'error' => 'Something went wrong. Please try again later.',
                'details' => $e->getMessage()
            ], 500);
        }
    }
    public function show(Request $request, $general_country_id, $general_state_id)
    {
        try {
            // Merge org_id from route into request for validation
            $request->merge(['general_country_id' => $general_country_id, 'general_state_id' => $general_state_id]);
            // Validate the org_id
            $validator = Validator::make($request->all(), [
                'general_country_id' => 'required|integer|exists:general_countries,general_country_id',
                'general_state_id' => 'required|integer|exists:general_states,general_state_id',

            ]);
            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()], 422);
            }
            $generalstate = GeneralState::find($general_state_id);
            $generalstate->load('cities');
            return response()->json([

                'message' => 'General State',
                'generalstate' => $generalstate
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Something went wrong. Please try again later.',
                'details' => $e->getMessage()
            ], 500);
        }
    }


    public function update(Request $request, $general_country_id, $general_state_id)
    {
        try {
            // Merge route parameters into the request for validation
            $request->merge(['general_country_id' => $general_country_id, 'general_state_id' => $general_state_id]);
            // Validation rules including existence checks
            $rules = [
                'general_country_id' => 'required|integer|exists:general_countries,general_country_id',
                'general_state_id' => 'required|integer|exists:general_states,general_state_id',
                'state_name' => 'sometimes|string|max:60',
                'state_code' => 'sometimes|string|max:20',
            ];
            // Run validation
            $validator = Validator::make($request->all(), $rules);
            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()], 422);
            }
            $generalstate = GeneralState::find($general_state_id);
            $generalstate->update($request->only([
                'state_name',
                'state_code'
            ]));
            return response()->json([
                'message' => 'General State  updated successfully.',
                'generalstate' => $generalstate
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Something went wrong. Please try again later.',
                'details' => $e->getMessage()
            ], 500);
        }
    }

    public function destroy(Request $request, $general_country_id, $general_state_id)
    {
        try {
            $request->merge(['general_country_id' => $general_country_id, 'general_state_id' => $general_state_id]);
            $rules = [
                'general_country_id' => 'required|integer|exists:general_countries,general_country_id',
                'general_state_id' => 'required|integer|exists:general_states,general_state_id',
            ];
            // Run validation
            $validator = Validator::make($request->all(), $rules);
            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()], 422);
            }

            $generalstate = GeneralState::find($general_state_id);
            $generalstate->delete();

            return response()->json([
                'message' => 'Deleted successfully.'
            ], 200);

        } catch (QueryException $e) {
            // Foreign key constraint violation
            if ($e->getCode() === '23000' && str_contains($e->getMessage(), '1451')) {
                return response()->json([
                    'error' => 'Cannot delete this registration type because it is linked to organization records.'
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
