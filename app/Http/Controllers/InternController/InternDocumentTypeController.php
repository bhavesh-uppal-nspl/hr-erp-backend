<?php

namespace App\Http\Controllers\InternController;
use App\Models\InterModel\InternDocumentType;
use App\Models\InterModel\IntershipTypes;
use Auth;
use Illuminate\Support\Facades\Validator;
use App\Http\Controllers\Controller;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class InternDocumentTypeController extends Controller
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
                $document = InternDocumentType::where('organization_id', $org_id)->get();
                if ($document->isEmpty()) {
                    return response()->json([
                        'message' => 'Docuemnt Type not found.'
                    ], 404);
                }
                $mappedDocument = $document->map(function ($dep) {
                    return [
                        'document_type_name'=>$dep->document_type_name,
                        'document_type_short_name' => $dep->document_type_short_name ?? '',
                        'is_active' => $dep->is_active ?? '',
                    ];
                });
                return response()->json($mappedDocument);
            }
            $query = InternDocumentType::where('organization_id', $org_id);
            $per = $request->input('per_page', 10);
            $search = $request->input('search');
            if ($search) {
                $query->where(function ($q) use ($search) {
                    $q->where('document_type_name', 'like', "%{$search}%");
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
                'document_type_name' => [
                    'sometimes',
                    'nullable',
                    'string',
                    'max:255',
                    Rule::unique('intern_document_types', 'document_type_name')
                        ->where(function ($query) use ($org_id) {
                            return $query->where('organization_id', $org_id);
                        }),
                ],
                'document_type_short_name' => [
                    'sometimes',
                    'nullable',
                    'string',
                    'max:20',
                    Rule::unique('intern_document_types', 'document_type_short_name')
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
            $intership = InternDocumentType::create($data);
            return response()->json([
                'message' => 'Organization Intern Document Added Successfully.',
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
            $request->merge(['organization_id' => $org_id, 'intern_document_type_id' => $intern_id]);
            $validator = Validator::make($request->all(), [
                'organization_id' => 'required|integer|exists:organizations,organization_id',
                'intern_document_type_id' => 'required|integer|exists:intern_document_types,intern_document_type_id',
            ]);
            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()], 422);
            }
            $intership = InternDocumentType::find($intern_id);
            return response()->json([
                'message' => "Orgaization Intern Document Type",
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
                'intern_document_type_id' => $intern_id
            ]);
            $rules = [
                'organization_id' => 'required|integer|exists:organizations,organization_id',
                'intern_document_type_id' => 'required|integer|exists:intern_document_types,intern_document_type_id',
                'document_type_name' => [
                    'sometimes',
                    'string',
                    'max:255',
                    Rule::unique('intern_document_types', 'document_type_name')
                        ->ignore($intern_id, 'intern_document_type_id')
                        ->where(function ($query) use ($request): mixed {
                            return $query->where('organization_id', $request->organization_id);
                        }),
                ],

                'document_type_short_name' => [
                    'sometimes',
                    'string',
                    'max:20',
                    Rule::unique('intern_document_types', 'document_type_short_name')
                        ->ignore($intern_id, 'intern_document_type_id')
                        ->where(function ($query) use ($request) {
                            return $query->where('organization_id', $request->organization_id);
                        }),
                ],


            ];
            $validator = Validator::make($request->all(), $rules);
            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()], 422);
            }
            $intership = InternDocumentType::find($intern_id);
            $intership->update($request->only([

                'organization_id',
                'organization_entity_id',
                'document_type_name',
                'document_type_short_name',
                'is_active',
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
            $request->merge(['organization_id' => $org_id, 'intern_document_type_id' => $intern_id]);
            $validator = Validator::make($request->all(), [
                'organization_id' => 'required|integer|exists:organizations,organization_id',
                'intern_document_type_id' => 'required|integer|exists:intern_document_types,intern_document_type_id',
            ]);
            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()], 422);
            }
            $intership = InternDocumentType::find($intern_id);
            $intership->delete();
            return response()->json([
                'message' => 'Intern Document Type Deleted SuccessFully !'
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
