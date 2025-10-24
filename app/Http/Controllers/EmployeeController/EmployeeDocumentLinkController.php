<?php

namespace App\Http\Controllers\EmployeeController;
use App\Models\EmployeesModel\EmployeeDocumentLinks;
use Illuminate\Support\Facades\Validator;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class EmployeeDocumentLinkController extends Controller
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
            $query = EmployeeDocumentLinks::where('organization_id', $org_id);
            $per = $request->input('per_page', 10);
            $search = $request->input('search');
            if ($search) {
                $query->where(function ($q) use ($search) {
                    $q->where('linked_record_id', 'like', "%{$search}%");
                });
            }
            $data = $query->orderBy('created_at', 'desc')->paginate($per);
            return response()->json([
                'message' => 'OK',
                'links' => $data,
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
                'linked_record_id' => 'required|string|max:100',
                'general_employee_document_table_reference_id' => 'required|integer|exists:employee_document_types,employee_document_type_id',
                'employee_document_id' => 'required|integer|exists:employee_documents,employee_document_id',
            ]);
            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()], 422);
            }
            $data = $request->all();
            $links = EmployeeDocumentLinks::create(array_merge($data));
            return response()->json([
                'message' => 'Employees Document Link Added SuccessFully.',
                'links' => $links
            ], 201);
        } catch (\Exception $e) {
            // Handle any other errors and return a response
            return response()->json([
                'error' => 'Something went wrong. Please try again later.',
                'details' => $e->getMessage()
            ], 500);
        }
    }

    public function show(Request $request, $org_id, $link_id)
    {
        try {
            $request->merge(['organization_id' => $org_id, 'employee_document_link_id' => $link_id]);
            $validator = Validator::make($request->all(), [
                'organization_id' => 'required|integer|exists:organizations,organization_id',
                'employee_document_link_id' => 'required|integer|exists:employee_document_links,employee_document_link_id',
            ]);
            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()], 422);
            }
            $links = EmployeeDocumentLinks::find($link_id);
            return response()->json([
                'message' => 'Employee Document Link Found',
                'links' => $links
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Something went wrong. Please try again later.',
                'details' => $e->getMessage()
            ], 500);
        }
    }

    public function update(Request $request, $org_id, $link_id)
    {
        try {
            $request->merge([
                'organization_id' => $org_id,
                'employee_document_link_id' => $link_id
            ]);
            $rules = [
                'organization_id' => 'required|integer|exists:organizations,organization_id',
                   'employee_document_link_id' => 'required|integer|exists:employee_document_link_id,employee_document_link_id',
                'employee_document_id' => 'required|integer|exists:employee_documents,employee_document_id',
                'general_employee_document_table_reference_id' => 'nullable|string|max:100',
                'linked_record_id' => 'nullable|string|max:20',
            ];
            $validator = Validator::make($request->all(), $rules);
            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()], 422);
            }
            $links = EmployeeDocumentLinks::find($link_id);
            $links->update($request->only([
                'general_employee_document_table_reference_id',
                'employee_document_id',
                'linked_record_id',
            ]));
            return response()->json([
                'message' => 'Employee Document Link  Updated Successfully.',
                'links' => $links
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Something went wrong. Please try again later.',
                'details' => $e->getMessage()
            ], 500);
        }
    }

    public function destroy(Request $request, $org_id, $link_id)
    {
        try {
            $request->merge([
                'organization_id' => $org_id,
                'employee_document_link_id' => $link_id
            ]);
            $validator = Validator::make($request->all(), [
                'organization_id' => 'required|integer|exists:organizations,organization_id',
                'employee_document_link_id' => 'required|integer|exists:employee_document_links,employee_document_link_id',
            ]);
            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()], 422);
            }
            $links = EmployeeDocumentLinks::find($link_id);
            $links->delete();
            return response()->json([
                'message' => 'Employee Document Link Deleted Successfully'
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Something went wrong. Please try again later.',
                'details' => $e->getMessage()
            ], 500);
        }
    }

}
