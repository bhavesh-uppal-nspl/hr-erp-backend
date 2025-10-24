<?php

namespace App\Http\Controllers\PayrollController;
use App\Http\Controllers\Controller;
use App\Models\PayrollModels\PayrollComponentTypes;
use App\Models\PayrollModels\PayrollCycle;
use App\Models\PayrollModels\PayrollRun;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class PayrollCycleController extends Controller
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

            $query = PayrollCycle::where('organization_id', $org_id);
            if (!empty($search)) {
                $query->where(function ($q) use ($search) {
                    $q->where('pay_frequency', 'like', '%' . $search . '%')
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
                'message' => 'Payroll cycle fetched successfully',
                'payroll' => $statusTypes
            ], 200);

        } catch (\Exception $e) {
            \Log::error('Error fetching payroll run : ' . $e->getMessage());

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
                'payroll_cycle_name' => [
                    'required',
                    'string',
                    'max:255',
                    function ($attribute, $value, $fail) use ($org_id) {
                        $exists = PayrollCycle::where('organization_id', $org_id)
                            ->where('payroll_cycle_name', $value)
                            ->exists();
                        if ($exists) {
                            $fail('cycle name is already exists.');
                        }
                    }
                ],
                'week_start_day' => 'required|in:Mon,Tue,Wed,Thu,Fri,Sat,Sun',
                'pay_frequency' => 'required|in:Monthly,SemiMonthly,BiWeekly,Weekly,Daily',
                'monthly_period_start_day' => 'required|integer|min:1|max:31',
            ]);

            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()], 422);
            }
            $data = $request->all();
            PayrollCycle::create($data);
            return response()->json([
                'message' => 'Payroll Cycle added successfully.',
            ], 201);

        } catch (\Exception $e) {
            \Log::error('Error creating payroll run: ' . $e->getMessage());
            return response()->json([
                'error' => 'Something went wrong. Please try again later.',
                'details' => $e->getMessage()
            ], 500);
        }
    }

    public function show(Request $request, $org_id, $id)
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
                'organization_payroll_cycle_id' => $id
            ]);
            $validator = Validator::make($request->all(), [
                'organization_id' => 'required|integer|exists:organizations,organization_id',
                'organization_payroll_cycle_id' => 'required|integer|exists:organization_payroll_cycles,organization_payroll_cycle_id',
            ]);
            if ($validator->fails()) {
                return response()->json([
                    'errors' => $validator->errors()
                ], 422);
            }
            $payrollcycle = PayrollCycle::find($id);
            return response()->json([
                'message' => 'Payroll cycle found',
                'payroll' => $payrollcycle
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Something went wrong. Please try again later.',
                'details' => $e->getMessage()
            ], 500);
        }
    }

    public function update(Request $request, $org_id, $id)
    {
        try {
            $request->merge([
                'organization_id' => $org_id,
                'organization_payroll_cycle_id' => $id
            ]);
            $rules = [
                'organization_id' => 'required|integer|exists:organizations,organization_id',
                'organization_entity_id' => 'nullable|integer|exists:organization_entities,organization_entity_id',
                'payroll_cycle_name' => [
                    'sometimes',
                    'string',
                    'max:255',
                    Rule::unique('organization_payroll_cycles', 'payroll_cycle_name')
                        ->where(function ($query) use ($org_id) {
                            return $query->where('organization_id', $org_id);
                        })
                        ->ignore($id, 'organization_payroll_cycle_id'),
                ],
                'week_start_day' => 'nullable|in:Mon,Tue,Wed,Thu,Fri,Sat,Sun',
                'pay_frequency' => 'nullable|in:Monthly,SemiMonthly,BiWeekly,Weekly,Daily',
                'monthly_period_start_day' => 'nullable|integer|min:1|max:31',
            ];
            $validator = Validator::make($request->all(), $rules);
            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()], 422);
            }
            $payrollcycle = PayrollCycle::find($id);
            $payrollcycle->update($request->only([
                'pay_frequency',
                'week_start_day',
                'monthly_period_start_day',
                'payroll_cycle_name'
            ]));
            return response()->json([
                'message' => 'Payroll cycle  Updated Successfully.',
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Something went wrong. Please try again later.',
                'details' => $e->getMessage()
            ], 500);
        }
    }

    public function destroy(Request $request, $org_id, $id)
    {
        try {
            $request->merge([
                'organization_id' => $org_id,
                'organization_payroll_cycle_id' => $id
            ]);
            $validator = Validator::make($request->all(), [
                'organization_id' => 'required|integer|exists:organizations,organization_id',
                'organization_payroll_cycle_id' => 'required|integer|exists:organization_payroll_cycles,organization_payroll_cycle_id',
            ]);
            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()], 422);
            }
            $payrollcycle = PayrollCycle::find($id);
            $payrollcycle->delete();
            return response()->json([
                'message' => 'Payroll cycle Deleted Successfully'
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Something went wrong. Please try again later.',
                'details' => $e->getMessage()
            ], 500);
        }
    }

}
