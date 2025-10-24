<?php

namespace App\Http\Controllers\OrganizationController;

use App\Models\OrganizationModel\ApplicationOrganizationAcive;
use App\Models\OrganizationModel\Organization;
use Auth;
use Illuminate\Support\Facades\Validator;
use App\Http\Controllers\Controller;
use App\Models\OrganizationModel\OrganizationBusinessDivision;

use Exception;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class OrganizationBusinessDivisionController extends Controller
{


    public function update(Request $request, $org_id, $application_user_id)
    {

        try {

            $user = Auth::guard('applicationusers')->user();
            $organizationIds = $user->Client->Organization->pluck('organization_id')->toArray();
            if (!in_array($org_id, $organizationIds)) {
                return response()->json([
                    'messages' => 'Unauthenticated'
                ], 401);
            }

            // Merge org_id from route into request for validation
            $request->merge(['organization_id' => $org_id, "application_user_id" => $application_user_id]);
            $validator = Validator::make($request->all(), [
                'organization_id' => 'required|integer|exists:organizations,organization_id',
                'application_user_id' => 'required|integer|exists:application_users,application_user_id',
            ]);
            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()], 422);
            }

            $record = ApplicationOrganizationAcive::where('organization_id', $org_id)
                ->where('application_user_uid', $application_user_id)
                ->first();



            return response()->json([
                'message' => 'Organization Active',
                'businessDivision' => $record
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Something went wrong. Please try again later.',
                'details' => $e->getMessage()
            ], 500);
        }

    }




}
