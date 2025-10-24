<?php
namespace App\Http\Controllers\EmployeeController;
use App\Models\EmployeesModel\EmployeeIncrements;
use App\Models\EmployeesModel\EmployeeRecords;
use Carbon\Carbon;
use Illuminate\Support\Facades\Validator;
use App\Http\Controllers\Controller;
use Auth;
use Illuminate\Http\Request;


class EmployeeRecordController extends Controller
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
            $perPage = $request->get('per_page');
            $page = (int) $request->get('page', 1);
            $search = $request->get('search');


            if ($request->input('mode') == 1) {
                $workshift = EmployeeRecords::with('designation', 'department', 'EmployeeIncrement.IncrementType', 'employee')->where('organization_id', $org_id)->get();

                if ($workshift->isEmpty()) {
                    return response()->json([
                        'message' => 'workshift not found.'
                    ], 404);
                }
                $mappedWorkshift = $workshift->map(function ($dep) {
                    return [
                        'employee_name' => trim(
                            ($dep->employee->first_name ?? '') . ' ' .
                            ($dep->employee->middle_name ?? '') . ' ' .
                            ($dep->employee->last_name ?? '')
                        )
                        ,
                        'designation' => $dep->designation->organization_designation_name ?? '',
                        'department' => $dep->department->organization_department_name ?? '',
                        'increment_type' => $dep->EmployeeIncrement->IncrementType->organization_employee_increment_type_name ?? '',
                       'start_date' => $dep->start_date ? Carbon::parse($dep->start_date)->format('Y-m-d') : null,
                         'end_date'   => $dep->end_date ? Carbon::parse($dep->end_date)->format('Y-m-d') : null,
                         'change_reason'=> $dep->change_reason ?? '',
                         'remarks'=> $dep->remarks ?? '',

                    ];
                });
                return response()->json($mappedWorkshift);
            }




            $query = EmployeeRecords::with('designation', 'department', 'EmployeeIncrement.IncrementType', 'employee')->where('organization_id', $org_id);
            if (!empty($search)) {
                $query->where(function ($q) use ($search) {
                    $q->where('change_reason', 'like', '%' . $search . '%')
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
                'message' => 'Employee records fetched successfully',
                'records' => $statusTypes
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
                'organization_designation_id' => 'nullable|integer|exists:organization_designations,organization_designation_id',
                'organization_department_id' => 'nullable|integer|exists:organization_departments,organization_department_id',
                'employee_increment_id' => 'nullable|integer|exists:employee_increments,employee_increment_id',
                'start_date' => 'nullable|date|before_or_equal:today',
                'end_date' => 'nullable|date|after_or_equal:increment_date',
                'change_reason' => ['nullable', 'in:Promotion,Transfer,Demotion,Correction'],
                'remarks' => 'nullable|string|max:500'
            ]);
            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()], 422);
            }
            $data = $request->all();
            EmployeeRecords::create(array_merge($data));
            return response()->json([
                'message' => 'Employee record  Added SuccessFully.',
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Something went wrong. Please try again later.',
                'details' => $e->getMessage()
            ], 500);
        }
    }

    public function show(Request $request, $org_id, $record_id)
    {
        try {
            $request->merge(['organization_id' => $org_id, 'employee_employment_record_id' => $record_id]);
            $validator = Validator::make($request->all(), [
                'organization_id' => 'required|integer|exists:organizations,organization_id',
                'employee_employment_record_id' => 'required|integer|exists:employee_employment_records,employee_employment_record_id',
            ]);
            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()], 422);
            }

            $records = EmployeeRecords::find($record_id);

            $RecordData = $records->toArray();
            $RecordData['start_date'] = $records->start_date ? Carbon::parse($records->start_date)->format('Y-m-d') : null;
            $RecordData['end_date'] = $records->end_date ? Carbon::parse($records->end_date)->format('Y-m-d') : null;

            return response()->json([
                'message' => 'Employee Records data Found',
                'records' => $RecordData
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Something went wrong. Please try again later.',
                'details' => $e->getMessage()
            ], 500);
        }
    }

    public function update(Request $request, $org_id, $record_id)
    {
        try {
            $request->merge([
                'organization_id' => $org_id,
                'employee_employment_record_id' => $record_id
            ]);
            $rules = [
                'organization_id' => 'required|integer|exists:organizations,organization_id',
                'employee_employment_record_id' => 'required|integer|exists:employee_employment_records,employee_employment_record_id',
                'employee_id' => 'nullable|integer|exists:employees,employee_id',
                'organization_designation_id' => 'nullable|integer|exists:organization_designations,organization_designation_id',
                'organization_department_id' => 'nullable|integer|exists:organization_departments,organization_department_id',
                'employee_increment_id' => 'nullable|integer|exists:employee_increments,employee_increment_id',
                'start_date' => 'nullable|date|before_or_equal:today',
                'end_date' => 'nullable|date|after_or_equal:increment_date',
                'change_reason' => ['nullable', 'in:Promotion,Transfer,Demotion,Correction'],
                'remarks' => 'nullable|string|max:500'
            ];
            $validator = Validator::make($request->all(), $rules);
            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()], 422);
            }
            $records = EmployeeRecords::find($record_id);
            $records->update($request->only([
                'organization_id' => 'required|integer|exists:organizations,organization_id',
                'employee_id',
                'organization_designation_id',
                'organization_department_id',
                'employee_increment_id',
                'start_date',
                'end_date',
                'change_reason',
                'remarks'
            ]));

            return response()->json([
                'message' => 'Employee Records updated  Successfully.',
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Something went wrong. Please try again later.',
                'details' => $e->getMessage()
            ], 500);
        }
    }

    public function destroy(Request $request, $org_id, $record_id)
    {
        try {
            $request->merge([
                'organization_id' => $org_id,
                'employee_employment_record_id' => $record_id
            ]);
            $validator = Validator::make($request->all(), [
                'organization_id' => 'required|integer|exists:organizations,organization_id',
                'employee_employment_record_id' => 'required|integer|exists:employee_employment_records,employee_employment_record_id',
            ]);
            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()], 422);
            }
            $records = EmployeeRecords::find($record_id);
            $records->delete();
            return response()->json([
                'message' => 'Employee records Deleted Successfully'
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Something went wrong. Please try again later.',
                'details' => $e->getMessage()
            ], 500);
        }
    }
}
