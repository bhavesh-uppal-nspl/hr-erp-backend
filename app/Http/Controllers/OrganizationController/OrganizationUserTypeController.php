<?php

namespace App\Http\Controllers\OrganizationController;
use App\Models\OrganizationModel\OrganizationUnitTypes;
use App\Models\OrganizationModel\OrganizationUserType;
use Illuminate\Support\Facades\Validator;
use App\Http\Controllers\Controller;
use App\Models\OrganizationModel\Organization;
use Exception;
use Illuminate\Http\Request;
use Auth;
use Illuminate\Validation\Rule;

class OrganizationUserTypeController extends Controller
{

    public function index(Request $request, $org_id, )
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
            
            $query = OrganizationUserType::where('organization_id', $org_id);
            $per = $request->input('per_page', 10);
            $search = $request->input('search');
            if ($search) {
                $query->where(function ($q) use ($search) {
                    $q->where('user_type_name', 'like', "%{$search}%")
                        ->orWhere('user_type_code', 'like', "%{$search}%")
                        ->orWhere('description', 'like', "%{$search}%");
                });
            }
            $data = $query->orderBy('created_at', 'desc')->paginate($per);
            return response()->json([
                'message' => 'OK',
                'userTypes' => $data,

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
                    'messages' => 'Unauthorized'
                ], 401);
            }

            $request->merge(['organization_id' => $org_id]);
            $validator = Validator::make($request->all(), [
                'organization_id' => 'required|integer|exists:organizations,organization_id',
                'user_type_name' => [
                    'nullable',
                    'string',
                    'max:50',
                    Rule::unique('organization_user_types')->where(function ($query) use ($request) {
                        return $query->where('organization_id', $request->organization_id);
                    }),
                ],
                'user_type_code' => [
                    'nullable',
                    'string',
                    'max:50',
                    Rule::unique('organization_user_types')->where(function ($query) use ($request) {
                        return $query->where('organization_id', $request->organization_id);
                    }),
                ],
                'description' => 'nullable|string|max:500',
            ]);
            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()], 422);
            }
            $data = $request->all();
            $usertypes = OrganizationUserType::create($data);
            return response()->json([
                'message' => 'Organization User Type added successfully.',
                'usertypes' => $usertypes
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Something went wrong. Please try again later.',
                'details' => $e->getMessage()
            ], 500);
        }
    }

    public function show(Request $request, $org_id, $user_type_id)
    {
        try {
            $user = Auth::guard('applicationusers')->user();
            $organizationIds = $user->Client->Organization->pluck('organization_id')->toArray();
            if (!in_array($org_id, $organizationIds)) {
                return response()->json([
                    'messages' => 'unauthorized'
                ], 401);
            }
            // Merge org_id from route into request for validation
            $request->merge(['organization_id' => $org_id, 'organization_user_type_id' => $user_type_id]);
            $validator = Validator::make($request->all(), [
                'organization_id' => 'required|integer|exists:organizations,organization_id',
                'organization_user_type_id' => 'required|integer|exists:organization_user_types,organization_user_type_id',
            ]);

            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()], 422);
            }
            $userTypes = OrganizationUserType::find($user_type_id);
            return response()->json([
                'message' => "Orgaization user types  found",
                'userTypes' => $userTypes
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Something went wrong. Please try again later.',
                'details' => $e->getMessage()
            ], 500);
        }
    }



    public function update(Request $request, $org_id, $user_type_id)
    {
        try {
            $user = Auth::guard('applicationusers')->user();
            $organizationIds = $user->Client->Organization->pluck('organization_id')->toArray();
            if (!in_array($org_id, $organizationIds)) {
                return response()->json([
                    'messages' => 'unauthorized'
                ], 401);

            }
            $request->merge(['organization_id' => $org_id, 'organization_user_type_id' => $user_type_id]);
            $rules = [
                'organization_id' => 'required|integer|exists:organizations,organization_id',
                'organization_user_type_id' => 'required|integer|exists:organization_user_types,organization_user_type_id',

                'user_type_name' => [
                    'nullable',
                    'string',
                    'max:50',
                    Rule::unique('organization_user_types', 'user_type_name')
                        ->where(function ($query) use ($org_id) {
                            return $query->where('organization_id', $org_id);
                        })
                        ->ignore($user_type_id, 'organization_user_type_id'),
                ],
                'user_type_code' => [
                    'nullable',
                    'string',
                    'max:50',
                    Rule::unique('organization_user_types', 'user_type_code')
                        ->where(function ($query) use ($org_id) {
                            return $query->where('organization_id', $org_id);
                        })
                      ->ignore($user_type_id, 'organization_user_type_id'),
                ],
            ];

            // Run validation
            $validator = Validator::make($request->all(), $rules);

            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()], 422);
            }
            $usertype = OrganizationUserType::find($user_type_id);
            $usertype->update($request->only([
                'user_type_code',
                'user_type_name',
                'description'
            ]));

            return response()->json([
                'message' => 'Organization user type updated successfully.',
                'usertype' => $usertype
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Something went wrong. Please try again later.',
                'details' => $e->getMessage()
            ], 500);
        }
    }

    public function destroy(Request $request, $org_id, $user_type_id)
    {
        try {
            $user = Auth::guard('applicationusers')->user();
            $organizationIds = $user->Client->Organization->pluck('organization_id')->toArray();
            if (!in_array($org_id, $organizationIds)) {
                return response()->json([
                    'messages' => 'unauthorized'
                ], 401);

            }
            $request->merge(['organization_id' => $org_id, 'organization_user_type_id' => $user_type_id]);
            $validator = Validator::make($request->all(), [
                'organization_id' => 'required|integer|exists:organizations,organization_id',
                'organization_user_type_id' => 'required|integer|exists:organization_user_types,organization_user_type_id',
            ]);
            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()], 422);
            }
            $unittype = OrganizationUserType::find($user_type_id);
            $unittype->delete();
            return response()->json([
                'message' => 'Organization User type Deleted Successfully'
            ], 200); // or just remove 200 — it's the default


        } catch (Exception $e) {
            if ($e instanceof \Illuminate\Database\QueryException && $e->errorInfo[1] == 1451) {
                // Foreign key constraint violation
                return response()->json([
                    'error' => 'Cannot delete unit type because it is linked with other records. Please delete dependent records first.'
                ], 409); // 409 Conflict
            }

            // For other exceptions
            return response()->json([
                'error' => 'Failed to delete Address Type type.',
                'exception' => $e->getMessage() // Optional: remove in production
            ], 500);
        }
    }
}


