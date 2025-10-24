<?php

namespace App\Http\Controllers\EmployeeController;
use App\Models\EmployeesModel\EmployeeContact;
use App\Models\EmployeesModel\EmployeeDocument;
use App\Models\EmployeesModel\EmployeeDocumentSectionLink;
use App\Models\EmployeesModel\EmployeeDocumentTypes;
use Illuminate\Support\Facades\Validator;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class EmployeeDocumentController extends Controller
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
                $workshift = EmployeeDocument::with('employee', 'DocumentType', 'SectionLink.ProfileSection')->where('organization_id', $org_id)->get();

                if ($workshift->isEmpty()) {
                    return response()->json([
                        'message' => 'workshift not found.'
                    ], 404);
                }
                $mappedWorkshift = $workshift->map(function ($dep) {
                    return [
                         'employee_name' => trim(($emp->employee->first_name ?? '') . ' ' . ($emp->employee->middle_name ?? '') . ' ' . ($emp->employee->last_name ?? '')),
                        'document_type' => $dep->DocumentType->document_type_name ?? '',
                        'document_name' => $dep->document_name ?? '',
                       
                    ];
                });
                return response()->json($mappedWorkshift);
            }






            $query = EmployeeDocument::with('employee', 'DocumentType', 'SectionLink.ProfileSection')->where('organization_id', $org_id);
            $per = $request->input('per_page', 10);
            $search = $request->input('search');
            if ($search) {
                $query->where(function ($q) use ($search) {
                    $q->where('document_name', 'like', "%{$search}%");
                });
            }
            $data = $query->orderBy('created_at', 'desc')->paginate($per);
            return response()->json([
                'message' => 'OK',
                'document' => $data,
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
            // Merge organization_id from route parameter
            $request->merge(['organization_id' => $org_id]);

            // Validation
            $validator = Validator::make($request->all(), [
                'organization_id' => 'required|integer|exists:organizations,organization_id',
                'document_name' => 'required|string|max:100',
                'employee_document_type_id' => 'required|integer|exists:employee_document_types,employee_document_type_id',
                'employee_id' => 'required|integer|exists:employees,employee_id',
                'document_url' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:2048',
                'organization_entity_id' => 'nullable|integer',
                'organization_employee_profile_section_id' => 'nullable|array',
                'organization_employee_profile_section_id.*' => 'integer'
            ]);

            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()], 422);
            }

            $data = $request->all();

            // Handle file upload
            if ($request->hasFile('document_url') && $request->file('document_url')->isValid()) {
                $file = $request->file('document_url');
                $path = $file->store('documents', 'public');
                $data['document_url'] = $path; // Store relative path
            } else {
                $data['document_url'] = $request->input('document_url', null);
            }




            $employeeDocument = EmployeeDocument::create($data);


            // add data in profile  section link table 
            // Add the data in profile section link table
            if (!empty($data['organization_employee_profile_section_id'])) {
                foreach ($data['organization_employee_profile_section_id'] as $section_id) {
                    EmployeeDocumentSectionLink::create([
                        'employee_document_id' => $employeeDocument->employee_document_id,
                        'organization_id' => $org_id,
                        'organization_employee_profile_section_id' => $section_id,
                        'organization_entity_id' => $data['organization_entity_id'] ?? null, // optional
                    ]);
                }
            }



            return response()->json([
                'message' => 'Employee Document Added Successfully.',
                'employeeDocument' => $employeeDocument
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Something went wrong. Please try again later.',
                'details' => $e->getMessage()
            ], 500);
        }
    }

   public function show(Request $request, $org_id, $document_id)
    {
        try {
            $request->merge(['organization_id' => $org_id, 'employee_document_id' => $document_id]);

            $validator = Validator::make($request->all(), [
                'organization_id' => 'required|integer|exists:organizations,organization_id',
                'employee_document_id' => 'required|integer|exists:employee_documents,employee_document_id',
            ]);

            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()], 422);
            }

            $employeeDocument = EmployeeDocument::find($document_id);
            $employeeDocument->load('SectionLink.ProfileSection');

            if (!$employeeDocument) {
                return response()->json(['error' => 'Document not found'], 404);
            }
            // Transform document URL
            $employeeDocumentArray = $employeeDocument->toArray();
          $employeeDocumentArray['document_url'] = $employeeDocument->document_url;
            return response()->json([
                'message' => 'Employee Document Found',
                'employeeDocument' => $employeeDocumentArray
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Something went wrong. Please try again later.',
                'details' => $e->getMessage()
            ], 500);
        }
    }

    public function update(Request $request, $org_id, $document_id)
    {
        try {
            // Find existing document
            $employeeDocument = EmployeeDocument::where('employee_document_id', $document_id)
                ->where('organization_id', $org_id)
                ->first();


            if (!$employeeDocument) {
                return response()->json(['error' => 'Employee Document not found.'], 404);
            }

            $request->merge(['organization_id' => $org_id]);

            // Validation - all fields nullable
            $validator = Validator::make($request->all(), [
                'organization_id' => 'required|integer|exists:organizations,organization_id',
                'document_name' => 'nullable|string|max:100',
                'employee_document_type_id' => 'nullable|integer|exists:employee_document_types,employee_document_type_id',
                'employee_id' => 'nullable|integer|exists:employees,employee_id',
                'document_url' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:2048',
                'organization_entity_id' => 'nullable|integer',
                'organization_employee_profile_section_id' => 'nullable|array',
                'organization_employee_profile_section_id.*' => 'integer'
            ]);

            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()], 422);
            }
            $data = $request->all();

            // Prepare update data
            $updateData = [
                'document_name' => $data['document_name'] ?? $employeeDocument->document_name,
                'employee_document_type_id' => $data['employee_document_type_id'] ?? $employeeDocument->employee_document_type_id,
                'employee_id' => $data['employee_id'] ?? $employeeDocument->employee_id,
                'organization_entity_id' => $data['organization_entity_id'] ?? $employeeDocument->organization_entity_id,
                // keep current document_url, will overwrite if new file uploaded
                'document_url' => $employeeDocument->document_url
            ];

            // Handle file upload
            if ($request->hasFile('document_url') && $request->file('document_url')->isValid()) {
                $file = $request->file('document_url');
                $path = $file->store('documents', 'public');
                $updateData['document_url'] = $path;
            }

            // Update document
            $employeeDocument->update($updateData);

            // Sync profile section links
            if (isset($data['organization_employee_profile_section_id']) && is_array($data['organization_employee_profile_section_id'])) {
                // Delete old links
                EmployeeDocumentSectionLink::where('employee_document_id', $employeeDocument->employee_document_id)->delete();

                // Insert new links
                foreach ($data['organization_employee_profile_section_id'] as $section_id) {
                    EmployeeDocumentSectionLink::create([
                        'employee_document_id' => $employeeDocument->employee_document_id,
                        'organization_id' => $org_id,
                        'organization_employee_profile_section_id' => $section_id,
                        'organization_entity_id' => $data['organization_entity_id'] ?? null,
                    ]);
                }
            }

            return response()->json([
                'message' => 'Employee Document Updated Successfully.',
                'employeeDocument' => $employeeDocument->fresh() // return updated data
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Something went wrong. Please try again later.',
                'details' => $e->getMessage()
            ], 500);
        }
    }


    public function destroy(Request $request, $org_id, $document_id)
    {
        try {
            $request->merge([
                'organization_id' => $org_id,
                'employee_document_id' => $document_id
            ]);
            $validator = Validator::make($request->all(), [
                'organization_id' => 'required|integer|exists:organizations,organization_id',
                'employee_document_id' => 'required|integer|exists:employee_documents,employee_document_id',
            ]);
            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()], 422);
            }


            EmployeeDocumentSectionLink::where('employee_document_id', $document_id)->delete();

            $employeeDocument = EmployeeDocument::find($document_id);
            $employeeDocument->delete();
            return response()->json([
                'message' => 'Employee Documents Deleted Successfully'
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Something went wrong. Please try again later.',
                'details' => $e->getMessage()
            ], 500);
        }
    }

}
