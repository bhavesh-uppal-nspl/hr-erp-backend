<?php

namespace App\Http\Controllers\OrganizationController;

use App\Models\OrganizationModel\OrganizationEmploymentIncrementTypes;
use App\Models\OrganizationModel\OrganizationProfileSection;
use Illuminate\Support\Facades\Validator;
use App\Http\Controllers\Controller;
use App\Models\OrganizationModel\OrganizationEmpAddType;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Auth;

class OrganizationEmployeeProfileSectionController extends Controller
{

    public function index(Request $request, $org_id)
    {
        try {
            $request->merge(['organization_id' => $org_id]);
            $user = Auth::guard('applicationusers')->user();
            $organizationIds = $user->Client->Organization->pluck('organization_id')->toArray();
            if (!in_array((int) $org_id, $organizationIds)) {
                return response()->json([
                    'message' => 'Unauthenticated'
                ], 401);
            }
            $perPage = $request->get('per_page');
            $page = (int) $request->get('page', 1);
            $search = $request->get('search');
            $organizationId = $request->get('organization_id');
            $query = OrganizationProfileSection::query();
            if (!empty($organizationId)) {
                $query->where('organization_id', $organizationId);
            }
            if (!empty($search)) {
                $query->where(function ($q) use ($search) {
                    $q->where('employee_increment_type_name', 'like', '%' . $search . '%')
                        ->orWhere('description', 'like', '%' . $search . '%');
                });
            }
            if ($perPage === 'all') {
                $statusTypes = $query->get();
            } elseif (!empty($perPage) && is_numeric($perPage)) {
                $perPage = (int) $perPage;
                $statusTypes = $query->paginate($perPage, ['*'], 'page', $page);
            } else {
                $statusTypes = $query->get();
            }
            return response()->json([
                'profileSections' => $statusTypes
            ]);
        } catch (\Exception $e) {
            \Log::error('Error fetching attendance status types: ' . $e->getMessage());
            return response()->json([
                'message' => 'Failed to fetch attendance status types'
            ], 500);
        }
    }


    
}
