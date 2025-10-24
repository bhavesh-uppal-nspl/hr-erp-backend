<?php

namespace App\Http\Controllers\InternController;

use App\Models\InterModel\IntershipTypes;
use Auth;
use Illuminate\Support\Facades\Validator;
use App\Http\Controllers\Controller;
use App\Models\OrganizationModel\Organization;
use App\Models\OrganizationModel\OrganizationBusinessUnit;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class InternshipTypeController extends Controller
{
    public function index(Request $request, $org_id)
    {
        try {
            $request->merge(['organization_id' => $org_id]);
            $validator = Validator::make($request->all(), [
                'organization_id' => 'required|integer|exists:organizations,organization_id',
            ]);
            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()], 422);
            }

            if ($request->input('mode') == 1) {
                $type = IntershipTypes::where('organization_id', $org_id)->get();

                if ($type->isEmpty()) {
                    return response()->json([
                        'message' => 'Internship Type not found.'
                    ], 404);
                }
                $mappedShiftType = $type->map(function ($dep) {
                    return [
                        'internship_type_name'=>$dep->internship_type_name,
                       
                    ];
                });
                return response()->json($mappedShiftType);
            }

            $query = IntershipTypes::where('organization_id', $org_id);
            $per = $request->input('per_page', 10);
            $search = $request->input('search');
            if ($search) {
                $query->where(function ($q) use ($search) {
                    $q->where('internship_type_name', 'like', "%{$search}%");
                });
            }
            $data = $query->orderBy('created_at', 'desc')->paginate($per);
            return response()->json([
                'message' => 'OK',
                'intership' => $data,
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
                    'message' => 'Unauthorized'
                ], 401);
            }

            $request->merge(['organization_id' => $org_id]);

            $validator = Validator::make($request->all(), [
                'organization_id' => 'required|integer|exists:organizations,organization_id',

                'organization_entity_id' => 'nullable|integer|exists:organization_entities,organization_entity_id',
                'internship_type_name' => [
                    'sometimes',
                    'nullable',
                    'string',
                    'max:255',
                    Rule::unique('organization_internship_types', 'internship_type_name')
                        ->where(function ($query) use ($org_id) {
                            return $query->where('organization_id', $org_id);
                        }),
                ],
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'errors' => $validator->errors()
                ], 422);
            }

            $data = $request->all();
            $intership = IntershipTypes::create($data);
            return response()->json([
                'message' => 'Organization Intership Added Successfully.',
                'intership' => $intership
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Something went wrong. Please try again later.',
                'details' => $e->getMessage()
            ], 500);
        }
    }

    public function show(Request $request, $org_id, $intern_id)
    {
        try {
            $user = Auth::guard('applicationusers')->user();
            $organizationIds = $user->Client->Organization->pluck('organization_id')->toArray();
            if (!in_array($org_id, $organizationIds)) {
                return response()->json([
                    'messages' => 'unauthorized'
                ], 401);

            }
            $request->merge(['organization_id' => $org_id, 'organization_internship_type_id' => $intern_id]);
            $validator = Validator::make($request->all(), [
                'organization_id' => 'required|integer|exists:organizations,organization_id',
                'organization_internship_type_id' => 'required|integer|exists:organization_internship_types,organization_internship_type_id',
            ]);
            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()], 422);
            }
            $intership = IntershipTypes::find($intern_id);
            return response()->json([
                'message' => "Orgaization Business Unit",
                'intership' => $intership
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Something went wrong. Please try again later.',
                'details' => $e->getMessage()
            ], 500);
        }
    }

    public function update(Request $request, $org_id, $intern_id)
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
                'organization_internship_type_id' => $intern_id
            ]);
            $rules = [
                'organization_id' => 'required|integer|exists:organizations,organization_id',
                'organization_internship_type_id' => 'required|integer|exists:organization_internship_types,organization_internship_type_id',
                'internship_type_name' => [
                    'sometimes',
                    'string',
                    'max:255',
                    Rule::unique('organization_internship_types', 'internship_type_name')
                        ->ignore($intern_id, 'organization_internship_type_id')
                        ->where(function ($query) use ($request) {
                            return $query->where('organization_id', $request->organization_id);
                        }),
                ],


            ];
            $validator = Validator::make($request->all(), $rules);
            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()], 422);
            }
            $intership = IntershipTypes::find($intern_id);
            $intership->update($request->only([
                'internship_type_name',
            ]));
            return response()->json([
                'intership' => $intership
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Something went wrong. Please try again later.',
                'details' => $e->getMessage()
            ], 500);
        }
    }

    public function destroy(Request $request, $org_id, $intern_id)
    {
        try {
            $user = Auth::guard('applicationusers')->user();
            $organizationIds = $user->Client->Organization->pluck('organization_id')->toArray();
            if (!in_array($org_id, $organizationIds)) {
                return response()->json([
                    'messages' => 'unauthorized'
                ], 401);
            }
            $request->merge(['organization_id' => $org_id, 'organization_internship_type_id' => $intern_id]);
            $validator = Validator::make($request->all(), [
                'organization_id' => 'required|integer|exists:organizations,organization_id',
                'organization_internship_type_id' => 'required|integer|exists:organization_internship_types,organization_internship_type_id',
            ]);
            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()], 422);
            }
            $intership = IntershipTypes::find($intern_id);
            $intership->delete();
            return response()->json([
                'message' => 'Organization Intership Type Deleted SuccessFully !'
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
