<?php

namespace App\Http\Controllers\InternController;
use App\Models\InterModel\InternStipend;
use Auth;
use Illuminate\Support\Facades\Validator;
use App\Http\Controllers\Controller;
use Exception;
use Illuminate\Http\Request;
class InternStipendController extends Controller
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
                $stipend = InternStipend::with('Intern')->where('organization_id', $org_id)->get();
                if ($stipend->isEmpty()) {
                    return response()->json([
                        'message' => 'workshift not found.'
                    ], 404);
                }
                $mappedStipend = $stipend->map(function ($dep) {
                    return [
                        'intern_name' => trim(($dep->Intern->first_name ?? '') . ' ' . ($dep->Intern->last_name ?? '')),

                        'stipend_type' => $dep->stipend_type ?? '',
                        'stipend_amount' => $dep->stipend_amount ?? '',
                        'currency_code' => $dep->currency_code ?? '',
                        'payment_cycle' => $dep->payment_cycle ?? '',
                        'effective_start_date' => $dep->effective_start_date  ?? '',
                        'effective_end_date' => $dep->effective_end_date  ?? '',
                        'is_active' => $dep->is_active  ?? '',
                        'remarks' => $dep->remarks  ?? '',
                    ];
                });
                return response()->json($mappedStipend);
            }

            $query = InternStipend::with('Intern')->where('organization_id', $org_id);
            $per = $request->input('per_page', 10);
            $search = $request->input('search');
            if ($search) {
                $query->where(function ($q) use ($search) {
                    $q->where('internship_type_name', 'like', "%{$search}%");
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
            $user = Auth::guard('applicationusers')->user();
            $organizationIds = $user->Client->Organization->pluck('organization_id')->toArray();

            if (!in_array($org_id, $organizationIds)) {
                return response()->json([
                    'message' => 'Unauthorized'
                ], 401);
            }

            $request->merge(['organization_id' => $org_id]);
            $validator = Validator::make($request->all(), [
                'organization_id' => 'required|integer|exists:organizations,organization_id',

                'organization_entity_id' => 'nullable|integer|exists:organization_entities,organization_entity_id',
                'stipend_type' => 'nullable|in:Fixed,Hourly,Monthly,Project-Based,Unpaid',
                'stipend_amount' => 'nullable|numeric',
                'currency_code' => 'nullable|string',
                'payment_cycle' => 'nullable|in:One-Time,Monthly,Quarterly,End-of-Internship',
                'effective_start_date' => 'nullable|date',
                'effective_end_date' => 'nullable|date',
                'last_payment_date' => 'nullable|date',
                'next_payment_date' => 'nullable|date',
                'remarks' => 'nullable|string'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'errors' => $validator->errors()
                ], 422);
            }

            $data = $request->all();
            $intership = InternStipend::create($data);
            return response()->json([
                'message' => 'stipend Added Successfully.',
                'intership' => $intership
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Something went wrong. Please try again later.',
                'details' => $e->getMessage()
            ], 500);
        }
    }

    public function show(Request $request, $org_id, $stipend_id)
    {
        try {
            $user = Auth::guard('applicationusers')->user();
            $organizationIds = $user->Client->Organization->pluck('organization_id')->toArray();
            if (!in_array($org_id, $organizationIds)) {
                return response()->json([
                    'messages' => 'unauthorized'
                ], 401);

            }
            $request->merge(['organization_id' => $org_id, 'intern_stipend_id' => $stipend_id]);
            $validator = Validator::make($request->all(), [
                'organization_id' => 'required|integer|exists:organizations,organization_id',
                'intern_stipend_id' => 'required|integer|exists:intern_stipends,intern_stipend_id',
            ]);
            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()], 422);
            }
            $intership = InternStipend::find($stipend_id);
            return response()->json([
                'message' => "Intern Stipend",
                'intership' => $intership
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Something went wrong. Please try again later.',
                'details' => $e->getMessage()
            ], 500);
        }
    }

    public function update(Request $request, $org_id, $stipend_id)
    {
        try {
            $user = Auth::guard('applicationusers')->user();
            $organizationIds = $user->Client->Organization->pluck('organization_id')->toArray();
            if (!in_array($org_id, $organizationIds)) {
                return response()->json([
                    'messages' => 'unauthorized'
                ], 401);

            }
            $request->merge([
                'organization_id' => $org_id,
                'intern_stipend_id' => $stipend_id
            ]);
            $rules = [
                'organization_id' => 'required|integer|exists:organizations,organization_id',
                'intern_stipend_id' => 'required|integer|exists:intern_stipends,intern_stipend_id',
                'stipend_type' => 'nullable|in:Fixed,Hourly,Monthly,Project-Based,Unpaid',
                'stipend_amount' => 'nullable|numeric',
                'currency_code' => 'nullable|string',
                'payment_cycle' => 'nullable|in:One-Time,Monthly,Quarterly,End-of-Internship',
                'effective_start_date' => 'nullable|date',
                'effective_end_date' => 'nullable|date',
                'last_payment_date' => 'nullable|date',
                'next_payment_date' => 'nullable|date',
                'remarks' => 'nullable|string'


            ];
            $validator = Validator::make($request->all(), $rules);
            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()], 422);
            }
            $intership = InternStipend::find($stipend_id);
            $intership->update($request->only([
                'stipend_type',
                'intern_id',
                'stipend_amount',
                'currency_code',
                'payment_cycle',
                'effective_start_date',
                'effective_end_date',
                'is_active',
                'last_payment_date',
                'next_payment_date',
                'remarks',
            ]));
            return response()->json([
                'intership' => $intership
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Something went wrong. Please try again later.',
                'details' => $e->getMessage()
            ], 500);
        }
    }

    public function destroy(Request $request, $org_id, $intern_id)
    {
        try {
            $user = Auth::guard('applicationusers')->user();
            $organizationIds = $user->Client->Organization->pluck('organization_id')->toArray();
            if (!in_array($org_id, $organizationIds)) {
                return response()->json([
                    'messages' => 'unauthorized'
                ], 401);
            }
            $request->merge(['organization_id' => $org_id, 'intern_stipend_id' => $intern_id]);
            $validator = Validator::make($request->all(), [
                'organization_id' => 'required|integer|exists:organizations,organization_id',
                'intern_stipend_id' => 'required|integer|exists:intern_stipends,intern_stipend_id',
            ]);
            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()], 422);
            }
            $intership = InternStipend::find($intern_id);
            $intership->delete();
            return response()->json([
                'message' => 'Intership Type Deleted SuccessFully !'
            ], 200); // or just remove 200 — it's the default

        } catch (Exception $e) {
            if ($e instanceof \Illuminate\Database\QueryException && $e->errorInfo[1] == 1451) {
                // Foreign key constraint violation
                return response()->json([
                    'error' => 'Cannot delete Business Unit type because it is linked with other records. Please delete dependent records first.'
                ], 409); // 409 Conflict
            }

            // For other exceptions
            return response()->json([
                'error' => 'Failed to delete ownership type.',
                'exception' => $e->getMessage() // Optional: remove in production
            ], 500);
        }
    }
}
