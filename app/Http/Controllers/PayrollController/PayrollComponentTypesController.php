<?php
namespace App\Http\Controllers\PayrollController;
use App\Http\Controllers\Controller;
use App\Models\PayrollModels\PayrollComponentTypes;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class PayrollComponentTypesController extends Controller
{
    public function index(Request $request, $org_id)
    {
        try {
            // Get the authenticated user
            $user = Auth::guard('applicationusers')->user();
            $organizationIds = $user->Client->Organization->pluck('organization_id')->toArray();
            if (!in_array((int) $org_id, $organizationIds)) {
                return response()->json([
                    'message' => 'Unauthenticated'
                ], 401);
            }

            // Merge org_id into the request for validation purposes
            $request->merge(['organization_id' => $org_id]);

            // Validate organization_id exists
            $validator = Validator::make($request->all(), [
                'organization_id' => 'required|integer|exists:organizations,organization_id',
            ]);

            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()], 422);
            }

            // Get filters & pagination parameters
            $perPage = $request->get('per_page');
            $page = (int) $request->get('page', 1);
            $search = $request->get('search');

            // Build query for attendance break types
            $query = PayrollComponentTypes::where('organization_id', $org_id);

            // Apply search filter if provided
            if (!empty($search)) {
                $query->where(function ($q) use ($search) {
                    $q->where('payroll_component_type_name', 'like', '%' . $search . '%')
                        ->orWhere('description', 'like', '%' . $search . '%');
                });
            }

            // Handle pagination and fetch data
            if ($perPage === 'all') {
                $statusTypes = $query->get();
            } elseif (!empty($perPage) && is_numeric($perPage)) {
                $perPage = (int) $perPage;
                $statusTypes = $query->paginate($perPage, ['*'], 'page', $page);
            } else {
                $statusTypes = $query->get();
            }

            // Return success response
            return response()->json([
                'message' => 'Payroll Component Types fetched successfully',
                'payrollTypes' => $statusTypes
            ], 200);

        } catch (\Exception $e) {
            \Log::error('Error fetching attendance break types: ' . $e->getMessage());

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
            if (!in_array((int) $org_id, $organizationIds)) {
                return response()->json([
                    'message' => 'Unauthenticated'
                ], 401);
            }
            $request->merge(['organization_id' => $org_id]);
            $validator = Validator::make($request->all(), [
                'organization_id' => 'required|integer|exists:organizations,organization_id',
                'organization_entity_id' => 'nullable|integer|exists:organization_entities,organization_entity_id',
                'organization_configuration_template_id' => 'nullable|integer|exists:organization_configuration_templates,organization_configuration_template_id',
                'payroll_component_type_name' => [
                    'required',
                    'string',
                    'max:255',
                    Rule::unique('organization_payroll_component_types', 'payroll_component_type_name')
                        ->where(function ($query) use ($request) {
                            return $query->where('organization_id', $request->organization_id);
                        }),
                ],
                'description' => 'nullable|string|max:255',
                'is_active' => 'nullable|boolean',
                'created_at' => 'nullable|date',
                'updated_at' => 'nullable|date',
            ]);

            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()], 422);
            }
            $data = $request->all();
            PayrollComponentTypes::create($data);

            return response()->json([
                'message' => 'Payroll Component Type added successfully.',
            ], 201);

        } catch (\Exception $e) {
            \Log::error('Error creating payroll component type type: ' . $e->getMessage());
            return response()->json([
                'error' => 'Something went wrong. Please try again later.',
                'details' => $e->getMessage()
            ], 500);
        }
    }


    public function show(Request $request, $org_id, $type_id)
    {
        try {
            $user = Auth::guard('applicationusers')->user();
            $organizationIds = $user->Client->Organization->pluck('organization_id')->toArray();

            if (!in_array((int) $org_id, $organizationIds)) {
                return response()->json([
                    'message' => 'Unauthenticated'
                ], 401);
            }
            $request->merge([
                'organization_id' => $org_id,
                'organization_payroll_component_type_id' => $type_id
            ]);

            $validator = Validator::make($request->all(), [
                'organization_id' => 'required|integer|exists:organizations,organization_id',
                'organization_payroll_component_type_id' => 'required|integer|exists:organization_payroll_component_types,organization_payroll_component_type_id',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'errors' => $validator->errors()
                ], 422);
            }
            $payrollTypes = PayrollComponentTypes::find($type_id);
            return response()->json([
                'message' => 'Payroll component type found',
                'payrollTypes' => $payrollTypes
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Something went wrong. Please try again later.',
                'details' => $e->getMessage()
            ], 500);
        }
    }


    public function update(Request $request, $org_id, $type_id)
    {
        try {
            $request->merge([
                'organization_id' => $org_id,
                'organization_payroll_component_type_id' => $type_id
            ]);
            $rules = [


                'organization_id' => 'required|integer|exists:organizations,organization_id',
                'organization_payroll_component_type_id' => 'required|integer|exists:organization_payroll_component_types,organization_payroll_component_type_id',
                'organization_entity_id' => 'nullable|integer|exists:organization_entities,organization_entity_id',
                'organization_configuration_template_id' => 'nullable|integer|exists:organization_configuration_templates,organization_configuration_template_id',
                'payroll_component_type_name' => [
                    'nullable',
                    'string',
                    'max:255',
                    Rule::unique('organization_payroll_component_types', 'payroll_component_type_name')
                        ->where(function ($query) use ($request) {
                            return $query->where('organization_id', $request->organization_id);
                        })
                        ->ignore($type_id, 'organization_payroll_component_type_id'),
                ],
                'description' => 'nullable|string|max:255',
                'is_active' => 'nullable|boolean',
                'created_at' => 'nullable|date',
                'updated_at' => 'nullable|date',
            ];
            $validator = Validator::make($request->all(), $rules);
            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()], 422);
            }
            $payrollTypes = PayrollComponentTypes::find($type_id);
            $payrollTypes->update($request->only([
                'payroll_component_type_name',
                'description',
                'is_active',
            ]));
            return response()->json([
                'message' => 'Payroll component Type  Updated Successfully.',
                'payrollTypes' => $payrollTypes
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Something went wrong. Please try again later.',
                'details' => $e->getMessage()
            ], 500);
        }
    }

    public function destroy(Request $request, $org_id, $type_id)
    {
        try {
            $request->merge([
                'organization_id' => $org_id,
                'organization_attendance_break_type_id' => $type_id
            ]);
            $validator = Validator::make($request->all(), [
                'organization_id' => 'required|integer|exists:organizations,organization_id',
                'organization_payroll_component_type_id' => 'required|integer|exists:organization_payroll_component_types,organization_payroll_component_type_id',
            ]);
            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()], 422);
            }
            $payrollTypes = PayrollComponentTypes::find($type_id);
            $payrollTypes->delete();
            return response()->json([
                'message' => 'Employee Attendence Break Type Deleted Successfully'
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Something went wrong. Please try again later.',
                'details' => $e->getMessage()
            ], 500);
        }
    }



}
