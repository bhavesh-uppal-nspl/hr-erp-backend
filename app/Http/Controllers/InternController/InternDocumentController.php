<?php

namespace App\Http\Controllers\InternController;
use App\Models\EmployeesModel\EmployeeDocument;
use App\Models\EmployeesModel\EmployeeDocumentSectionLink;
use App\Models\InterModel\InternCertificate;
use Illuminate\Support\Facades\Validator;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class InternDocumentController extends Controller
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
            $query = InternCertificate::with('Intern')->where('organization_id', $org_id);
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
            // Merge organization_id from route parameter
            $request->merge(['organization_id' => $org_id]);


            // Validation
            $validator = Validator::make($request->all(), [
                'organization_id' => 'required|integer|exists:organizations,organization_id',
                'certificate_title' => 'required|string|max:100',
                'certificate_number' => 'required|string|max:100',
                'remarks' => 'required|string|max:255',
                'certificate_type ' => 'required|in:Completion,Recommendation,Appreciation,Custom',
                'intern_id' => 'required|integer|exists:interns,intern_id',
                'certificate_file_url' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:2048',
                'organization_entity_id' => 'nullable|integer',
                'issued_by_employee_id' => 'required|integer|exists:issued_by_employees,employee_id',
                'issue_date'=>'nullable|date'
                
            ]);

            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()], 422);
            }

            $data = $request->all();

            // Handle file upload
            if ($request->hasFile('certificate_file_url') && $request->file('certificate_file_url')->isValid()) {
                $file = $request->file('certificate_file_url');
                $path = $file->store('certificates', 'public');
                $data['certificate_file_url'] = $path; // Store relative path
            } else {
                $data['certificate_file_url'] = $request->input('certificate_file_url', null);
            }

            $employeeDocument = EmployeeDocument::create($data);
            return response()->json([
                'message' => 'Intern Document Added Successfully.',
                'intership' => $employeeDocument
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Something went wrong. Please try again later.',
                'details' => $e->getMessage()
            ], 500);
        }
    }

   public function show(Request $request, $org_id, $certificate_id)
    {
        try {
            $request->merge(['organization_id' => $org_id, 'intern_certificate_id' => $certificate_id]);

            $validator = Validator::make($request->all(), [
                'organization_id' => 'required|integer|exists:organizations,organization_id',
                'intern_certificate_id' => 'required|integer|exists:intern_certificates,intern_certificate_id',
            ]);

            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()], 422);
            }

            $employeeDocument = InternCertificate::find($certificate_id);
         

            if (!$employeeDocument) {
                return response()->json(['error' => 'Intern not found'], 404);
            }
           
            return response()->json([
                'message' => 'Intern Certicate Found',
                'intership' => $employeeDocument
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Something went wrong. Please try again later.',
                'details' => $e->getMessage()
            ], 500);
        }
    }

    public function update(Request $request, $org_id, $certificate_id)
    {
        try {
            // Find existing document
            $employeeDocument = InternCertificate::where('intern_certificate_id', $certificate_id)
                ->where('organization_id', $org_id)
                ->first();


            
            $request->merge(['organization_id' => $org_id]);

            // Validation - all fields nullable
            $validator = Validator::make($request->all(), [
                'organization_id' => 'required|integer|exists:organizations,organization_id',
                'intern_certificate_id' => 'required|integer|exists:intern_certificates,intern_certificate_id',
                'certificate_title' => 'required|string|max:100',
                'certificate_number' => 'required|string|max:100',
                'remarks' => 'required|string|max:255',
                'certificate_type ' => 'required|in:Completion,Recommendation,Appreciation,Custom',
                'intern_id' => 'required|integer|exists:interns,intern_id',
                'certificate_file_url' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:2048',
                'organization_entity_id' => 'nullable|integer',
                'issued_by_employee_id' => 'required|integer|exists:issued_by_employees,employee_id',
                'issue_date'=>'nullable|date'
            ]);

            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()], 422);
            }
            $data = $request->all();

            // Prepare update data
            $updateData = [
                'certificate_type' => $data['certificate_type '] ?? $employeeDocument->certificate_type ,
                'certificate_title' => $data['certificate_title'] ?? $employeeDocument->certificate_title,
                'intern_id' => $data['intern_id '] ?? $employeeDocument->intern_id ,
                'issue_date' => $data['issue_date '] ?? $employeeDocument->issue_date ,
                'certificate_number' => $data['certificate_number'] ?? $employeeDocument->certificate_number,
                'organization_entity_id' => $data['organization_entity_id'] ?? $employeeDocument->organization_entity_id,
              
                'certificate_file_url' => $employeeDocument->certificate_file_url,
                'issued_by_employee_id' => $employeeDocument->issued_by_employee_id,
                'remarks' => $employeeDocument->remarks
            ];

            // Handle file upload
            if ($request->hasFile('certificate_file_url') && $request->file('certificate_file_url')->isValid()) {
                $file = $request->file('certificate_file_url');
                $path = $file->store('certificates', 'public');
                $updateData['certificate_file_url'] = $path;
            }

            // Update document
            $employeeDocument->update($updateData);

          
            return response()->json([
                'message' => 'Intern Document Updated Successfully.',
                'employeeDocument' => $employeeDocument->fresh() // return updated data
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Something went wrong. Please try again later.',
                'details' => $e->getMessage()
            ], 500);
        }
    }


    public function destroy(Request $request, $org_id, $certificate_id)
    {
        try {
            $request->merge([
                'organization_id' => $org_id,
                'intern_certificate_id' => $certificate_id
            ]);
            $validator = Validator::make($request->all(), [
                'organization_id' => 'required|integer|exists:organizations,organization_id',
                'intern_certificate_id' => 'required|integer|exists:intern_certificates,intern_certificate_id',
            ]);
            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()], 422);
            }


      
            $employeeDocument = InternCertificate::find($certificate_id);
            $employeeDocument->delete();
            return response()->json([
                'message' => 'Intern Certicate Deleted Successfully'
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Something went wrong. Please try again later.',
                'details' => $e->getMessage()
            ], 500);
        }
    }

}
