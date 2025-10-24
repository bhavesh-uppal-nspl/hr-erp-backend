<?php

namespace App\Http\Controllers\PayrollController;
use App\Http\Controllers\Controller;
use App\Models\PayrollModels\PayrollComponent;
use App\Models\PayrollModels\PayrollComponentTypes;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class PayrollComponentController extends Controller
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
            $request->merge(['organization_id' => $org_id]);
            $validator = Validator::make($request->all(), [
                'organization_id' => 'required|integer|exists:organizations,organization_id',
            ]);

            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()], 422);
            }
            $perPage = $request->get('per_page');
            $page = (int) $request->get('page', 1);
            $search = $request->get('search');

            $query = PayrollComponent::with('payrollComponentType')->where('organization_id', $org_id);

            if (!empty($search)) {
                $query->where(function ($q) use ($search) {
                    $q->where('payroll_component_type_name', 'like', '%' . $search . '%')
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

            // Return success response
            return response()->json([
                'message' => 'Payroll Component  fetched successfully',
                'payrollComponent' => $statusTypes
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
                   'organization_payroll_component_type_id' => 'nullable|integer|exists:organization_payroll_component_types,organization_payroll_component_type_id',
                'payroll_component_name' => [
                    'required',
                    'string',
                    'max:255',
                    Rule::unique('organization_payroll_components', 'payroll_component_name')
                        ->where(function ($query) use ($request) {
                            return $query->where('organization_id', $request->organization_id);
                        }),
                ],
                'calculation_method' => 'nullable|in:Fixed,Percentage,Formula,Slab',
                'rounding_rule' => 'nullable|in:None,Nearest,Up,Down',
               'fixed_amount' => 'nullable|numeric',
                'percentage_of_component' => 'nullable|string|max:50',
                'taxable' => 'nullable|boolean',
                'affects_net_pay' => 'nullable|boolean',
                'is_active' => 'nullable|boolean',
                'rounding_precision' => 'nullable|integer|min:0|max:255',
                'sort_order' => 'nullable|integer',
                'effective_from' => 'required|date',
                'effective_to' => 'required|date|after_or_equal:effective_from',
                'formula_json' => 'nullable|string',
            ]);

            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()], 422);
            }
            $data = $request->all();
            PayrollComponent::create($data);

            return response()->json([
                'message' => 'Payroll Component  added successfully.',
            ], 201);

        } catch (\Exception $e) {
            \Log::error('Error creating payroll component : ' . $e->getMessage());
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
                'organization_payroll_component_id' => $type_id
            ]);
            $validator = Validator::make($request->all(), [
                'organization_id' => 'required|integer|exists:organizations,organization_id',
                'organization_payroll_component_id' => 'required|integer|exists:organization_payroll_components,organization_payroll_component_id',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'errors' => $validator->errors()
                ], 422);
            }
            $payroll = PayrollComponent::find($type_id);
            $payroll->load('payrollComponentType');
            return response()->json([
                'message' => 'Payroll component found',
                'payroll' => $payroll
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
                'organization_payroll_component_id' => $type_id
            ]);
            $rules = [
                'organization_id' => 'required|integer|exists:organizations,organization_id',
                'organization_payroll_component_id' => 'required|integer|exists:organization_payroll_components,organization_payroll_component_id',
                'organization_entity_id' => 'nullable|integer|exists:organization_entities,organization_entity_id',
                   'organization_payroll_component_type_id' => 'nullable|integer|exists:organization_payroll_component_types,organization_payroll_component_type_id',
                'payroll_component_name' => [
                    'nullable',
                    'string',
                    'max:255',
                    Rule::unique('organization_payroll_components', 'payroll_component_name')
                        ->where(function ($query) use ($request) {
                            return $query->where('organization_id', $request->organization_id);
                        })
                        ->ignore($type_id, 'organization_payroll_component_id'),
                ],

                'calculation_method' => 'nullable|in:Fixed,Percentage,Formula,Slab',
                'rounding_rule' => 'nullable|in:None,Nearest,Up,Down',
               'fixed_amount' => 'nullable|numeric',
                'percentage_of_component' => 'nullable|string|max:50',
                'taxable' => 'nullable|boolean',
                'affects_net_pay' => 'nullable|boolean',
                'is_active' => 'nullable|boolean',
                    'rounding_precision' => 'nullable|integer|min:0|max:255',
                'sort_order' => 'nullable|integer',
                'effective_from' => 'required|date',
                'effective_to' => 'required|date|after_or_equal:effective_from',
                'formula_json' => 'nullable|string',
            ];
            $validator = Validator::make($request->all(), $rules);
            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()], 422);
            }
            $payroll = PayrollComponent::find($type_id);
            $payroll->update($request->only([
                'organization_payroll_component_type_id ',
                'organization_entity_id',
                'organization_id',
                'payroll_component_name',
                'calculation_method',
                'fixed_amount',
                'percentage_of_component',
                'formula_json',
                'taxable',
                'affects_net_pay',
                'rounding_rule',
                'rounding_precision',
                'sort_order',
                'is_active',
                'effective_from',
                'effective_to'
            ]));
            return response()->json([
                'message' => 'Payroll component  Updated Successfully.',
                'payroll' => $payroll
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
                'organization_payroll_component_id' => 'required|integer|exists:organization_payroll_components,organization_payroll_component_id',
            ]);
            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()], 422);
            }
            $payroll = PayrollComponent::find($type_id);
            $payroll->delete();
            return response()->json([
                'message' => 'payroll component Deleted Successfully'
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Something went wrong. Please try again later.',
                'details' => $e->getMessage()
            ], 500);
        }
    }



}
