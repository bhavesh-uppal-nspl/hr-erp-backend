<?php

namespace App\Http\Controllers\EMSControllers;

use App\Http\Controllers\Controller;
use App\Models\EMSModels\Certificates;
use App\Models\EMSModels\Certificate;
use App\Models\EMSModels\TrainingProgram;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class CertificatesController extends Controller
{

    public function index(Request $request)
    {

        try {
            $perPage = $request->get('per_page', 10); // Keep as string for "all" comparison
            $page = (int) $request->get('page', 1);
            $search = $request->get('search');
            $organizationId = $request->get('organization_id');

            $query = Certificates::with('student', 'admission', 'batch', 'trainingProgram');

            // Filter by organization_id
            if (!empty($organizationId)) {
                $query->where('organization_id', $organizationId);
            }

            // Search filter
            if (!empty($search)) {
                $query->where(function ($q) use ($search) {
                    $q->where('first_name', 'like', '%' . $search . '%')
                        ->orWhere('gender', 'like', '%' . $search . '%')
                        ->orWhere('certificate_name', 'like', '%' . $search . '%');
                });
            }

            // Handle pagination or return all
            $certificate = ($perPage === 'all')
                ? $query->get()
                : $query->paginate((int) $perPage, ['*'], 'page', $page);

            return response()->json([
                'message' => 'Certificate fetched successfully',
                'data' => $certificate
            ]);
        } catch (\Exception $e) {
            \Log::error('Error fetching Certificate: ' . $e->getMessage());
            return response()->json(['message' => 'Failed to fetch Certificate'], 500);
        }

    }


    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'organization_id' => 'required|integer|exists:organizations,organization_id',
            'organization_entity_id' => 'sometimes|nullable|integer|exists:organization_entities,organization_entity_id',

            'organization_ems_student_id' => 'required|integer|exists:organization_ems_students,organization_ems_student_id',
            'organization_ems_admission_id' => 'required|integer|exists:organization_ems_admissions,organization_ems_admission_id',
            'organization_ems_batch_id' => 'sometimes|nullable|integer|exists:organization_ems_batches,organization_ems_batch_id',
            'training_program_id' => 'required|integer|exists:organization_ems_training_programs,organization_ems_training_program_id',
            'certificate_number' => 'required|string|max:50|unique:organization_ems_certificates,certificate_number',
            'issue_date' => 'required|date',
            'valid_until' => 'nullable|date|after_or_equal:issue_date',
            'certificate_status' => 'sometimes|in:Issued,Revoked,Expired,Reissued',
            'certificate_name' => 'nullable|string|max:150',
            'remarks' => 'nullable|string|max:500',

        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        $data = $validator->validated();

        if ($request->hasFile('certificate_url') && $request->file('certificate_url')->isValid()) {
            $file = $request->file('certificate_url');
            $path = $file->store('certificates', 'public');
            $data['certificate_url'] = basename($path);
        } else {
            $data['certificate_url'] = $request->input('certificate_url');
        }

        if ($request->hasFile('qr_code_url') && $request->file('qr_code_url')->isValid()) {
            $file = $request->file('qr_code_url');
            $path = $file->store('qr_certificates', 'public');
            $data['qr_code_url'] = basename($path);
        } else {
            $data['qr_code_url'] = $request->input('qr_code_url');
        }

        // Generate Unique Certificate ID (e.g. STU202510050001)
        $uniqueCertificateId = 'CER' . date('Ymd') . rand(1000, 9999);
        while (\DB::table('organization_ems_certificates')->where('certificate_number', $uniqueCertificateId)->exists()) {
            $uniqueCertificateId = 'CER' . date('Ymd') . rand(1000, 9999);
        }
        $data['certificate_number'] = $uniqueCertificateId;

        // Default Certificate Status
        if (!$request->has('certificate_status')) {
            $data['certificate_status'] = 'Issued';
        }

        try {
            $certificate = Certificates::create($data);

            return response()->json([
                'success' => true,
                'message' => 'Certificate created successfully.',
                'data' => $certificate,
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to create Certificate.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }


    public function show($id)
    {
        try {
            $certificate = Certificates::with('student', 'admission', 'batch', 'trainingProgram')->findOrFail($id);

            return response()->json([
                'success' => true,
                'data' => $certificate,
            ]);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Certificate not found.',
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error fetching Certificate.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }


    public function update(Request $request, $id)
    {
        $certificate = Certificates::find($id);

        if (!$certificate) {
            return response()->json([
                'success' => false,
                'message' => 'Certificate not found.',
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'organization_id' => 'required|integer|exists:organizations,organization_id',
            'organization_entity_id' => 'sometimes|nullable|integer|exists:organization_entities,organization_entity_id',
            'organization_ems_student_id' => 'sometimes|integer|exists:organization_ems_students,organization_ems_student_id',
            'organization_ems_admission_id' => 'sometimes|integer|exists:organization_ems_admissions,organization_ems_admission_id',
            'organization_ems_batch_id' => 'sometimes|nullable|integer|exists:organization_ems_batches,organization_ems_batch_id',
            'training_program_id' => 'sometimes|integer|exists:organization_ems_training_programs,organization_ems_training_program_id',
            'certificate_number' => 'sometimes|string|max:50|unique:organization_ems_certificates,certificate_number,' . $id . ',organization_ems_certificate_id',
            'issue_date' => 'sometimes|date',
            'valid_until' => 'nullable|date|after_or_equal:issue_date',
            'certificate_status' => 'sometimes|in:Issued,Revoked,Expired,Reissued',
            'certificate_name' => 'nullable|string|max:150',
            'remarks' => 'nullable|string|max:500',

        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }


        $data = $validator->validated();


        if ($request->hasFile('certificate_url') && $request->file('certificate_url')->isValid()) {
            $file = $request->file('certificate_url');

            if ($certificate->certificate_url) {
                $oldFileName = basename($certificate->certificate_url);
                Storage::disk('public')->delete('certificates/' . $oldFileName);
            }


            $path = $file->store('certificates', 'public');
            $data['certificate_url'] = basename($path);
        } else {

            $data['certificate_url'] = $certificate->certificate_url;
        }

        if ($request->hasFile('qr_code_url') && $request->file('qr_code_url')->isValid()) {
            $file = $request->file('qr_code_url');

            if ($certificate->qr_code_url) {
                $oldFileName = basename($certificate->qr_code_url);
                Storage::disk('public')->delete('qr_certificates/' . $oldFileName);
            }


            $path = $file->store('qr_certificates', 'public');
            $data['qr_code_url'] = basename($path);
        } else {

            $data['qr_code_url'] = $certificate->qr_code_url;
        }


        try {
            // Exclude Certificate_id from update
            $certificate->fill($$data);
            $certificate->save();

            return response()->json([
                'success' => true,
                'message' => 'Certificate updated successfully.',
                'data' => $certificate,
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update Certificate.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }


    public function destroy($id)
    {
        try {
            $certificate = Certificates::findOrFail($id);
            $certificate->delete();

            return response()->json([
                'success' => true,
                'message' => 'Program deleted successfully.',
            ]);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Program not found.',
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete Program.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

}
