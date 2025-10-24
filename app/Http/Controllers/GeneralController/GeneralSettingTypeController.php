<?php

namespace App\Http\Controllers\GeneralController;

use App\Models\GeneralModel\GeneralSettingType;
use Illuminate\Support\Facades\Validator;
use App\Http\Controllers\Controller;
use App\Models\GeneralModel\GeneralSettingDataType;
use Illuminate\Http\Request;

class GeneralSettingTypeController extends Controller
{
    public function index()
    {
        try {
            $generalsetting = GeneralSettingType::all();
            return response()->json([
                'message' => 'All General Settings Types',
                'generalsettingtypes' => $generalsetting
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Something went wrong. Please try again later.'
            ], 500);
        }
    }

    public function store(Request $request)
    {
        try {
            $request->validate([
                'setting_data_type_name' => 'required|string|max:25|unique:general_setting_data_types,setting_data_type_name',
                'description' => 'required|string|max:2000'
            ]);
            $generalsetting = GeneralSettingDataType::create([
                'setting_data_type_name' => $request->setting_data_type_name,
                'description' => $request->description,
            ]);
            return response()->json([
                'message' => 'General Data Type Addeed SucessFully',
                'generalsetting' => $generalsetting
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

    public function show(Request $request, $data_type_id)
    {
        try {
            $request->merge(['general_setting_data_type_id' => $data_type_id]);
            $validator = Validator::make($request->all(), [
                'general_setting_data_type_id' => 'required|integer|exists:general_setting_data_types,general_setting_data_type_id',
            ]);
            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()], 422);
            }
            $generalsetting = GeneralSettingDataType::findOrFail($data_type_id);
            return response()->json([
                'message' => 'General Data Type Found',
                'generalsetting' => $generalsetting
            ], 200);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'error' => 'Data Type  not found with the provided ID.'
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Something went wrong. Please try again later.'
            ], 500);
        }

    }

    public function update(Request $request, $data_type_id)
    {
        try {
            $request->merge(['general_setting_data_type_id' => $data_type_id]);
            $validator = Validator::make($request->all(), [
                'general_setting_data_type_id' => 'required|integer|exists:general_setting_data_types,general_setting_data_type_id',
                'setting_data_type_name' => 'sometimes|string|max:25|unique:general_setting_data_types,setting_data_type_name',
                'description' => 'sometimes|string|max:2000'
            ]);
            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()], 422);
            }
            $generalsetting = GeneralSettingDataType::findOrFail($data_type_id);
            $generalsetting->update($request->all());
            return response()->json([
                'message' => 'Data Type Updated SucessFully',
                'generalsetting' => $generalsetting
            ], 200);

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'error' => 'Data Type  not found with the provided ID.'
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Something went wrong. Please try again later.'
            ], 500);
        }
    }

    public function destroy(Request $request, $data_type_id)
    {
        try {
         $request->merge(['general_setting_data_type_id' => $data_type_id]);
            $validator = Validator::make($request->all(), [
                   'general_setting_data_type_id' => 'required|integer|exists:general_setting_data_types,general_setting_data_type_id',
            ]);
            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()], 422);
            }
            $generalsetting = GeneralSettingDataType::findOrFail($data_type_id);
            $generalsetting->delete();
            return response()->json(['message' => 'Data Type deleted successfully'], 200);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {

            return response()->json([
                'error' => 'Leave Duration not found with the provided ID.'
            ], 404);
        }
    }
}
