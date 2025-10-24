<?php

namespace App\Http\Controllers\OrganizationController;

use Illuminate\Support\Facades\Validator;
use App\Http\Controllers\Controller;
use App\Models\OrganizationModel\Organization;
use App\Models\OrganizationModel\OrganizationEmployementType;
use Exception;
use Illuminate\Http\Request;
use Auth;
use Illuminate\Validation\Rule;

class OrganizationEmpTypeController extends Controller
{

    public function index(Request $request, $org_id)
    {
        try {
            $user = Auth::guard('applicationusers')->user();
            $organizationIds = $user->Client->Organization->pluck('organization_id')->toArray();
            if (!in_array($org_id, $organizationIds)) {
                return response()->json([
                    'messages' => 'unauthorized'
                ], 401);

            }
            $request->merge(['organization_id' => $org_id]);
            $validator = Validator::make($request->all(), [
                'organization_id' => 'required|integer|exists:organizations,organization_id',
            ]);
            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()], 422);
            }


            $query = OrganizationEmployementType::with('organization')->where('organization_id', $org_id);
            $per = $request->input('per_page', 10);
            $search = $request->input('search');
            if ($search) {
                $query->where(function ($q) use ($search) {
                    $q->where('employment_type_name', 'like', "%{$search}%");
                });
            }
            $data = $query->orderBy('created_at', 'desc')->paginate($per);
            return response()->json([
                'message' => 'OK',
                'employemtType' => $data,

            ]);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Something went wrong. Please try again later.',
                'details' => $e->getMessage()
            ], 500);
        }

    }


    public function store(Request $request, $org_id)
{
    try {
        $user = Auth::guard('applicationusers')->user();
        $organizationIds = $user->Client->Organization->pluck('organization_id')->toArray();

        if (!in_array($org_id, $organizationIds)) {
            return response()->json([
                'messages' => 'unauthorized'
            ], 401);
        }

        $request->merge(['organization_id' => $org_id]);

        $validator = Validator::make($request->all(), [
            'organization_id' => 'required|integer|exists:organizations,organization_id',
            'employment_type_name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('organization_employment_types', 'employment_type_name')
                    ->where('organization_id', $org_id)
            ]
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $data = $request->all();
        $employmentType = OrganizationEmployementType::create($data);

        return response()->json([
            'message' => 'Organization employmentType Added Successfully.',
            'employmentType' => $employmentType
        ], 201);

    } catch (\Exception $e) {
        return response()->json([
            'error' => 'Something went wrong. Please try again later.',
            'details' => $e->getMessage()
        ], 500);
    }
}




    // display specific organization 
    public function show(Request $request, $org_id, $employment_type_id)
    {
        try {
            $user = Auth::guard('applicationusers')->user();
            $organizationIds = $user->Client->Organization->pluck('organization_id')->toArray();
            if (!in_array($org_id, $organizationIds)) {
                return response()->json([
                    'messages' => 'unauthorized'
                ], 401);

            }
            $request->merge(['organization_id' => $org_id, 'organization_employment_type_id' => $employment_type_id]);
            $validator = Validator::make($request->all(), [
                'organization_id' => 'required|integer|exists:organizations,organization_id',
                'organization_employment_type_id' => 'required|integer|exists:organization_employment_types,organization_employment_type_id',
            ]);
            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()], 422);
            }
            $employmentTpe = OrganizationEmployementType::find($employment_type_id);
            return response()->json([
                'message' => "Orgaization Employment type Found",
                'employmentTpe' => $employmentTpe
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Something went wrong. Please try again later.',
                'details' => $e->getMessage()
            ], 500);
        }
    }

    // update the orgaization 
    public function update(Request $request, $org_id, $employment_type_id)
    {
        try {
            $user = Auth::guard('applicationusers')->user();
            $organizationIds =
                $user->Client->Organization->pluck('organization_id')->toArray();
            if (!in_array($org_id, $organizationIds)) {
                return response()->json([
                    'messages' => 'unauthorized'
                ], 401);

            }
            $request->merge([
                'organization_id' => $org_id,
                'organization_employment_type_id' => $employment_type_id
            ]);
            $rules = [
                'organization_id' =>
                    'required|integer|exists:organizations,organization_id',
                'organization_employment_type_id' =>
                    'required|integer|exists:organization_employment_types,organization_employment_type_id',


                'employment_type_name' => [
                    'sometimes',
                    'string',
                    'max:255',
                    Rule::unique('organization_employment_types', 'employment_type_name')
                        ->where('organization_id', $org_id)
                        ->ignore($employment_type_id, 'organization_employment_type_id')
                ],
            ];
            $validator = Validator::make($request->all(), $rules);
            if ($validator->fails()) {
                return response()->json([
                    'errors' =>
                        $validator->errors()
                ], 422);
            }
            $employmentTpe =
                OrganizationEmployementType::find($employment_type_id);
            $employmentTpe->update($request->only([
                'employment_type_name'
            ]));
            return response()->json([
                'message' => 'Organization  Employment Type updated
successfully.',
                'employmentTpe' => $employmentTpe
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Something went wrong. Please try again later.',
                'details' => $e->getMessage()
            ], 500);
        }
    }



    // delete the orgaization  
    public function destroy(Request $request, $org_id, $employment_type_id)
    {
        try {
            $user = Auth::guard('applicationusers')->user();
            $organizationIds = $user->Client->Organization->pluck('organization_id')->toArray();
            if (!in_array($org_id, $organizationIds)) {
                return response()->json([
                    'messages' => 'unauthorized'
                ], 401);

            }
            $request->merge([
                'organization_id' => $org_id,
                'organization_employment_type_id' => $employment_type_id
            ]);
            $validator = Validator::make($request->all(), [
                'organization_id' => 'required|integer|exists:organizations,organization_id',
                'organization_employment_type_id' => 'required|integer|exists:organization_employment_types,organization_employment_type_id',
            ]);
            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()], 422);
            }
            $employmentType = OrganizationEmployementType::find($employment_type_id);
            $employmentType->delete();
            return response()->json([
                'message' => 'Organization Employment Type  Deleted Successfully'
            ], 200); // or just remove 200 — it's the default
        } catch (Exception $e) {
            if ($e instanceof \Illuminate\Database\QueryException && $e->errorInfo[1] == 1451) {
                // Foreign key constraint violation
                return response()->json([
                    'error' => 'Cannot delete Business Unit type because it is linked with other records. Please delete dependent records first.'
                ], 409); // 409 Conflict
            }

            // For other exceptions
            return response()->json([
                'error' => 'Failed to delete ownership type.',
                'exception' => $e->getMessage() // Optional: remove in production
            ], 500);
        }
    }
}
