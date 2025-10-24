<?php

namespace App\Http\Controllers\EmployeeController;
use App\Models\EmployeesModel\EmployeeAddress;
use App\Models\EmployeesModel\EmployeeBankAccount;
use App\Models\EmployeesModel\EmployeeContact;
use App\Models\EmployeesModel\EmployeeDocument;
use App\Models\EmployeesModel\EmployeeDocumentSectionLink;
use App\Models\EmployeesModel\EmployeeEducation;
use App\Models\EmployeesModel\EmployeeExperience;
use App\Models\EmployeesModel\EmployeeFamilyMember;
use App\Models\EmployeesModel\EmployeeLanguage;
use App\Models\EmployeesModel\EmployeeMedical;
use App\Models\EmployeesModel\Employees;
use Illuminate\Support\Facades\Validator;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Auth;
use Illuminate\Validation\Rule;
use Storage;

class EmployeeFormController extends Controller
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
                    'board_university_name' => 'nullable|string|max:100',
                    'marks_percentage' => 'nullable|numeric|min:0|max:100',
                    'year_of_passing' => 'nullable|digits:4',
                    'is_pursuing' => 'nullable|boolean',
                ];

            case 'language':
                return [
                    'language_id' => 'sometimes|integer|exists:organization_languages,organization_language_id',
                    'can_read' => 'sometimes|boolean',
                    'can_write' => 'sometimes|boolean',
                    'can_speak' => 'sometimes|boolean',
                    'is_native' => 'nullable|boolean',
                    'description' => 'nullable|string|max:255',
                ];

            case 'family':
                return [
                    'name' => 'sometimes|string|max:100',
                    'relationship' => 'sometimes|string|max:50',
                    'phone' => 'nullable|string|max:20',
                    'date_of_birth' => 'nullable|date|before:today',
                    'is_dependent' => 'nullable|boolean',
                ];

            case 'address':
                return [
                    'organization_employee_address_type_id' => 'sometimes|integer|exists:organization_employee_address_types,organization_employee_address_type_id',
                    'organization_residential_ownership_type_id' => 'sometimes|integer|exists:organization_residential_ownership_types,organization_residential_ownership_type_id',
                    'address_line1' => 'nullable|string|max:255',
                    'address_line2' => 'nullable|string|max:255',
                    'address_line3' => 'nullable|string|max:255',
                    'general_city_id' => 'sometimes|integer|exists:general_cities,general_city_id',
                    'postal_code' => 'sometimes|string|max:10',

                ];

            case 'contact':
                return [
                    'personal_phone_number' => 'sometimes|string|max:15',
                    'alternate_personal_phone_number' => 'nullable|string|max:15',
                    'personal_email' => 'sometimes|email|max:100',
                    'alternate_personal_email' => 'nullable|email|max:100',
                    'preferred_contact_method' => 'nullable|string|max:50',
                    'emergency_person_phone_number_1' => 'nullable|string|max:15',
                    'emergency_person_name_1' => 'nullable|string|max:100',
                    'emergency_person_relation_1' => 'nullable|string|max:255',
                    'emergency_person_phone_number_2' => 'nullable|string|max:15',
                    'emergency_person_name_2' => 'nullable|string|max:100',
                    'emergency_person_relation_2' => 'nullable|string|max:255',
                    'work_phone_number' => 'nullable|string|max:15',
                    'work_email' => 'nullable|email|max:100',
                ];

            case 'experience':
                return [
                    'organization_name' => 'sometimes|string|max:100',
                    'job_title' => 'nullable|string|max:100',
                    'employment_type' => 'nullable|string|max:50',
                    'internship_compensation_type' => 'nullable|string|max:50',
                    'general_industry_id' => 'nullable|integer|exists:general_industries,general_industry_id',
                    'start_date' => 'sometimes|date',
                    'end_date' => 'nullable|date|after_or_equal:start_date',
                    'is_current_job' => 'nullable|boolean',
                    'last_drawn_ctc' => 'nullable|numeric|min:0',
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
                    'ifsc_code' => 'nullable|string|max:20',
                    'account_number' => 'nullable|string|max:30',
                    'account_type' => 'nullable|string|max:20',
                    'is_primary' => 'nullable|boolean',
                    'qr_code_url' => 'nullable|string|max:255',
                    'remarks' => 'nullable|string|max:255',
                ];

            case 'document':
                return [
                    'employee_document_type_id' => 'nullable|integer|exists:employee_document_types,employee_document_type_id',
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
  


    //  for online  only  
    
//  public function store1(Request $request, $org_id)
//     {
//         try {



//             $user = Auth::guard('applicationusers')->user();
//             $organizationIds = $user->Client->Organization->pluck('organization_id')->toArray();

//             if (!in_array($org_id, $organizationIds)) {
//                 return response()->json(['message' => 'Unauthenticated'], 401);
//             }

//             $isUpdate = $request->has('employee_id') && !empty($request->employee_id);

//             // Validation rules
//             $coreValidation = Validator::make($request->all(), [
//                 'employee_code' => [
//                     $isUpdate ? 'sometimes' : 'required',
//                     'string',
//                     'max:10',
//                     Rule::unique('employees')->where(fn($q) => $q->where('organization_id', $org_id))
//                         ->ignore($request->employee_id, 'employee_id')
//                 ],
//                 'first_name' => $isUpdate ? 'sometimes|string|max:30' : 'required|string|max:30',
//                 'middle_name' => 'nullable|string|max:30',
//                 'last_name' => 'nullable|string|max:30',
//                 'date_of_birth' => 'nullable|date|before:today',
//                 'gender' => 'nullable|in:Male,Female,Other',
//                 'marital_status' => 'nullable|in:Single,Married,Divorced,Widowed',
//                 'organization_unit_id' => 'nullable|integer|exists:organization_units,organization_unit_id',
//                 'organization_designation_id' => 'nullable|integer|exists:organization_designations,organization_designation_id',
//                 'organization_employment_type_id' => 'nullable|integer|exists:organization_employment_types,organization_employment_type_id',
//                 'organization_department_location_id' => 'nullable|integer|exists:organization_department_locations,organization_department_location_id',
//                 'organization_work_model_id' => 'nullable|integer|exists:organization_work_models,organization_work_model_id',
//                 'organization_work_shift_id' => 'nullable|integer|exists:organization_work_shifts,organization_work_shift_id',
//                 'organization_employment_status_id' => 'nullable|integer|exists:organization_employment_statuses,organization_employment_status_id',
//               'date_of_joining' => 'nullable|date',
//                 'reporting_manager_id' => 'nullable|integer|exists:employees,employee_id',
//                 'organization_user_id' => 'nullable|integer|exists:organization_users,organization_user_id',
//             ]);

//             if ($coreValidation->fails()) {
//                 return response()->json(['errors' => $coreValidation->errors(), 'section' => 'employee'], 422);
//             }

//             if ($isUpdate) {
//                 // UPDATE
//                 $employee = Employees::where('organization_id', $org_id)
//                     ->where('employee_id', $request->employee_id)
//                     ->first();

//                 if (!$employee) {
//                     return response()->json(['message' => 'Employee not found.'], 404);
//                 }

//                 $employee->fill($request->except('profile_image_url', 'employee_id'));

//                 if ($request->hasFile('profile_image_url')) {
//                     $file = $request->file('profile_image_url');

//                     if ($file->isValid()) {
//                         // Delete old file if it exists
//                         if ($employee->profile_image_url) {
//                             Storage::disk('public')->delete('employees/' . $employee->profile_image_url);
//                         }
//                         $path = $file->store('employees', 'public');
//                          $employee->profile_image_url = basename($path);
//                     } else {
//                         return response()->json(['error' => 'Uploaded logo file is invalid.'], 400);
//                     }
//                 }

//                 $employee->save();


//                 // Handle Employee Documents - UPDATE section
//                 if ($request->has('document')) {
//                     foreach ($request->document as $index => $doc) {
//                         $doc['organization_id'] = $org_id;
//                         $doc['employee_id'] = $employee->employee_id;

//                         // Handle file upload using array-based checking
//                         $documentFiles = $request->file('document', []);
//                         if (isset($documentFiles[$index]['document_url']) && $documentFiles[$index]['document_url'] instanceof \Illuminate\Http\UploadedFile) {
//                             $file = $documentFiles[$index]['document_url'];
//                             if ($file->isValid()) {
//                                 $path = $file->store('documents', 'public');
//                                 $doc['document_url'] = $path;
//                             }
//                         }

//                         // Upsert logic
//                         if (!empty($doc['employee_document_id'])) {
//                             $existingDoc = EmployeeDocument::where('employee_document_id', $doc['employee_document_id'])
//                                 ->where('organization_id', $org_id)
//                                 ->first();
//                             if ($existingDoc) {
//                                 $existingDoc->update($doc);
//                             } else {
//                                 EmployeeDocument::create($doc);
//                             }
//                         } else {
//                             $createdDoc = EmployeeDocument::create($doc);
//                             $doc['employee_document_id'] = $createdDoc->employee_document_id;
//                         }

//                         // Handle profile section links
//                         if (!empty($doc['organization_employee_profile_section_id'])) {
//                             EmployeeDocumentSectionLink::where('employee_document_id', $doc['employee_document_id'])->delete();
//                             foreach ($doc['organization_employee_profile_section_id'] as $sectionId) {
//                                 EmployeeDocumentSectionLink::create([
//                                     'employee_document_id' => $doc['employee_document_id'],
//                                     'organization_id' => $org_id,
//                                     'organization_employee_profile_section_id' => $sectionId,
//                                     'organization_entity_id' => $doc['organization_entity_id'] ?? null,
//                                 ]);
//                             }
//                         }
//                     }
//                 }



//                 $education = EmployeeEducation::where("employee_id", $employee->employee_id)->get();
//                 $language = EmployeeLanguage::where("employee_id", $employee->employee_id)->get();
//                 $family = EmployeeFamilyMember::where("employee_id", $employee->employee_id)->get();
//                 $address = EmployeeAddress::where("employee_id", $employee->employee_id)->get();
//                 $contact = EmployeeContact::where("employee_id", $employee->employee_id)->get();
//                 $experience = EmployeeExperience::where("employee_id", $employee->employee_id)->get();
//                 $medical = EmployeeMedical::where("employee_id", $employee->employee_id)->get();
//                 $payment_method = EmployeeBankAccount::where("employee_id", $employee->employee_id)->get();
//                 $document = EmployeeDocument::where("employee_id", $employee->employee_id)->get();

//                 return response()->json([
//                     'message' => 'Employee updated successfully.',
//                     'employee' => $this->transformEmployee($employee),
//                     'education' => $education,
//                     'language' => $language,
//                     'family' => $family,
//                     'address' => $address,
//                     'contact' => $contact,
//                     'experience' => $experience,
//                     'medical' => $medical,
//                     'payment_method' => $payment_method,
//                     'document' => $document
//                 ], 200);

//             } else {
//                 // CREATE
//                 $employee = new Employees();


//                 // for document   that is to be added   
//                 $employee->organization_id = $org_id;
//                 $employee->employee_code = $request->employee_code;
//                 $employee->first_name = $request->first_name;
//                 $employee->middle_name = $request->middle_name;
//                 $employee->last_name = $request->last_name;
//                 $employee->date_of_birth = $request->date_of_birth;
//                 $employee->gender = $request->gender;
//                 $employee->marital_status = $request->marital_status;
//                 $employee->organization_unit_id = $request->organization_unit_id;
//                 $employee->organization_department_location_id = $request->organization_department_location_id;
//                 $employee->organization_designation_id = $request->organization_designation_id;
//                 $employee->organization_employment_type_id = $request->organization_employment_type_id;
//                 $employee->organization_work_model_id = $request->organization_work_model_id;
//                 $employee->organization_work_shift_id = $request->organization_work_shift_id;
//                 $employee->organization_employment_status_id = $request->organization_employment_status_id;
//                 $employee->date_of_joining = $request->date_of_joining;
//                 $employee->reporting_manager_id = $request->reporting_manager_id;
//                 $employee->organization_user_id = $request->organization_user_id;

//                 if ($request->hasFile('profile_image_url') && $request->file('profile_image_url')->isValid()) {
//                     $file = $request->file('profile_image_url');
//                     $path = $file->store('employees', 'public');
//                    $employee->profile_image_url = basename($path); 
//                 } else {
//                     $employee['profile_image_url'] = $request->input('profile_image_url');
//                 }

//                 $employee->save();

//                 // Handle Employee Documents - CREATE section
//                 if ($request->has('document')) {
//                     foreach ($request->document as $index => $doc) {
//                         $doc['organization_id'] = $org_id;
//                         $doc['employee_id'] = $employee->employee_id;

//                         // Handle file upload using array-based checking
//                         $documentFiles = $request->file('document', []);
//                         if (isset($documentFiles[$index]['document_url']) && $documentFiles[$index]['document_url'] instanceof \Illuminate\Http\UploadedFile) {
//                             $file = $documentFiles[$index]['document_url'];
//                             if ($file->isValid()) {
//                                 $path = $file->store('documents', 'public');
//                                 $doc['document_url'] = $path;
//                             }
//                         }

//                         if (!empty($doc['employee_document_id'])) {
//                             $existingDoc = EmployeeDocument::where('employee_document_id', $doc['employee_document_id'])
//                                 ->where('organization_id', $org_id)
//                                 ->first();
//                             if ($existingDoc) {
//                                 $existingDoc->update($doc);
//                             } else {
//                                 EmployeeDocument::create($doc);
//                             }
//                         } else {
//                             $createdDoc = EmployeeDocument::create($doc);
//                             $doc['employee_document_id'] = $createdDoc->employee_document_id;
//                         }

//                         if (!empty($doc['organization_employee_profile_section_id'])) {
//                             EmployeeDocumentSectionLink::where('employee_document_id', $doc['employee_document_id'])->delete();
//                             foreach ($doc['organization_employee_profile_section_id'] as $sectionId) {
//                                 EmployeeDocumentSectionLink::create([
//                                     'employee_document_id' => $doc['employee_document_id'],
//                                     'organization_id' => $org_id,
//                                     'organization_employee_profile_section_id' => $sectionId,
//                                     'organization_entity_id' => $doc['organization_entity_id'] ?? null,
//                                 ]);
//                             }
//                         }
//                     }
//                 }





//                 return response()->json([
//                     'message' => 'Employee added successfully.',
//                     'employee' => $this->transformEmployee($employee),
//                     'education' => [],
//                     'language' => [],
//                     'family' => [],
//                     'address' => [],
//                     'contact' => [],
//                     'experience' => [],
//                     'medical' => [],
//                     'payment_method' => [],
//                     'document' => []
//                 ], 201);
//             }

//         } catch (\Exception $e) {
//             return response()->json([
//                 'error' => 'Something went wrong.',
//                 'details' => $e->getMessage(),
//             ], 500);
//         }
//     }



    public function store1(Request $request, $org_id)
    {
        try {


            $user = Auth::guard('applicationusers')->user();
            $organizationIds = $user->Client->Organization->pluck('organization_id')->toArray();

            if (!in_array($org_id, $organizationIds)) {
                return response()->json(['message' => 'Unauthenticated'], 401);
            }

            $isUpdate = $request->has('employee_id') && !empty($request->employee_id);

            // Validation rules
            $coreValidation = Validator::make($request->all(), [
                'employee_code' => [
                    $isUpdate ? 'sometimes' : 'required',
                    'string',
                    'max:10',
                    Rule::unique('employees')->where(fn($q) => $q->where('organization_id', $org_id))
                        ->ignore($request->employee_id, 'employee_id')
                ],
                'first_name' => $isUpdate ? 'sometimes|string|max:30' : 'required|string|max:30',
                'middle_name' => 'nullable|string|max:30',
                'last_name' => 'nullable|string|max:30',
                'date_of_birth' => 'nullable|date|before:today',
                'gender' => 'nullable|in:Male,Female,Other',
                'marital_status' => 'nullable|in:Single,Married,Divorced,Widowed',
                'organization_unit_id' => 'nullable|integer|exists:organization_units,organization_unit_id',
                'organization_designation_id' => 'nullable|integer|exists:organization_designations,organization_designation_id',
                'organization_employment_type_id' => 'nullable|integer|exists:organization_employment_types,organization_employment_type_id',
                'organization_department_location_id' => 'nullable|integer|exists:organization_department_locations,organization_department_location_id',
                'organization_work_model_id' => 'nullable|integer|exists:organization_work_models,organization_work_model_id',
                'organization_work_shift_id' => 'nullable|integer|exists:organization_work_shifts,organization_work_shift_id',
                'organization_employment_status_id' => 'nullable|integer|exists:organization_employment_statuses,organization_employment_status_id',
                'organization_employment_stage_id' => 'nullable|integer|exists:organization_employment_stages,organization_employment_stage_id',
                'date_of_joining' => 'nullable|date',
                'reporting_manager_id' => 'nullable|integer|exists:employees,employee_id',
                'organization_user_id' => 'nullable|integer|exists:organization_users,organization_user_id',
            ]);

            if ($coreValidation->fails()) {
                return response()->json(['errors' => $coreValidation->errors(), 'section' => 'employee'], 422);
            }

            if ($isUpdate) {
                // UPDATE
                $employee = Employees::where('organization_id', $org_id)
                    ->where('employee_id', $request->employee_id)
                    ->first();

                if (!$employee) {
                    return response()->json(['message' => 'Employee not found.'], 404);
                }

                $employee->fill($request->except('profile_image_url', 'employee_id'));

                if ($request->hasFile('profile_image_url')) {
                    $file = $request->file('profile_image_url');

                    if ($file->isValid()) {
                        // Delete old file if it exists
                        if ($employee->profile_image_url) {
                            Storage::disk('public')->delete($employee->profile_image_url);
                        }
                        $path = $file->store('employees', 'public');
                        $employee['profile_image_url'] = $path; 
                    } else {
                        return response()->json(['error' => 'Uploaded logo file is invalid.'], 400);
                    }
                }

                $employee->save();


                // Handle Employee Documents - UPDATE section
                if ($request->has('document')) {
                    foreach ($request->document as $index => $doc) {
                        $doc['organization_id'] = $org_id;
                        $doc['employee_id'] = $employee->employee_id;

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
                        if (!empty($doc['employee_document_id'])) {
                            $existingDoc = EmployeeDocument::where('employee_document_id', $doc['employee_document_id'])
                                ->where('organization_id', $org_id)
                                ->first();
                            if ($existingDoc) {
                                $existingDoc->update($doc);
                            } else {
                                EmployeeDocument::create($doc);
                            }
                        } else {
                            $createdDoc = EmployeeDocument::create($doc);
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



                $education = EmployeeEducation::where("employee_id", $employee->employee_id)->get();
                $language = EmployeeLanguage::where("employee_id", $employee->employee_id)->get();
                $family = EmployeeFamilyMember::where("employee_id", $employee->employee_id)->get();
                $address = EmployeeAddress::where("employee_id", $employee->employee_id)->get();
                $contact = EmployeeContact::where("employee_id", $employee->employee_id)->get();
                $experience = EmployeeExperience::where("employee_id", $employee->employee_id)->get();
                $medical = EmployeeMedical::where("employee_id", $employee->employee_id)->get();
                $payment_method = EmployeeBankAccount::where("employee_id", $employee->employee_id)->get();
                $document = EmployeeDocument::where("employee_id", $employee->employee_id)->get();

                return response()->json([
                    'message' => 'Employee updated successfully.',
                    'employee' => $this->transformEmployee($employee),
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
                $employee = new Employees();


                // for document   that is to be added   
                $employee->organization_id = $org_id;
                $employee->employee_code = $request->employee_code;
                $employee->first_name = $request->first_name;
                $employee->middle_name = $request->middle_name;
                $employee->last_name = $request->last_name;
                $employee->date_of_birth = $request->date_of_birth;
                $employee->gender = $request->gender;
                $employee->marital_status = $request->marital_status;
                $employee->organization_unit_id = $request->organization_unit_id;
                $employee->organization_department_location_id = $request->organization_department_location_id;
                $employee->organization_designation_id = $request->organization_designation_id;
                $employee->organization_employment_type_id = $request->organization_employment_type_id;
                $employee->organization_work_model_id = $request->organization_work_model_id;
                $employee->organization_work_shift_id = $request->organization_work_shift_id;
                $employee->organization_employment_status_id = $request->organization_employment_status_id;
                $employee->organization_employment_stage_id = $request->organization_employment_stage_id;
                $employee->date_of_joining = $request->date_of_joining;
                $employee->reporting_manager_id = $request->reporting_manager_id;
                $employee->organization_user_id = $request->organization_user_id;

                if ($request->hasFile('profile_image_url') && $request->file('profile_image_url')->isValid()) {
                    $file = $request->file('profile_image_url');
                    $path = $file->store('employees', 'public');
                    $employee['profile_image_url'] = $path; // Store only relative path
                } else {
                    $employee['profile_image_url'] = $request->input('profile_image_url');
                }

                $employee->save();

                // Handle Employee Documents - CREATE section
                if ($request->has('document')) {
                    foreach ($request->document as $index => $doc) {
                        $doc['organization_id'] = $org_id;
                        $doc['employee_id'] = $employee->employee_id;

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
                            $existingDoc = EmployeeDocument::where('employee_document_id', $doc['employee_document_id'])
                                ->where('organization_id', $org_id)
                                ->first();
                            if ($existingDoc) {
                                $existingDoc->update($doc);
                            } else {
                                EmployeeDocument::create($doc);
                            }
                        } else {
                            $createdDoc = EmployeeDocument::create($doc);
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
                    'message' => 'Employee added successfully.',
                    'employee' => $this->transformEmployee($employee),
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

    private function transformEmployee($employee)
    {
        $employeeArray = $employee->toArray();
        $employeeArray['profile_image_url'] = $employee->profile_image_url
            ? asset('storage/' . $employee->profile_image_url)
            : null;

        return $employeeArray;
    }


       public function store2(Request $request, $org_id)
    {
        try {

            $sections = [];
            $primaryKeys = [];

            if ($request->has('education'))
                $sections['education'] = EmployeeEducation::class;
            $primaryKeys['education'] = 'employee_education_id';
            if ($request->has('language'))
                $sections['language'] = EmployeeLanguage::class;
            $primaryKeys['language'] = 'employee_language_id';
            if ($request->has('family'))
                $sections['family'] = EmployeeFamilyMember::class;
            $primaryKeys['family'] = 'employee_family_member_id';
            if ($request->has('address'))
                $sections['address'] = EmployeeAddress::class;
            $primaryKeys['address'] = 'employee_address_id';
            if ($request->has('contact'))
                $sections['contact'] = EmployeeContact::class;
            $primaryKeys['contact'] = 'employee_contact_id';
            if ($request->has('experience'))
                $sections['experience'] = EmployeeExperience::class;
            $primaryKeys['experience'] = 'employee_experience_id';
            if ($request->has('medical'))
                $sections['medical'] = EmployeeMedical::class;
            $primaryKeys['medical'] = 'employee_medical_id';
            if ($request->has('payment_method'))
                $sections['payment_method'] = EmployeeBankAccount::class;
            $primaryKeys['payment_method'] = 'employee_bank_account_id';
            if ($request->has('document'))
                $sections['document'] = EmployeeDocument::class;
            $primaryKeys['document'] = 'employee_document_id';

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

                $employeeId = $entries[0]['employee_id'] ?? null;
                if (!$employeeId) {
                    return response()->json(['error' => "Employee ID is required in section $sectionKey."], 400);
                }

                // Get existing IDs from DB
                $existing = $modelClass::where('employee_id', $employeeId)
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
                'message' => 'Employee sections saved successfully.',
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
    public function update(Request $request, $org_id, $employee_id)
    {
        try {
            $user = Auth::guard('applicationusers')->user();
            $organizationIds =
                $user->Client->Organization->pluck('organization_id')->toArray();

            if (!in_array($org_id, $organizationIds)) {
                return response()->json(['message' => 'Unauthenticated'], 401);
            }

            // Main employee data
            $employee = Employees::with('workmodel', 'departmentLocation.department')->where('employee_id', $employee_id)
                ->where('organization_id', $org_id)
                ->first();

            if (!$employee) {
                return response()->json([
                    'message' => 'Employee not found.'
                ], 404);
            }


            $education = EmployeeEducation::with('degree')->where('employee_id', $employee_id)
                ->where('organization_id', $org_id)->get();
            $language = EmployeeLanguage::where('employee_id', $employee_id)
                ->where('organization_id', $org_id)->get();
            $family = EmployeeFamilyMember::where('employee_id', $employee_id)
                ->where('organization_id', $org_id)->get();
            $address = EmployeeAddress::with('city')->where('employee_id', $employee_id)
                ->where('organization_id', $org_id)->get();
            $contact = EmployeeContact::where('employee_id', $employee_id)
                ->where('organization_id', $org_id)->get();
            $experience = EmployeeExperience::where('employee_id', $employee_id)
                ->where('organization_id', $org_id)->get();
            $medical = EmployeeMedical::where('employee_id', $employee_id)
                ->where('organization_id', $org_id)->get();
            $payment_method =
                EmployeeBankAccount::where('employee_id', $employee_id)
                    ->where('organization_id', $org_id)->get();


            $document = EmployeeDocument::with('SectionLink.ProfileSection')->where('employee_id', $employee_id)
                ->where('organization_id', $org_id)->get();


            // Return everything
            return response()->json([
                'employee' => $employee,
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
