<?php

namespace App\Http\Controllers\OrganizationController;

use App\Models\OrganizationModel\OrganizationLanguages;
use Illuminate\Support\Facades\Validator;
use App\Http\Controllers\Controller;
use App\Models\OrganizationModel\Organization;
use App\Models\OrganizationModel\OrganizationLeaveCategory;
use Exception;
use Illuminate\Http\Request;
use Auth;
use Illuminate\Validation\Rule;

class OrganizationLanguageController extends Controller
{


    public function index(Request $request, $org_id)
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
            ]);
            if ($validator->fails()) {
                return response()->json([
                    'errors' =>
                        $validator->errors()
                ], 422);
            }


            $query = OrganizationLanguages::where('organization_id', $org_id);
            $per = $request->input('per_page', 10);
            $search = $request->input('search');
            if ($search) {
                $query->where(function ($q) use ($search) {
                    $q->where('language_name', 'like', "%{$search}%")
                        ->orWhere('language_code', 'like', "%{$search}%")
                        ->orWhere('description', 'like', "%{$search}%");

                });
            }
            $data = $query->orderBy('created_at', 'desc')->paginate($per);
            return response()->json([
                'message' => 'OK',
                'Languages' => $data,

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
                'language_name' => [
                    'nullable',
                    'string',
                    'max:10',
                    Rule::unique('organization_languages', 'language_name')
                        ->where('organization_id', $org_id)
                ],
                'language_code' => [
                    'required',
                    'string',
                    'max:255',
                    Rule::unique('organization_languages', 'language_code')
                        ->where('organization_id', $org_id)
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
            $languages = OrganizationLanguages::create($data);
            return response()->json([
                'message' => 'Organization Language Added SuccessFully.',
                'orglanguages' => $languages
            ], 201);

        } catch (\Exception $e) {
            // Handle any other errors and return a response
            return response()->json([
                'error' => 'Something went wrong. Please try again later.',
                'details' => $e->getMessage()
            ], 500);
        }
    }

    public function show(Request $request, $org_id, $language_id)
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
                'organization_language_id' => $language_id
            ]);
            $validator = Validator::make($request->all(), [
                'organization_id' =>
                    'required|integer|exists:organizations,organization_id',
                'organization_language_id' =>
                    'required|integer|exists:organization_languages,organization_language_id',
            ]);
            if ($validator->fails()) {
                return response()->json([
                    'errors' =>
                        $validator->errors()
                ], 422);
            }
            $languages = OrganizationLanguages::find($language_id);
            return response()->json([
                'message' => 'Organization Language  Found',
                'orglanguages' => $languages
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Something went wrong. Please try again later.',
                'details' => $e->getMessage()
            ], 500);
        }
    }

    public function update(Request $request, $org_id, $language_id)
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
                'organization_language_id' => $language_id
            ]);

            $rules = [
                'organization_id' =>
                    'required|integer|exists:organizations,organization_id',

                'organization_language_id' =>
                    'required|integer|exists:organization_languages,organization_language_id',

                'language_name' => [
                    'sometimes',
                    'string',
                    'max:255',
                    Rule::unique(
                        'organization_languages',
                        'language_name'
                    )->ignore($language_id, 'organization_language_id')
                ],
                'language_code' => [
                    'sometimes',
                    'nullable',
                    'string',
                    'max:255',
                    Rule::unique(
                        'organization_languages',
                        'language_code'
                    )->ignore($language_id, 'organization_language_id')
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
            $leavecategory = OrganizationLanguages::find($language_id);
            $leavecategory->update($request->only([
                'language_code',
                'language_name',
                'description',
            ]));

            return response()->json([
                'message' => 'Organization Languages updated successfully.',
                'orgLanguage' => $leavecategory
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Something went wrong. Please try again later.',
                'details' => $e->getMessage()
            ], 500);
        }
    }


    public function destroy(Request $request, $org_id, $language_id)
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
                'organization_language_id' => $language_id
            ]);
            $validator = Validator::make($request->all(), [
                'organization_id' =>
                    'required|integer|exists:organizations,organization_id',
                'organization_language_id' =>
                    'required|integer|exists:organization_languages,organization_language_id',
            ]);
            if ($validator->fails()) {
                return response()->json([
                    'errors' =>
                        $validator->errors()
                ], 422);
            }
            $leavecategory = OrganizationLanguages::find($language_id);
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


