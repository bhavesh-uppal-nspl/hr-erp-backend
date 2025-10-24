<?php

namespace App\Http\Controllers\OrganizationController;

use App\Models\OrganizationModel\OrganizationSetting;
use Illuminate\Support\Facades\Validator;
use App\Http\Controllers\Controller;
use App\Models\OrganizationModel\Organization;
use App\Models\OrganizationModel\OrganizationSettingType;
use Exception;
use Illuminate\Http\Request;
use Auth;
use Illuminate\Validation\Rule;

class OrganizationSettingTypeController extends Controller
{


    public function index(Request $request)
    {
        try {
            $query = OrganizationSettingType::query();

            $per = $request->input('per_page', 10);
            $search = $request->input('search');

            if ($search) {
                $query->where(function ($q) use ($search) {
                    $q->where('work_shift_type_name', 'like', "%{$search}%")
                        ->orWhere('work_shift_type_short_name', 'like', "%{$search}%");
                });
            }

            $data = $query->orderBy('created_at', 'desc')->paginate($per);

            return response()->json([
                'message' => 'OK',
                'settingtypes' => $data,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Something went wrong. Please try again later.',
                'details' => $e->getMessage()
            ], 500);
        }
    }

}
