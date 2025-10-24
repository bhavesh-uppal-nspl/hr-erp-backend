<?php

namespace App\Http\Controllers\EMSControllers;

use App\Http\Controllers\Controller;
use App\Models\EMSModels\company;
use App\Models\EMSModels\TrainingProgram;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class CompaniesController extends Controller
{

    public function index(Request $request)
    {

        try {
            $perPage = $request->get('per_page', 10); // Keep as string for "all" comparison
            $page = (int) $request->get('page', 1);
            $search = $request->get('search');
            $organizationId = $request->get('organization_id');

            $query = Company::query();

            // Filter by organization_id
            if (!empty($organizationId)) {
                $query->where('organization_id', $organizationId);
            }

            // Search filter
            if (!empty($search)) {
                $query->where(function ($q) use ($search) {
                    $q->where('company_name', 'like', '%' . $search . '%');
                });
            }

            // Handle pagination or return all
            $company = ($perPage === 'all')
                ? $query->get()
                : $query->paginate((int) $perPage, ['*'], 'page', $page);

            return response()->json([
                'message' => 'Company fetched successfully',
                'data' => $company
            ]);
        } catch (\Exception $e) {
            \Log::error('Error fetching Company: ' . $e->getMessage());
            return response()->json(['message' => 'Failed to fetch Company'], 500);
        }

    }


    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'organization_id' => 'required|integer|exists:organizations,organization_id',
            'organization_entity_id' => 'required|integer|exists:organization_entities,organization_entity_id',
            'company_name' => [
                'required',
                'string',
                'max:150',
                Rule::unique('organization_ems_companies')
                    ->where(fn($query) => $query->where('organization_id', $request->organization_id))
            ],
            'industry' => 'sometimes|nullable|string|max:100',
            'contact_person' => 'sometimes|nullable|string|max:100',
            'email' => 'sometimes|nullable|email|max:100',
            'phone' => 'sometimes|nullable|string|max:20',
            'address' => 'sometimes|nullable|string|max:255',
            'status' => 'sometimes|in:Active,Inactive',
        ]);


        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            $company = Company::create($request->all());

            return response()->json([
                'success' => true,
                'message' => 'Company created successfully.',
                'data' => $company,
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to create Company.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function show($id)
    {
        try {
            $company = Company::findOrFail($id);

            return response()->json([
                'success' => true,
                'data' => $company,
            ]);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Company not found.',
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error fetching Company.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }


    public function update(Request $request, $id)
    {
        try {
            $company = Company::findOrFail($id);

            $validator = Validator::make($request->all(), [
                'organization_id' => 'sometimes|integer|exists:organizations,organization_id',
                'organization_entity_id' => 'sometimes|integer|exists:organization_entities,organization_entity_id',
                'company_name' => [
                    'sometimes',
                    'string',
                    'max:150',
                    Rule::unique('organization_ems_companies')
                        ->where(fn($query) => $query->where('organization_id', $request->organization_id ?? $company->organization_id))
                        ->ignore($company->organization_ems_company_id, 'organization_ems_company_id')
                ],
                'industry' => 'sometimes|nullable|string|max:100',
                'contact_person' => 'sometimes|nullable|string|max:100',
                'email' => 'sometimes|nullable|email|max:100',
                'phone' => 'sometimes|nullable|string|max:20',
                'address' => 'sometimes|nullable|string|max:255',
                'status' => 'sometimes|in:Active,Inactive',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'errors' => $validator->errors(),
                ], 422);
            }

            $company->update($request->all());

            return response()->json([
                'success' => true,
                'message' => 'Company updated successfully.',
                'data' => $company,
            ]);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Company not found.',
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update Company.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function destroy($id)
    {
        try {
            $company = Company::findOrFail($id);
            $company->delete();

            return response()->json([
                'success' => true,
                'message' => 'Company deleted successfully.',
            ]);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Company not found.',
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete Company.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
