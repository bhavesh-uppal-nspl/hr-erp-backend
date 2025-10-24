<?php

namespace App\Http\Controllers\GeneralController;

use App\Models\GeneralModel\GeneralCountry;
use App\Models\GeneralModel\GeneralState;
use Illuminate\Support\Facades\Validator;
use App\Http\Controllers\Controller;
use App\Models\GeneralModel\GeneralCities;
use Illuminate\Http\Request;

class GeneralCityController extends Controller
{


      public function indexV2($general_country_id, $general_state_id)
    {
      
        try {
            $cities = GeneralCities::where('general_country_id', $general_country_id)
                ->where('general_state_id', $general_state_id)
                ->get();

            return response()->json(['data' => $cities], 200);
        } catch (\Exception $e) {
            \Log::error('Error fetching cities: ' . $e->getMessage());
            return response()->json(['message' => 'Failed to fetch cities'], 500);
        }
    }



    public function index(Request $request)
    {
        try {
         
            $cities = GeneralCities::all();
            $cities->load('state','country');
            return response()->json([
                'message' => 'All General Cities',
                'cities' => $cities
            ], 200);

            // Fetch documents for the specified registration

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
   
            $validator = Validator::make($request->all(), [
                'general_state_id' => 'required|integer|exists:general_states,general_state_id',
                'general_country_id' => 'required|integer|exists:general_countries,general_country_id',
                'city_name' => 'required|string|max:60',
                'city_latitude' => 'required|numeric|between:-90,90',
                'city_longitude' => 'required|numeric|between:-180,180',
            ]);

            // Return validation errors if any
            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()], 422);
            }
            $data = $request->all();

            $cities = GeneralCities::create( $data);
            return response()->json([
                'message' => 'City Added SuccessFully.',
                'city' => $cities
            ], 201);

        } catch (\Exception $e) {
            // Handle any other errors and return a response
            return response()->json([
                'error' => 'Something went wrong. Please try again later.',
                'details' => $e->getMessage()
            ], 500);
        }
    }

    public function show(Request $request, $general_city_id)
    {
        try {
            // Merge org_id from route into request for validation
            $request->merge(['general_city_id' => $general_city_id]);

            // Validate the org_id
            $validator = Validator::make($request->all(), [
                // 'general_state_id' => 'required|integer|exists:general_states,general_state_id',
                'general_city_id' => 'required|integer|exists:general_cities,general_city_id',
                // 'general_country_id' => 'required|integer|exists:general_countries,general_country_id',
            ]);
            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()], 422);
            }
            $cities = GeneralCities::find($general_city_id);
            return response()->json([

                'message' => 'City',
                'city' => $cities
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Something went wrong. Please try again later.',
                'details' => $e->getMessage()
            ], 500);
        }
    }

    public function update(Request $request, $general_city_id)
    {
        try {
            $request->merge(['general_city_id' => $general_city_id]);

            // Validation rules including existence checks
            $rules = [
                'general_state_id' => 'sometimes|integer|exists:general_states,general_state_id',
                'general_city_id' => 'required|integer|exists:general_cities,general_city_id',
                'general_country_id' => 'sometimes|integer|exists:general_countries,general_country_id',
                'city_name' => 'sometimes|string|max:60',
                'city_latitude' => 'sometimes|numeric|between:-90,90',
                'city_longitude' => 'sometimes|numeric|between:-180,180',
            ];

            // Run validation
            $validator = Validator::make($request->all(), $rules);
            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()], 422);
            }
            // Find the location
            $cities = GeneralCities::find($general_city_id);
            $cities->update($request->only(keys: [
                'city_name',
                'city_latitude',
                'city_longitude',
                'general_state_id',
                'general_country_id',
            ]));

            return response()->json([
                'message' => 'General city updated successfully.',
                'city' => $cities
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Something went wrong. Please try again later.',
                'details' => $e->getMessage()
            ], 500);
        }
    }

    public function destroy(Request $request, $general_state_id, $general_country_id, $general_city_id)
    {
        try {
            $request->merge(['general_state_id' => $general_state_id, 'general_city_id' => $general_city_id, 'general_country_id' => $general_country_id]);
            $validator = Validator::make($request->all(), [
                'general_state_id' => 'required|integer|exists:general_states,general_state_id',
                'general_city_id' => 'required|integer|exists:general_cities,general_city_id',
                'general_country_id' => 'required|integer|exists:general_countries,general_country_id',
            ]);
            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()], 422);
            }
            $city = GeneralCities::findOrFail($general_city_id);
            $city->delete();

            return response()->json([
                'message' => 'City deleted successfully.'
            ], 200);

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'error' => 'City not found with the provided ID.'
            ], 404);

        } catch (\Illuminate\Database\QueryException $e) {
            // Handle foreign key constraint violation
            if ($e->getCode() == 23000) {
                return response()->json([
                    'error' => 'Cannot delete this city because it is linked with other records.'
                ], 409); // Conflict
            }

            return response()->json([
                'error' => 'Database error: ' . $e->getMessage()
            ], 500);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Something went wrong. Please try again later.',
                'details' => $e->getMessage()
            ], 500);
        }
    }



public function indexV1(Request $request)
{
    try {
        $query = $request->input('search');

        $cities = GeneralCities::when($query, function ($q) use ($query) {
            return $q->where('city_name', 'LIKE', '%' . $query . '%');
        })->limit(10)->get(); 

        return response()->json([
            'message' => 'Filtered General Cities',
            'cities' => $cities
        ], 200);

    } catch (\Exception $e) {
        return response()->json([
            'error' => 'Something went wrong. Please try again later.',
            'details' => $e->getMessage()
        ], 500);
    }
}

}
