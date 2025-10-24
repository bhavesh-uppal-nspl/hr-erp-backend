<?php

namespace App\Http\Controllers\InternController;
use App\Models\EmployeesModel\EmployeeDocumentSectionLink;
use App\Models\InterModel\InternAddress;
use App\Models\InterModel\InternBankAccount;
use App\Models\InterModel\InternContact;
use App\Models\InterModel\InternDocument;
use App\Models\InterModel\InternEducation;
use App\Models\InterModel\InternExperience;
use App\Models\InterModel\InternFamilyMember;
use App\Models\InterModel\InternLanguages;
use App\Models\InterModel\InternMedical;
use App\Models\InterModel\Interns;
use Illuminate\Support\Facades\Validator;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Auth;
use Illuminate\Validation\Rule;
use Storage;

class InterFormController extends Controller
{

    private function getValidationRules($section)
    {
        switch ($section) {
            case 'education':
                return [
                    'organization_education_level_id' => 'sometimes|integer|exists:organization_education_levels,organization_education_level_id',
                    'organization_education_degree_id' => 'nullable|integer|exists:organization_education_degrees,organization_education_degree_id',
                    'organization_education_stream_id' => 'nullable|integer|exists:organization_education_streams,organization_education_stream_id',
                    'organization_education_level_degree_stream_id' => 'nullable|integer',
                    'institute_name' => 'sometimes|string|max:100',
                    'board_name' => 'nullable|string|max:100',
                    'marks_percentage' => 'nullable|numeric|min:0|max:100',
                    'year_of_passing' => 'nullable|digits:4',
                    'is_pursuing' => 'nullable|boolean',
                ];

            case 'language':
                return [
                    'organization_language_id' => 'sometimes|integer|exists:organization_languages,organization_language_id',
                    'can_read' => 'sometimes|boolean',
                    'can_write' => 'sometimes|boolean',
                    'can_speak' => 'sometimes|boolean',
                    'is_native' => 'nullable|boolean',
                    'description' => 'nullable|string|max:255',
                ];

            case 'family':
                return [
                    'full_name' => 'sometimes|string|max:100',
                    'relationship_type' => 'sometimes|string|max:50',
                    'phone_number' => 'nullable|string|max:20',
                    'date_of_birth' => 'nullable|date|before:today',
                    'email' => 'nullable|email|max:100',
                    'gender' => 'nullable|String|max:100',
                    'is_dependent' => 'nullable|boolean',
                ];

            case 'address':
                return [
                 
                    'address_line1' => 'nullable|string|max:255',
                    'address_line2' => 'nullable|string|max:255',
                    'address_line3' => 'nullable|string|max:255',
                    'general_city_id' => 'sometimes|integer|exists:general_cities,general_city_id',
                    'postal_code' => 'sometimes|string|max:10',

                ];

            case 'contact':
                return [
                    'personal_phone_number' => 'sometimes|string|max:15',
                    'alternate_phone_number' => 'nullable|string|max:15',
                    'personal_email' => 'sometimes|email|max:100',
                    'emergency_contact_name' => 'nullable|string|max:100',
                    'emergency_contact_relation' => 'nullable|string|max:255',
                    'emergency_contact_phone' => 'nullable|string|max:15',
                 
                ];

            case 'experience':
                return [
                    'organization_name' => 'sometimes|string|max:100',
                    'work_title' => 'nullable|string|max:100',
                    'work_mode' => 'nullable|string|max:100',
                    'experience_type' => 'nullable|string|max:50',
                    'compensation_status' => 'nullable|string|max:50',
                    'compensation_amount'=>'nullable|numeric',
                    'general_industry_id' => 'nullable|integer|exists:general_industries,general_industry_id',
                    'start_date' => 'sometimes|date',
                    'end_date' => 'nullable|date|after_or_equal:start_date',
                    'currency_code' => 'nullable|string|max:10',
                    'location' => 'nullable|string|max:100',
                    'reporting_manager_name' => 'nullable|string|max:100',
                    'reporting_manager_contact' => 'nullable|string|max:20',
                    'description' => 'nullable|string|max:500',
                    'is_verified' => 'nullable|boolean',
                    'verified_by' => 'nullable|string|max:100',
                    'verification_date' => 'nullable|date',
                    'verification_notes' => 'nullable|string|max:500',
                ];

            case 'medical':
                return [
                    'blood_group' => 'nullable|in:A+,A-,B+,B-,AB+,AB-,O+,O-,Unknown',
                    'allergies' => 'nullable|string|max:255',
                    'diseases' => 'nullable|string|max:255',
                    'disability_status' => 'nullable|string|max:100',
                    'disability_description' => 'nullable|string|max:255',
                    'is_fit_for_duty' => 'nullable|boolean',
                    'last_health_check_date' => 'nullable|date',
                    'medical_notes' => 'nullable|string|max:500',
                ];


            case 'payment_method':
                return [
                    'account_holder_name' => 'nullable|string|max:100',
                    'bank_name' => 'nullable|string|max:100',
                    'branch_name' => 'nullable|string|max:100',
                    'ifsc_code' => 'nullable|string|max:20',
                    'swift_code' => 'nullable|string|max:20',
                    'iban_number' => 'nullable|string|max:50',
                    'account_number' => 'nullable|string|max:30',
                    // 'account_type' => 'nullable|string|max:20',
                    'is_primary' => 'nullable|boolean',
                    'upi_id' => 'nullable|string|max:255',
                    'wallet_id' => 'nullable|string|max:100',
                 
                   
                ];

            case 'document':
                return [
                    'intern_document_type_id' => 'nullable|integer|exists:employee_document_types,employee_document_type_id',
                    'document_name' => 'nullable|string|max:100',
                    // 'document_url' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:2048',
                    'organization_employee_profile_section_id' => 'nullable|array',
                    'organization_employee_profile_section_id.*' => 'integer',
                    'organization_entity_id' => 'nullable|integer',


                ];

            default:
                return [];
        }
    }
  

    // for online upload 
    // public function store1(Request $request, $org_id)
    // {
    //     try {


    //         $user = Auth::guard('applicationusers')->user();
    //         $organizationIds = $user->Client->Organization->pluck('organization_id')->toArray();

    //         if (!in_array($org_id, $organizationIds)) {
    //             return response()->json(['message' => 'Unauthenticated'], 401);
    //         }

    //         $isUpdate = $request->has('intern_id') && !empty($request->intern_id);

    //         // Validation rules
    //         $coreValidation = Validator::make($request->all(), [
    //             'intern_code' => [
    //                 $isUpdate ? 'sometimes' : 'required',
    //                 'string',
    //                 'max:10',
    //                 Rule::unique('interns')->where(fn($q) => $q->where('organization_id', $org_id))
    //                     ->ignore($request->intern_id, 'intern_id')
    //             ],
    //             'first_name' => $isUpdate ? 'sometimes|string|max:30' : 'required|string|max:30',
    //             'middle_name' => 'nullable|string|max:30',
    //             'last_name' => 'nullable|string|max:30',
    //             'date_of_birth' => 'nullable|date|before:today',
    //             'gender' => 'nullable|in:Male,Female,Other',
    //             'marital_status' => 'nullable|in:Single,Married,Divorced,Widowed',
    //             'organization_unit_id' => 'nullable|integer|exists:organization_units,organization_unit_id',
    //             // 'organization_designation_id' => 'nullable|integer|exists:organization_designations,organization_designation_id',
    //             'organization_internship_type_id' => 'nullable|integer|exists:organization_internship_types,organization_internship_type_id',
    //             'organization_department_location_id' => 'nullable|integer|exists:organization_department_locations,organization_department_location_id',
    //             'organization_internship_status_id' => 'nullable|integer|exists:organization_internship_statuses,organization_internship_status_id',
    //             'internship_start_date' => 'nullable|date',
    //            'internship_end_date' => 'nullable|date|after_or_equal:internship_start_date',
    //             'mentor_employee_id' => 'nullable|integer|exists:employees,employee_id',
    //             'organization_user_id' => 'nullable|integer|exists:organization_users,organization_user_id',
    //         ]);

    //         if ($coreValidation->fails()) {
    //             return response()->json(['errors' => $coreValidation->errors(), 'section' => 'intern'], 422);
    //         }

    //         if ($isUpdate) {
    //             // UPDATE
    //             $intern = Interns::where('organization_id', $org_id)
    //                 ->where('intern_id', $request->intern_id)
    //                 ->first();

    //             if (!$intern) {
    //                 return response()->json(['message' => 'Intern not found.'], 404);
    //             }

    //             $intern->fill($request->except('profile_image_url', 'intern_id'));

    //             if ($request->hasFile('profile_image_url')) {
    //                 $file = $request->file('profile_image_url');

    //                 if ($file->isValid()) {
    //                     // Delete old file if it exists
    //                     if ($intern->profile_image_url) {
    //                         Storage::disk('public')->delete('interns/' . intern->profile_image_url);
    //                     }
    //                     $path = $file->store('interns', 'public');
    //                     $intern['profile_image_url'] = basename($path); 
    //                 } else {
    //                     return response()->json(['error' => 'Uploaded logo file is invalid.'], 400);
    //                 }
    //             }

    //             $intern->save();


    //             // Handle Employee Documents - UPDATE section
    //             if ($request->has('document')) {
    //                 foreach ($request->document as $index => $doc) {
    //                     $doc['organization_id'] = $org_id;
    //                     $doc['employee_id'] = $intern->intern_id;

    //                     // Handle file upload using array-based checking
    //                     $documentFiles = $request->file('document', []);
    //                     if (isset($documentFiles[$index]['document_url']) && $documentFiles[$index]['document_url'] instanceof \Illuminate\Http\UploadedFile) {
    //                         $file = $documentFiles[$index]['document_url'];
    //                         if ($file->isValid()) {
    //                             $path = $file->store('documents', 'public');
    //                             $doc['document_url'] = $path;
    //                         }
    //                     }

    //                     // update logic 
    //                     if (!empty($doc['intern_document_id'])) {
    //                         $existingDoc = InternDocument::where('employee_document_id', $doc['employee_document_id'])
    //                             ->where('organization_id', $org_id)
    //                             ->first();
    //                         if ($existingDoc) {
    //                             $existingDoc->update($doc);
    //                         } else {
    //                             InternDocument::create($doc);
    //                         }
    //                     } else {
    //                         $createdDoc = InternDocument::create($doc);
    //                         $doc['employee_document_id'] = $createdDoc->employee_document_id;
    //                     }

    //                     // Handle profile section links
    //                     if (!empty($doc['organization_employee_profile_section_id'])) {
    //                         EmployeeDocumentSectionLink::where('employee_document_id', $doc['employee_document_id'])->delete();
    //                         foreach ($doc['organization_employee_profile_section_id'] as $sectionId) {
    //                             EmployeeDocumentSectionLink::create([
    //                                 'employee_document_id' => $doc['employee_document_id'],
    //                                 'organization_id' => $org_id,
    //                                 'organization_employee_profile_section_id' => $sectionId,
    //                                 'organization_entity_id' => $doc['organization_entity_id'] ?? null,
    //                             ]);
    //                         }
    //                     }
    //                 }
    //             }



    //             $education = InternEducation::where("intern_id", $intern->intern_id)->get();
    //             $language = InternLanguages::where("intern_id", $intern->intern_id)->get();
    //             $family = InternFamilyMember::where("intern_id", $intern->intern_id)->get();
    //             $address = InternAddress::where("intern_id", $intern->intern_id)->get();
    //             $contact = InternContact::where("intern_id", $intern->intern_id)->get();
    //             $experience = InternExperience::where("intern_id", $intern->intern_id)->get();
    //             $medical = InternMedical::where("intern_id", $intern->intern_id)->get();
    //             $payment_method = InternBankAccount::where("intern_id", $intern->intern_id)->get();
    //             $document = InternDocument::where("intern_id", $intern->intern_id)->get();

    //             return response()->json([
    //                 'message' => 'Intern updated successfully.',
    //                 'intern' => $this->transformEmployee($intern),
    //                 'education' => $education,
    //                 'language' => $language,
    //                 'family' => $family,
    //                 'address' => $address,
    //                 'contact' => $contact,
    //                 'experience' => $experience,
    //                 'medical' => $medical,
    //                 'payment_method' => $payment_method,
    //                 'document' => $document
    //             ], 200);

    //         } else {
    //             // CREATE
    //             $intern = new Interns();


    //             // for document   that is to be added   
    //             $intern->organization_id = $org_id;
    //             $intern->intern_code = $request->intern_code;
    //             $intern->first_name = $request->first_name;
    //             $intern->middle_name = $request->middle_name;
    //             $intern->last_name = $request->last_name;
    //             $intern->date_of_birth = $request->date_of_birth;
    //             $intern->gender = $request->gender;
    //             $intern->marital_status = $request->marital_status;
    //             $intern->organization_unit_id = $request->organization_unit_id;
    //             $intern->organization_department_location_id = $request->organization_department_location_id;
    //             // $intern->organization_designation_id = $request->organization_designation_id;
    //             $intern->organization_internship_type_id  = $request->organization_internship_type_id ;
    //             $intern->organization_internship_status_id = $request->organization_internship_status_id;
    //             $intern->internship_start_date = $request->internship_start_date;
    //             $intern->internship_end_date = $request->internship_end_date;
    //             $intern->mentor_employee_id = $request->mentor_employee_id;
    //             $intern->organization_user_id = $request->organization_user_id;

    //             if ($request->hasFile('profile_image_url') && $request->file('profile_image_url')->isValid()) {
    //                 $file = $request->file('profile_image_url');
    //                 $path = $file->store('interns', 'public');
    //                 $intern['profile_image_url'] = basename($path); // Store only relative path
    //             } else {
    //                 $intern['profile_image_url'] = $request->input('profile_image_url');
    //             }

    //             $intern->save();

    //             // Handle Employee Documents - CREATE section
    //             if ($request->has('document')) {
    //                 foreach ($request->document as $index => $doc) {
    //                     $doc['organization_id'] = $org_id;
    //                     $doc['intern_id'] = $intern->intern_id;

    //                     // Handle file upload using array-based checking
    //                     $documentFiles = $request->file('document', []);
    //                     if (isset($documentFiles[$index]['document_url']) && $documentFiles[$index]['document_url'] instanceof \Illuminate\Http\UploadedFile) {
    //                         $file = $documentFiles[$index]['document_url'];
    //                         if ($file->isValid()) {
    //                             $path = $file->store('documents', 'public');
    //                             $doc['document_url'] = $path;
    //                         }
    //                     }

    //                     if (!empty($doc['employee_document_id'])) {
    //                         $existingDoc = InternDocument::where('employee_document_id', $doc['employee_document_id'])
    //                             ->where('organization_id', $org_id)
    //                             ->first();
    //                         if ($existingDoc) {
    //                             $existingDoc->update($doc);
    //                         } else {
    //                             InternDocument::create($doc);
    //                         }
    //                     } else {
    //                         $createdDoc = InternDocument::create($doc);
    //                         $doc['employee_document_id'] = $createdDoc->employee_document_id;
    //                     }

    //                     if (!empty($doc['organization_employee_profile_section_id'])) {
    //                         EmployeeDocumentSectionLink::where('employee_document_id', $doc['employee_document_id'])->delete();
    //                         foreach ($doc['organization_employee_profile_section_id'] as $sectionId) {
    //                             EmployeeDocumentSectionLink::create([
    //                                 'employee_document_id' => $doc['employee_document_id'],
    //                                 'organization_id' => $org_id,
    //                                 'organization_employee_profile_section_id' => $sectionId,
    //                                 'organization_entity_id' => $doc['organization_entity_id'] ?? null,
    //                             ]);
    //                         }
    //                     }
    //                 }
    //             }


    //             return response()->json([
    //                 'message' => 'Intern added successfully.',
    //                 'intern' => $this->transformEmployee($intern),
    //                 'education' => [],
    //                 'language' => [],
    //                 'family' => [],
    //                 'address' => [],
    //                 'contact' => [],
    //                 'experience' => [],
    //                 'medical' => [],
    //                 'payment_method' => [],
    //                 'document' => []
    //             ], 201);
    //         }

    //     } catch (\Exception $e) {
    //         return response()->json([
    //             'error' => 'Something went wrong.',
    //             'details' => $e->getMessage(),
    //         ], 500);
    //     }
    // } 





    public function store1(Request $request, $org_id)
    {
        try {


            $user = Auth::guard('applicationusers')->user();
            $organizationIds = $user->Client->Organization->pluck('organization_id')->toArray();

            if (!in_array($org_id, $organizationIds)) {
                return response()->json(['message' => 'Unauthenticated'], 401);
            }

            $isUpdate = $request->has('intern_id') && !empty($request->intern_id);

            // Validation rules
            $coreValidation = Validator::make($request->all(), [
                'intern_code' => [
                    $isUpdate ? 'sometimes' : 'required',
                    'string',
                    'max:10',
                    Rule::unique('interns')->where(fn($q) => $q->where('organization_id', $org_id))
                        ->ignore($request->intern_id, 'intern_id')
                ],
                'first_name' => $isUpdate ? 'sometimes|string|max:30' : 'required|string|max:30',
                'middle_name' => 'nullable|string|max:30',
                'last_name' => 'nullable|string|max:30',
                'date_of_birth' => 'nullable|date|before:today',
                'gender' => 'nullable|in:Male,Female,Other',
                'marital_status' => 'nullable|in:Single,Married,Divorced,Widowed',
                'organization_unit_id' => 'nullable|integer|exists:organization_units,organization_unit_id',
                // 'organization_designation_id' => 'nullable|integer|exists:organization_designations,organization_designation_id',
                'organization_internship_type_id' => 'nullable|integer|exists:organization_internship_types,organization_internship_type_id',
                'organization_department_location_id' => 'nullable|integer|exists:organization_department_locations,organization_department_location_id',
                'organization_internship_status_id' => 'nullable|integer|exists:organization_internship_statuses,organization_internship_status_id',
                'organization_internship_stage_id' => 'nullable|integer|exists:organization_internship_stages,organization_internship_stage_id',
                'internship_start_date' => 'nullable|date',
                'organization_work_shift_id'=>'nullable|integer|exists:organization_work_shifts,organization_work_shift_id',
               'internship_end_date' => 'nullable|date|after_or_equal:internship_start_date',
                'mentor_employee_id' => 'nullable|integer|exists:employees,employee_id',
                'organization_user_id' => 'nullable|integer|exists:organization_users,organization_user_id',
                'is_paid'=>'nullable|boolean',
               
            ]);

            if ($coreValidation->fails()) {
                return response()->json(['errors' => $coreValidation->errors(), 'section' => 'intern'], 422);
            }

            if ($isUpdate) {
                // UPDATE
                $intern = Interns::where('organization_id', $org_id)
                    ->where('intern_id', $request->intern_id)
                    ->first();

                if (!$intern) {
                    return response()->json(['message' => 'Intern not found.'], 404);
                }

                $intern->fill($request->except('profile_image_url', 'intern_id'));

                if ($request->hasFile('profile_image_url')) {
                    $file = $request->file('profile_image_url');

                    if ($file->isValid()) {
                        // Delete old file if it exists
                        if ($intern->profile_image_url) {
                            Storage::disk('public')->delete($intern->profile_image_url);
                        }
                        $path = $file->store('interns', 'public');
                        $intern['profile_image_url'] = $path; 
                    } else {
                        return response()->json(['error' => 'Uploaded logo file is invalid.'], 400);
                    }
                }

                $intern->save();


                // Handle Employee Documents - UPDATE section
                if ($request->has('document')) {
                    foreach ($request->document as $index => $doc) {
                        $doc['organization_id'] = $org_id;
                        $doc['employee_id'] = $intern->intern_id;

                        // Handle file upload using array-based checking
                        $documentFiles = $request->file('document', []);
                        if (isset($documentFiles[$index]['document_url']) && $documentFiles[$index]['document_url'] instanceof \Illuminate\Http\UploadedFile) {
                            $file = $documentFiles[$index]['document_url'];
                            if ($file->isValid()) {
                                $path = $file->store('documents', 'public');
                                $doc['document_url'] = $path;
                            }
                        }

                        // update logic 
                        if (!empty($doc['intern_document_id'])) {
                            $existingDoc = InternDocument::where('employee_document_id', $doc['employee_document_id'])
                                ->where('organization_id', $org_id)
                                ->first();
                            if ($existingDoc) {
                                $existingDoc->update($doc);
                            } else {
                                InternDocument::create($doc);
                            }
                        } else {
                            $createdDoc = InternDocument::create($doc);
                            $doc['employee_document_id'] = $createdDoc->employee_document_id;
                        }

                        // Handle profile section links
                        if (!empty($doc['organization_employee_profile_section_id'])) {
                            EmployeeDocumentSectionLink::where('employee_document_id', $doc['employee_document_id'])->delete();
                            foreach ($doc['organization_employee_profile_section_id'] as $sectionId) {
                                EmployeeDocumentSectionLink::create([
                                    'employee_document_id' => $doc['employee_document_id'],
                                    'organization_id' => $org_id,
                                    'organization_employee_profile_section_id' => $sectionId,
                                    'organization_entity_id' => $doc['organization_entity_id'] ?? null,
                                ]);
                            }
                        }
                    }
                }



                $education = InternEducation::where("intern_id", $intern->intern_id)->get();
                $language = InternLanguages::where("intern_id", $intern->intern_id)->get();
                $family = InternFamilyMember::where("intern_id", $intern->intern_id)->get();
                $address = InternAddress::where("intern_id", $intern->intern_id)->get();
                $contact = InternContact::where("intern_id", $intern->intern_id)->get();
                $experience = InternExperience::where("intern_id", $intern->intern_id)->get();
                $medical = InternMedical::where("intern_id", $intern->intern_id)->get();
                $payment_method = InternBankAccount::where("intern_id", $intern->intern_id)->get();
                $document = InternDocument::where("intern_id", $intern->intern_id)->get();

                return response()->json([
                    'message' => 'Intern updated successfully.',
                    'intern' => $this->transformEmployee($intern),
                    'education' => $education,
                    'language' => $language,
                    'family' => $family,
                    'address' => $address,
                    'contact' => $contact,
                    'experience' => $experience,
                    'medical' => $medical,
                    'payment_method' => $payment_method,
                    'document' => $document
                ], 200);

            } else {
                // CREATE
                $intern = new Interns();


                // for document   that is to be added   
                $intern->organization_id = $org_id;
                $intern->intern_code = $request->intern_code;
                $intern->first_name = $request->first_name;
                $intern->middle_name = $request->middle_name;
                $intern->last_name = $request->last_name;
                $intern->date_of_birth = $request->date_of_birth;
                $intern->gender = $request->gender;
                $intern->marital_status = $request->marital_status;
                $intern->organization_unit_id = $request->organization_unit_id;
                $intern->organization_department_location_id = $request->organization_department_location_id;
                // $intern->organization_designation_id = $request->organization_designation_id;
                $intern->organization_internship_type_id  = $request->organization_internship_type_id ;
                $intern->organization_internship_status_id = $request->organization_internship_status_id;
                $intern->organization_internship_stage_id = $request->organization_internship_stage_id;
                $intern->organization_work_shift_id = $request->organization_work_shift_id;
                $intern->internship_start_date = $request->internship_start_date;
                $intern->internship_end_date = $request->internship_end_date;
                $intern->mentor_employee_id = $request->mentor_employee_id;
                $intern->organization_user_id = $request->organization_user_id;
                $intern->is_paid = $request->is_paid;

                if ($request->hasFile('profile_image_url') && $request->file('profile_image_url')->isValid()) {
                    $file = $request->file('profile_image_url');
                    $path = $file->store('interns', 'public');
                    $intern['profile_image_url'] = $path; // Store only relative path
                } else {
                    $intern['profile_image_url'] = $request->input('profile_image_url');
                }

                $intern->save();

                // Handle Employee Documents - CREATE section
                if ($request->has('document')) {
                    foreach ($request->document as $index => $doc) {
                        $doc['organization_id'] = $org_id;
                        $doc['intern_id'] = $intern->intern_id;

                        // Handle file upload using array-based checking
                        $documentFiles = $request->file('document', []);
                        if (isset($documentFiles[$index]['document_url']) && $documentFiles[$index]['document_url'] instanceof \Illuminate\Http\UploadedFile) {
                            $file = $documentFiles[$index]['document_url'];
                            if ($file->isValid()) {
                                $path = $file->store('documents', 'public');
                                $doc['document_url'] = $path;
                            }
                        }

                        if (!empty($doc['employee_document_id'])) {
                            $existingDoc = InternDocument::where('employee_document_id', $doc['employee_document_id'])
                                ->where('organization_id', $org_id)
                                ->first();
                            if ($existingDoc) {
                                $existingDoc->update($doc);
                            } else {
                                InternDocument::create($doc);
                            }
                        } else {
                            $createdDoc = InternDocument::create($doc);
                            $doc['employee_document_id'] = $createdDoc->employee_document_id;
                        }

                        if (!empty($doc['organization_employee_profile_section_id'])) {
                            EmployeeDocumentSectionLink::where('employee_document_id', $doc['employee_document_id'])->delete();
                            foreach ($doc['organization_employee_profile_section_id'] as $sectionId) {
                                EmployeeDocumentSectionLink::create([
                                    'employee_document_id' => $doc['employee_document_id'],
                                    'organization_id' => $org_id,
                                    'organization_employee_profile_section_id' => $sectionId,
                                    'organization_entity_id' => $doc['organization_entity_id'] ?? null,
                                ]);
                            }
                        }
                    }
                }


                return response()->json([
                    'message' => 'Intern added successfully.',
                    'intern' => $this->transformEmployee($intern),
                    'education' => [],
                    'language' => [],
                    'family' => [],
                    'address' => [],
                    'contact' => [],
                    'experience' => [],
                    'medical' => [],
                    'payment_method' => [],
                    'document' => []
                ], 201);
            }

        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Something went wrong.',
                'details' => $e->getMessage(),
            ], 500);
        }
    }

    private function transformEmployee($intern)
    {
        $internArray = $intern->toArray();
        $internArray['profile_image_url'] = $intern->profile_image_url
            ? asset('storage/' . $intern->profile_image_url)
            : null;

        return $internArray;
    }


       public function store2(Request $request, $org_id)
    {
        try {

            $sections = [];
            $primaryKeys = [];

            if ($request->has('education'))
                $sections['education'] = InternEducation::class;
            $primaryKeys['education'] = 'intern_education_id';
            if ($request->has('language'))
                $sections['language'] = InternLanguages::class;
            $primaryKeys['language'] = 'intern_language_id';
            if ($request->has('family'))
                $sections['family'] = InternFamilyMember::class;
            $primaryKeys['family'] = 'intern_family_member_id';
            if ($request->has('address'))
                $sections['address'] = InternAddress::class;
            $primaryKeys['address'] = 'intern_address_id';
            if ($request->has('contact'))
                $sections['contact'] = InternContact::class;
            $primaryKeys['contact'] = 'intern_contact_id';
            if ($request->has('experience'))
                $sections['experience'] = InternExperience::class;
            $primaryKeys['experience'] = 'intern_experience_id';
            if ($request->has('medical'))
                $sections['medical'] = InternMedical ::class;
            $primaryKeys['medical'] = 'intern_medical_id';
            if ($request->has('payment_method'))
                $sections['payment_method'] = InternBankAccount::class;
            $primaryKeys['payment_method'] = 'intern_bank_account_id';
            if ($request->has('document'))
                $sections['document'] = InternDocument::class;
            $primaryKeys['document'] = 'intern_document_id';

            $sectionErrors = [];
            $validatedData = [];

            // 1️⃣ Validate entries
            foreach ($sections as $sectionKey => $modelClass) {
                $entries = $request->input($sectionKey);
                $validatedData[$sectionKey] = [];

                if (!is_array($entries))
                    continue;

                foreach ($entries as $index => $entry) {
                    $validator = Validator::make($entry, $this->getValidationRules($sectionKey, $entry));
                    if ($validator->fails()) {
                        $sectionErrors[$sectionKey][$index] = $validator->errors();
                    } else {
                        $validatedData[$sectionKey][] = $entry;
                    }
                }
            }

            if (!empty($sectionErrors)) {
                return response()->json(['errors' => $sectionErrors], 422);
            }

            \DB::beginTransaction();

            foreach ($validatedData as $sectionKey => $entries) {
                $modelClass = $sections[$sectionKey];

                $internId = $entries[0]['intern_id'] ?? null;
                if (!$internId) {
                    return response()->json(['error' => "Intern ID is required in section $sectionKey."], 400);
                }

                // Get existing IDs from DB
                $existing = $modelClass::where('intern_id', $internId)
                    ->where('organization_id', $org_id)
                    ->get();

                $existingById = $existing->keyBy($primaryKeys[$sectionKey]);
                $submittedIds = collect($entries)->pluck($primaryKeys[$sectionKey])->filter()->all();

                // Delete entries not present in current request
                $idsToDelete = $existing->pluck($primaryKeys[$sectionKey])->diff($submittedIds);
                if ($idsToDelete->isNotEmpty()) {
                    $modelClass::whereIn($primaryKeys[$sectionKey], $idsToDelete)->delete();
                }

                foreach ($entries as $index => $entry) {
                    $entry['organization_id'] = $org_id;

                    if (!empty($entry[$primaryKeys[$sectionKey]]) && $existingById->has($entry[$primaryKeys[$sectionKey]])) {
                        $model = $existingById[$entry[$primaryKeys[$sectionKey]]];

                        if ($sectionKey === 'document') {
                            // Handle file upload using the proper array-based checking
                            $documentFiles = $request->file('document', []);
                            if (isset($documentFiles[$index]['document_url']) && $documentFiles[$index]['document_url'] instanceof \Illuminate\Http\UploadedFile) {
                                $file = $documentFiles[$index]['document_url'];
                                if ($file->isValid()) {
                                    $path = $file->store('documents', 'public');
                                    $entry['document_url'] = $path;
                                }
                            } else {
                                // No new file uploaded, keep existing
                                unset($entry['document_url']);
                            }

                            // Handle EmployeeDocumentSectionLink
                            if (!empty($entry['organization_employee_profile_section_id'])) {
                                EmployeeDocumentSectionLink::where('employee_document_id', $entry['employee_document_id'])->delete();
                                foreach ($entry['organization_employee_profile_section_id'] as $sectionId) {
                                    EmployeeDocumentSectionLink::create([
                                        'employee_document_id' => $entry['employee_document_id'],
                                        'organization_id' => $org_id,
                                        'organization_employee_profile_section_id' => $sectionId,
                                        'organization_entity_id' => $entry['organization_entity_id'] ?? null,
                                    ]);
                                }
                            }
                        }

                        $model->fill($entry)->save();

                    } else {
                        // Create new
                        if ($sectionKey === 'document') {
                            // Handle file upload using the proper array-based checking
                            $documentFiles = $request->file('document', []);
                            if (isset($documentFiles[$index]['document_url']) && $documentFiles[$index]['document_url'] instanceof \Illuminate\Http\UploadedFile) {
                                $file = $documentFiles[$index]['document_url'];
                                if ($file->isValid()) {
                                    $path = $file->store('documents', 'public');
                                    $entry['document_url'] = $path;
                                }
                            }
                        }

                        $model = $modelClass::create($entry);

                        if ($sectionKey === 'document' && !empty($entry['organization_employee_profile_section_id'])) {
                            foreach ($entry['organization_employee_profile_section_id'] as $sectionId) {
                                EmployeeDocumentSectionLink::create([
                                    'employee_document_id' => $model->employee_document_id,
                                    'organization_id' => $org_id,
                                    'organization_employee_profile_section_id' => $sectionId,
                                    'organization_entity_id' => $entry['organization_entity_id'] ?? null,
                                ]);
                            }
                        }
                    }
                }
            }

            \DB::commit();
            return response()->json([
                'message' => 'Intern Section  saved successfully.',
            ], 201);

        } catch (\Exception $e) {
            \DB::rollBack();
            return response()->json([
                'error' => 'Something went wrong.',
                'details' => $e->getMessage(),
            ], 500);
        }
    }

    // it only get the Employee data  of all models 
    public function update(Request $request, $org_id, $intern_id)
    {
        try {
            $user = Auth::guard('applicationusers')->user();
            $organizationIds =
                $user->Client->Organization->pluck('organization_id')->toArray();

            if (!in_array($org_id, $organizationIds)) {
                return response()->json(['message' => 'Unauthenticated'], 401);
            }

            // Main employee data
            $intern = Interns::with('departmentLocation.department')->where('intern_id', $intern_id)
                ->where('organization_id', $org_id)
                ->first();

            if (!$intern) {
                return response()->json([
                    'message' => 'Intern not found.'
                ], 404);
            }


            $education = InternEducation::with('degree','Stream','Level')->where('intern_id', $intern_id)
                ->where('organization_id', $org_id)->get();
            $language = InternLanguages::where('intern_id', $intern_id)
                ->where('organization_id', $org_id)->get();
            $family = InternFamilyMember::where('intern_id', $intern_id)
                ->where('organization_id', $org_id)->get();
            $address = InternAddress::with('city')->where('intern_id', $intern_id)
                ->where('organization_id', $org_id)->get();
            $contact = InternContact::where('intern_id', $intern_id)
                ->where('organization_id', $org_id)->get();
            $experience = InternExperience::where('intern_id', $intern_id)
                ->where('organization_id', $org_id)->get();
            $medical = InternMedical::where('intern_id', $intern_id)
                ->where('organization_id', $org_id)->get();
            $payment_method =
                InternBankAccount::where('intern_id', $intern_id)
                    ->where('organization_id', $org_id)->get();


            $document = InternDocument::with('SectionLink.ProfileSection')->where('intern_id', $intern_id)
                ->where('organization_id', $org_id)->get();


            // Return everything
            return response()->json([
                'intern' => $intern,
                'education' => $education,
                'language' => $language,
                'family' => $family,
                'address' => $address,
                'contact' => $contact,
                'experience' => $experience,
                'medical' => $medical,
                'payment_method' => $payment_method,
                'document' => $document
            ], 200);

        } catch (\Exception $e) {

            return response()->json([
                'error' => 'Something went wrong.',
                'details' => $e->getMessage()
            ], 500);
        }
    }


}
