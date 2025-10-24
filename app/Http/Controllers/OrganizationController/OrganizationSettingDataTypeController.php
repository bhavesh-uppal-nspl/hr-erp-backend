<?php

namespace App\Http\Controllers\GeneralController;
use App\Models\OrganizationModel\OrganizationSettingDataType;
use Illuminate\Support\Facades\Validator;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class OrganizationSettingDataTypeController extends Controller
{
    public function index()
    {
        try {
            $generalsetting = OrganizationSettingDataType::all();
            return response()->json([
                'message' => 'All  organization data types Types',
                'settingtypes' => $generalsetting
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Something went wrong. Please try again later.'
            ], 500);
        }
    }


    public function store(Request $request, $org_id)
    {
        try {
            $validator = Validator::make($request->all(), [
                'setting_data_type_name' => [
                    'required',
                    'string',
                    'max:255',
                    function ($attribute, $value, $fail) use ($org_id) {
                        $exists = OrganizationSettingDataType::where('organization_id', $org_id)
                            ->where('setting_data_type_name', $value)
                            ->exists();
                        if ($exists) {
                            $fail('setting data  type name already exists.');
                        }
                    }
                ],
                'description' => 'nullable|string|max:255'
            ]);
            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()], 422);
            }
            $data = $request->all();
            $unittypes = OrganizationSettingDataType::create($data);

            return response()->json([
                'message' => 'Organization setting data type added successfully.',
                'unittypes' => $unittypes
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Something went wrong. Please try again later.',
                'details' => $e->getMessage()
            ], 500);
        }
    }



}
