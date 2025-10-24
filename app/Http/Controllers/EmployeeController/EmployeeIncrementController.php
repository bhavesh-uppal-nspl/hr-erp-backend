<?php
namespace App\Http\Controllers\EmployeeController;
use App\Models\EmployeesModel\EmployeeIncrements;
use Carbon\Carbon;
use Illuminate\Support\Facades\Validator;
use App\Http\Controllers\Controller;
use Auth;
use Illuminate\Http\Request;


class EmployeeIncrementController extends Controller
{

     public function index(Request $request, $org_id)
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
            ]);
            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()], 422);
            }


            if ($request->input('mode') == 1) {
                $increments = EmployeeIncrements::with( 'IncrementType','employee')->where('organization_id', $org_id)->get();

                
                $mappedIncrements = $increments->map(function ($dep) {
                    return [
                            'employee_name' => $dep->employee
        ? trim(($dep->employee->first_name ?? '') . ' ' . ($dep->employee->middle_name ?? '') . ' ' . ($dep->employee->last_name ?? ''))
        : '',
         'Increment_Type'=>$dep->IncrementType->employee_increment_type_name ?? '',
                        'increment_amount'=>$dep->increment_amount ?? '',
                        'previous_ctc_amount' => $dep->previous_ctc_amount ?? '',
                        'increment_percentage' => $dep->increment_percentage ?? '',
                        'new_ctc_amount' => $dep->new_ctc_amount ?? '',
                        'increment_date' => $dep->increment_date ?? '',
                        'effective_date' => $dep->effective_date  ?? '',
                        'remarks' => $dep->remarks ?? '',
                    ];
                });
                return response()->json($mappedIncrements);
            }

            $perPage = $request->get('per_page');
            $page = (int) $request->get('page', 1);
            $search = $request->get('search');

            $query = EmployeeIncrements::with('IncrementType','employee')->where('organization_id', $org_id);
            if (!empty($search)) {
                $query->where(function ($q) use ($search) {
                    $q->where('attendance_break_name', 'like', '%' . $search . '%')
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
                'message' => 'Employee Increments fetched successfully',
                'incrementdata' => $statusTypes
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
            $request->merge(['organization_id' => $org_id]);
            $validator = Validator::make($request->all(), [
                'organization_id' => 'required|integer|exists:organizations,organization_id',
                'employee_id' => 'nullable|integer|exists:employees,employee_id',
                'organization_employee_increment_type_id' => 'nullable|integer|exists:organization_employee_increment_types,organization_employee_increment_type_id',
                'previous_ctc_amount' => 'nullable|numeric|min:0|max:9999999999.99',
                'increment_percentage' => 'nullable|numeric|min:0|max:100',
                'increment_amount' => 'nullable|numeric|min:0|max:9999999999.99',
                'new_ctc_amount' => 'nullable|numeric|min:0|max:9999999999.99',
                'increment_date' => 'nullable|date|before_or_equal:today',
                'effective_date' => 'nullable|date|after_or_equal:increment_date',
                'remarks' => 'nullable|string|max:500'
            ]);
            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()], 422);
            }
            $data = $request->all();
            EmployeeIncrements::create(array_merge($data));
            return response()->json([
                'message' => 'Employee Incremenst  Added SuccessFully.',
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Something went wrong. Please try again later.',
                'details' => $e->getMessage()
            ], 500);
        }
    }

    public function show(Request $request, $org_id, $increment_id)
    {
        try {
            $request->merge(['organization_id' => $org_id, 'employee_increment_id' => $increment_id]);
            $validator = Validator::make($request->all(), [
                'organization_id' => 'required|integer|exists:organizations,organization_id',
                'employee_increment_id' => 'required|integer|exists:employee_increments,employee_increment_id',
            ]);
            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()], 422);
            }

            $increments = EmployeeIncrements::find($increment_id);
          
            $incrementData = $increments->toArray();
            $incrementData['increment_date'] = $increments->increment_date ? Carbon::parse($increments->increment_date)->format('Y-m-d') : null;
            $incrementData['effective_date'] = $increments->effective_date ? Carbon::parse($increments->effective_date)->format('Y-m-d') : null;
        
            return response()->json([
                'message' => 'Employee increments data Found',
                'increments' => $incrementData
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Something went wrong. Please try again later.',
                'details' => $e->getMessage()
            ], 500);
        }
    }

    public function update(Request $request, $org_id, $increment_id)
    {
        try {
            $request->merge([
                'organization_id' => $org_id,
                'employee_increment_id' => $increment_id
            ]);
            $rules = [
                 'organization_id' => 'required|integer|exists:organizations,organization_id',
                   'employee_increment_id' => 'required|integer|exists:employee_increments,employee_increment_id',
                'employee_id' => 'nullable|integer|exists:employees,employee_id',
                'organization_employee_increment_type_id' => 'nullable|integer|exists:organization_employee_increment_types,organization_employee_increment_type_id',
                'previous_ctc_amount' => 'nullable|numeric|min:0|max:9999999999.99',
                'increment_percentage' => 'nullable|numeric|min:0|max:100',
                'increment_amount' => 'nullable|numeric|min:0|max:9999999999.99',
                'new_ctc_amount' => 'nullable|numeric|min:0|max:9999999999.99',
                'increment_date' => 'nullable|date|before_or_equal:today',
                'effective_date' => 'nullable|date|after_or_equal:increment_date',
                'remarks' => 'nullable|string|max:500'
            ];
            $validator = Validator::make($request->all(), $rules);
            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()], 422);
            }
            $increments = EmployeeIncrements::find($increment_id);
            $increments->update($request->only([
                'organization_id',
                'employee_id',
                'organization_employee_increment_type_id',
                'previous_ctc_amount' ,
                'increment_percentage',
                'increment_amount',
                'new_ctc_amount',
                'increment_date',
                'effective_date',
                'remarks'
            ]));

            return response()->json([
                'message' => 'Employee Increments updated  Successfully.',
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Something went wrong. Please try again later.',
                'details' => $e->getMessage()
            ], 500);
        }
    }

    public function destroy(Request $request, $org_id, $increment_id)
    {
        try {
            $request->merge([
                'organization_id' => $org_id,
                'employee_increment_id' => $increment_id
            ]);
            $validator = Validator::make($request->all(), [
                'organization_id' => 'required|integer|exists:organizations,organization_id',
                'employee_increment_id' => 'required|integer|exists:employee_increments,employee_increment_id',
            ]);
            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()], 422);
            }
            $increments = EmployeeIncrements::find($increment_id);
            $increments->delete();
            return response()->json([
                'message' => 'Employee increments Deleted Successfully'
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Something went wrong. Please try again later.',
                'details' => $e->getMessage()
            ], 500);
        }
    }
}
