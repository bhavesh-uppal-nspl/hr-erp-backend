<?php

namespace App\Http\Controllers\OrganizationController;

use Illuminate\Support\Facades\Validator;
use App\Http\Controllers\Controller;
use App\Models\OrganizationModel\Organization;
use App\Models\OrganizationModel\OrganizationLeaveCategory;
use Exception;
use Illuminate\Http\Request;
use Auth;
use Illuminate\Validation\Rule;

class OrganizationLeaveCategoryController extends Controller
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


            $query = OrganizationLeaveCategory::where('organization_id', $org_id);
            $per = $request->input('per_page', 10);
            $search = $request->input('search');
            if ($search) {
                $query->where(function ($q) use ($search) {
                    $q->where('leave_category_name ', 'like', "%{$search}%")
                        ->orWhere('leave_category_code', 'like', "%{$search}%")
                        ->orWhere('description', 'like', "%{$search}%");

                });
            }
            $data = $query->orderBy('created_at', 'desc')->paginate($per);
            return response()->json([
                'message' => 'OK',
                'leavecategory' => $data,

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
            $organizationIds =
                $user->Client->Organization->pluck('organization_id')->toArray();
            if (!in_array($org_id, $organizationIds)) {
                return response()->json([
                    'messages' => 'unauthorized'
                ], 401);

            }
            $request->merge(['organization_id' => $org_id]);
            $validator = Validator::make($request->all(), [
                'organization_id' =>
                    'required|integer|exists:organizations,organization_id',



                'leave_category_name' => [
                    'sometimes',
                    'nullable',
                    'string',
                    'max:255',
                    Rule::unique(
                        'organization_leave_categories',
                        'leave_category_name'
                    )
                        ->where(function ($query) use ($org_id) {
                            return $query->where('organization_id', $org_id);
                        }),
                ],


                'leave_category_code' => [
                    'sometimes',
                    'nullable',
                    'string',
                    'max:50',
                    Rule::unique(
                        'organization_leave_categories',
                        'leave_category_code'
                    )
                        ->where(function ($query) use ($org_id) {
                            return $query->where('organization_id', $org_id);
                        }),
                ],



                'description' => ['nullable', 'string', 'max:255'],
            ]);
            if ($validator->fails()) {
                return response()->json([
                    'errors' =>
                        $validator->errors()
                ], 422);
            }
            $data = $request->all();
            $leavecategory = OrganizationLeaveCategory::create($data);
            return response()->json([
                'message' => 'Organization Levae Category Added SuccessFully.',
                'leavecategory' => $leavecategory
            ], 201);

        } catch (\Exception $e) {
            // Handle any other errors and return a response
            return response()->json([
                'error' => 'Something went wrong. Please try again later.',
                'details' => $e->getMessage()
            ], 500);
        }
    }

    public function show(Request $request, $org_id, $leave_category_id)
    {
        try {
            $user = Auth::guard('applicationusers')->user();
            $organizationIds = $user->Client->Organization->pluck('organization_id')->toArray();
            if (!in_array($org_id, $organizationIds)) {
                return response()->json([
                    'messages' => 'unauthorized'
                ], 401);

            }
            $request->merge(['organization_id' => $org_id, 'organization_leave_category_id' => $leave_category_id]);
            $validator = Validator::make($request->all(), [
                'organization_id' => 'required|integer|exists:organizations,organization_id',
                'organization_leave_category_id' => 'required|integer|exists:organization_leave_categories,organization_leave_category_id',
            ]);
            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()], 422);
            }
            $leavecategory = OrganizationLeaveCategory::find($leave_category_id);
            return response()->json([
                'message' => 'Organization Leave Category Found',
                'leavecategory' => $leavecategory
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Something went wrong. Please try again later.',
                'details' => $e->getMessage()
            ], 500);
        }
    }

    public function update(Request $request, $org_id, $leave_category_id)
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
                'organization_leave_category_id' => $leave_category_id
            ]);
            $rules = [
                'organization_id' =>
                    'required|integer|exists:organizations,organization_id',
                'organization_leave_category_id' =>
                    'required|integer|exists:organization_leave_categories,organization_leave_category_id',

                'leave_category_name' => [
                    'sometimes',
                    'string',
                    'max:255',
                    Rule::unique(
                        'organization_leave_categories',
                        'leave_category_name'
                    )
                        ->ignore(
                            $leave_category_id,
                            'organization_leave_category_id'
                        )
                        ->where(function ($query) use ($request) {
                            return $query->where(
                                'organization_id',
                                $request->organization_id
                            );
                        }),
                ],

                'leave_category_code' => [
                    'sometimes',
                    'string',
                    'max:100',
                    Rule::unique(
                        'organization_leave_categories',
                        'leave_category_code'
                    )
                        ->ignore(
                            $leave_category_id,
                            'organization_leave_category_id'
                        )
                        ->where(function ($query) use ($request) {
                            return $query->where(
                                'organization_id',
                                $request->organization_id
                            );
                        }),
                ],
                'description' => ['sometimes', 'nullable', 'string', 'max:255'],
            ];
            $validator = Validator::make($request->all(), $rules);
            if ($validator->fails()) {
                return response()->json([
                    'errors' =>
                        $validator->errors()
                ], 422);
            }
            $leavecategory =
                OrganizationLeaveCategory::find($leave_category_id);
            $leavecategory->update($request->only([
                'leave_category_code',
                'leave_category_name',
                'description',
            ]));

            return response()->json([
                'message' => 'Organization Leave Category updated
successfully.',
                'leavecategory' => $leavecategory
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Something went wrong. Please try again later.',
                'details' => $e->getMessage()
            ], 500);
        }
    }


    public function destroy(Request $request, $org_id, $leave_category_id)
    {
        try {
            $user = Auth::guard('applicationusers')->user();
            $organizationIds = $user->Client->Organization->pluck('organization_id')->toArray();
            if (!in_array($org_id, $organizationIds)) {
                return response()->json([
                    'messages' => 'unauthorized'
                ], 401);

            }
            $request->merge(['organization_id' => $org_id, 'organization_leave_category_id' => $leave_category_id]);
            $validator = Validator::make($request->all(), [
                'organization_id' => 'required|integer|exists:organizations,organization_id',
                'organization_leave_category_id' => 'required|integer|exists:organization_leave_categories,organization_leave_category_id',
            ]);
            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()], 422);
            }
            $leavecategory = OrganizationLeaveCategory::find($leave_category_id);
            $leavecategory->delete();
            return response()->json([
                'message' => 'Organization Leave Category Deleted Successfully'
            ], 200); // or just remove 200 — it's the default


        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Something went wrong. Please try again later.',
                'details' => $e->getMessage()
            ], 500);
        }
    }
}
