<?php

namespace App\Http\Controllers\EmployeeController;
use App\Models\EmployeesModel\EmployeeContact;
use App\Models\EmployeesModel\EmployeeDocumentTypes;
use Illuminate\Support\Facades\Validator;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class EmployeeDocumentTypeController extends Controller
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
                $workshift = EmployeeDocumentTypes::with('employee')->where('organization_id', $org_id)->get();

                if ($workshift->isEmpty()) {
                    return response()->json([
                        'message' => 'workshift not found.'
                    ], 404);
                }
                $mappedWorkshift = $workshift->map(function ($dep) {
                    return [
                        'document_type_name'=>$dep->document_type_name,
                        'document_type_short_name' => $dep->document_type_short_name ?? '',
                        'description' => $dep->description ?? '',
                     
                    ];
                });
                return response()->json($mappedWorkshift);
            }





            $query = EmployeeDocumentTypes::with('employee')->where('organization_id', $org_id);
            $per = $request->input('per_page', 10);
            $search = $request->input('search');
            if ($search) {
                $query->where(function ($q) use ($search) {
                    $q->where('document_type_name', 'like', "%{$search}%");
                    $q->where('document_type_short_name', 'like', "%{$search}%");
                });
            }
            $data = $query->orderBy('created_at', 'desc')->paginate($per);
            return response()->json([
                'message' => 'OK',
                'documentType' => $data,
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
            $request->merge(['organization_id' => $org_id]);
            $validator = Validator::make($request->all(), [
                'organization_id' => 'required|integer|exists:organizations,organization_id',
                'document_type_name' => [
                'nullable',
                'string',
                'max:255',
                Rule::unique('employee_document_types', 'document_type_name')
                    ->where(function ($query) use ($org_id) {
                        return $query->where('organization_id', $org_id);
                    }),
            ],

            'document_type_short_name' => [
                'nullable',
                'string',
                'max:100',
                Rule::unique('employee_document_types', 'document_type_short_name')
                    ->where(function ($query) use ($org_id) {
                        return $query->where('organization_id', $org_id);
                    }),
            ],

            'description' => 'nullable|string|max:1000',
                'is_active' => 'nullable|boolean',
            ]);
            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()], 422);
            }
            $data = $request->all();
            $employeeDocument = EmployeeDocumentTypes::create(array_merge($data));
            return response()->json([
                'message' => 'Employees Document Types Added SuccessFully.',
                'employeeDocument' => $employeeDocument
            ], 201);
        } catch (\Exception $e) {
            // Handle any other errors and return a response
            return response()->json([
                'error' => 'Something went wrong. Please try again later.',
                'details' => $e->getMessage()
            ], 500);
        }
    }

    public function show(Request $request, $org_id, $document_type_id)
    {
        try {
            $request->merge(['organization_id' => $org_id, 'employee_document_type_id' => $document_type_id]);
            $validator = Validator::make($request->all(), [
                'organization_id' => 'required|integer|exists:organizations,organization_id',
                'employee_document_type_id' => 'required|integer|exists:employee_document_types,employee_document_type_id',
            ]);
            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()], 422);
            }
            $employeeDocument = EmployeeDocumentTypes::find($document_type_id);
            return response()->json([
                'message' => 'Employee Document Type Found',
                'employeeDocumentTypes' => $employeeDocument
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Something went wrong. Please try again later.',
                'details' => $e->getMessage()
            ], 500);
        }
    }

    public function update(Request $request, $org_id, $document_type_id)
    {
        try {
            $request->merge([
                'organization_id' => $org_id,
                'employee_document_type_id ' => $document_type_id
            ]);
            $rules = [
                'organization_id' => 'required|integer|exists:organizations,organization_id',
                'employee_document_type_id' => 'required|integer|exists:employee_document_types,employee_document_type_id',
                 'document_type_name' => [
                    'nullable',
                    'string',
                    'max:255',
                    Rule::unique('employee_document_types', 'document_type_name')
                        ->where(function ($query) use ($request) {
                            return $query->where('organization_id', $request->organization_id);
                        })
                        ->ignore($document_type_id, 'employee_document_type_id'),
                ],

                'document_type_short_name' => [
                    'nullable',
                    'string',
                    'max:255',
                    Rule::unique('employee_document_types', 'document_type_short_name')
                        ->where(function ($query) use ($request) {
                            return $query->where('organization_id', $request->organization_id);
                        })
                        ->ignore($document_type_id, 'employee_document_type_id'),
                ],

                'description' => 'nullable|string',
                'is_active' => 'nullable|boolean',
            ];
            $validator = Validator::make($request->all(), $rules);
            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()], 422);
            }
            $employeeDocumentType = EmployeeDocumentTypes::find($document_type_id);
            $employeeDocumentType->update($request->only([
                'document_type_name',
                'document_type_short_name',
                'description',
                'is_active',
            ]));

            return response()->json([
                'message' => 'Employee Document Type Updated Successfully.',
                'employeeDocumentType' => $employeeDocumentType
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Something went wrong. Please try again later.',
                'details' => $e->getMessage()
            ], 500);
        }
    }

    public function destroy(Request $request, $org_id, $document_type_id)
    {
        try {
            $request->merge([
                'organization_id' => $org_id,
                'employee_document_type_id' => $document_type_id
            ]);
            $validator = Validator::make($request->all(), [
                'organization_id' => 'required|integer|exists:organizations,organization_id',
                'employee_document_type_id' => 'required|integer|exists:employee_document_types,employee_document_type_id',
            ]);
            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()], 422);
            }
            $employeeDocumentTypes = EmployeeDocumentTypes::find($document_type_id);
            $employeeDocumentTypes->delete();
            return response()->json([
                'message' => 'Employee Document type Deleted Successfully'
            ], 200);

        } catch (\Exception $e) {

            return response()->json([
                'error' => 'Something went wrong. Please try again later.',
                'details' => $e->getMessage()
            ], 500);
        }
    }

}
