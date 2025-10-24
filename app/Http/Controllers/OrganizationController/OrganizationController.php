<?php

namespace App\Http\Controllers\OrganizationController;

use App\Models\ApplicationModels\ApplicationActivityLogs;
use App\Models\ApplicationModels\ApplicationUserRoleAssignment;
use App\Models\ApplicationModels\ApplicationUserRoles;
use App\Models\ApplicationModels\ApplicationUsers;
use App\Models\ClientModels\Client;
use App\Models\ClientModels\ClientLicenses;
use App\Models\ConfigrationModels\ConfigrationTemplates;
use App\Models\GeneralModel\GeneralCities;
use App\Models\GeneralModel\GeneralState;
use App\Models\OrganizationModel\ApplicationOrganizationAcive;
use App\Models\OrganizationModel\OrganizationBusinessProfile;
use App\Models\OrganizationModel\OrganizationEntities;
use App\Models\OrganizationModel\OrganizationGroups;
use App\Models\OrganizationModel\OrganizationLocation;
use App\Models\OrganizationModel\OrganizationUser;
use App\Models\OrganizationModel\OrganizationUserRoleAssignment;
use App\Models\OrganizationModel\OrganizationUserType;
use Auth;
use DB;
use Hash;
use Illuminate\Support\Facades\Validator;
use App\Http\Controllers\Controller;
use App\Models\OrganizationModel\Organization;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use PharIo\Manifest\License;
use Tymon\JWTAuth\Facades\JWTAuth;

class OrganizationController extends Controller
{



    public function indexEmployees(Request $request)
    {
        try {
            $organizationId = $request->organization_id;

            $org = Organization::find($organizationId);

            if (!$org) {
                return response()->json(['message' => "No Employees Avaialble for this Organization"], 404);
            }
            $org->load(['employees']);
            return response()->json([
                'data' => $org->employees
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Something went wrong. Please try again later.',
                'details' => $e->getMessage()
            ], 500);
        }
    }

    public function indexV1(Request $request)
    {
        try {
            $query = Organization::query();

            if ($request->has('client_id')) {
                $query->where('client_id', $request->client_id);
            }

            if ($request->has('search') && $request->search !== '') {
                $query->where('organization_name', 'LIKE', '%' . $request->search . '%');
            }

            $perPage = $request->get('per_page', 10);

            $organizations = $query->paginate($perPage);

            return response()->json([
                'status' => 'success',
                'organizations' => $organizations
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Something went wrong. Please try again later.',
                'details' => $e->getMessage()
            ], 500);
        }
    }


    public function index(Request $request)
    {

        try {
            $organizations = Organization::where('client_id', $request->client_id)->get();
            return response()->json([
                'status' => 'success',
                'organizations' => $organizations
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Something went wrong. Please try again later.',
                'details' => $e->getMessage()
            ], 500);
        }





        // $query = Organization::with(['BusinessOwnershipType', 'industry', 'Entities']);

        // // Search query
        // if ($request->filled('query')) {
        //     $q = $request->query('query');
        //     $query->where('organization_name', 'LIKE', "%$q%");
        // }

        // // Industry filter
        // if ($request->filled('industry_filter')) {
        //     $industryFilter = $request->query('industry_filter');
        //     $query->join('general_industries', 'organizations.general_industry_id', '=', 'general_industries.general_industry_id')
        //         ->where('general_industries.industry_name', 'LIKE', "%$industryFilter%");
        // }

        // // Ownership Type filter
        // if ($request->filled('ownership_type_filter')) {
        //     $ownershipFilter = $request->query('ownership_type_filter');
        //     $query->join('general_business_ownership_types', 'organizations.general_business_ownership_type_id', '=', 'general_business_ownership_types.business_ownership_type_name')
        //         ->where('general_business_ownership_types.business_ownership_type_name', 'LIKE', "%$ownershipFilter%");
        // }

        // // Sorting
        // $sortBy = $request->query('sort_by', 'organization_name');
        // $sortOrder = $request->query('sort_order', 'asc');
        // $allowedSorts = [
        //     'organization_id',
        //     'organization_name',
        //     'organization_short_name'
        // ];

        // // Sorting logic
        // if (in_array($sortBy, $allowedSorts)) {
        //     if ($sortBy === 'industry_name') {
        //         // Sorting by industry name
        //         $query->join('general_industries', 'organizations.general_industry_id', '=', 'general_industries.general_industry_id')
        //             ->orderBy('general_industries.industry_name', $sortOrder)
        //             ->select('organizations.*');
        //     } elseif ($sortBy === 'business_ownership_type_name') {
        //         // Sorting by business ownership type name
        //         $query->join('general_business_ownership_types', 'organizations.general_business_ownership_type_id', '=', 'general_business_ownership_types.general_business_ownership_type_id')
        //             ->orderBy('general_business_ownership_types.business_ownership_type_name', $sortOrder)
        //             ->select('organizations.*');
        //     } else {
        //         // Sorting by organization columns (like organization_id, organization_name, etc.)
        //         $query->orderBy("organizations.$sortBy", $sortOrder);
        //     }
        // }

        // // Pagination
        // $perPage = $request->query('page_size', 15);
        // $organizations = $query->paginate($perPage);

        // return response()->json([
        //     'organizations' => $organizations->items(),
        //     'pagination' => [
        //         'current_page' => $organizations->currentPage(),
        //         'last_page' => $organizations->lastPage(),
        //         'total' => $organizations->total(),
        //     ],
        // ]);

    }


    public function store(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'organization_name' => 'required|string|max:255',
                'organization_short_name' => 'required|string|max:100',
            ]);


            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()], 422);
            }
            $application_user = ApplicationUsers::find($request->application_user_id);


            // if already sign up 
            if ($request->client_id) {


                // get the client from application user 
                $client_id = $request->client_id;

                // create organization group here 
                $organizationgrp = OrganizationGroups::create([
                    'client_id' => $request->client_id,
                    'organization_group_name' => "Group Name",
                    'organization_group_short_name' => "GROUP"
                ]);

                // get the licdense from client id 
                // create licence id 
                $license = ClientLicenses::where('client_id', $request->client_id)->first();



                $organization = Organization::create([
                    'organization_name' => $request->organization_name,
                    'organization_short_name' => $request->organization_short_name,
                    'license_id' => $license->client_license_id,
                    'client_id' => $request->client_id,
                    'organization_group_id' => $organizationgrp->organization_group_id

                ]);

                // create organization user 
                //    $organization_user=  OrganizationUser::create([
                //     'organization_id' => $organization->organization_id,
                //     'application_user_id' => $request->application_user_id,

                // ]);


                // // add its role in organizatiojn user role assignment 
                // $role_assignment=OrganizationUserRoleAssignment::create([
                //      'organization_id' => $organization->organization_id,
                //      'assigned_at'=>now(),
                //      'application_user_role_id'=>10,
                //      'organization_user_id'=>$organization_user->organization_user_id

                // ]);


            }

            //uppal12345@gmail.com   //bhavesh12345


            // if doing sign up for first time 
            else {

                $client_name = $application_user->full_name;
                $client = Client::create([
                    'client_name' => $client_name,

                ]);

                $application_user->client_id = $client->client_id;
                $application_user->save();


                // create licence id 
                $license = ClientLicenses::create([
                    'client_id' => $client->client_id

                ]);

                // create organization group here 

                $organizationgrp = OrganizationGroups::create([
                    'client_id' => $client->client_id,
                    'organization_group_name' => "Group Name",
                    'organization_short_name' => "GROUP"
                ]);


                $organization = Organization::create([
                    'organization_name' => $request->organization_name,
                    'organization_short_name' => $request->organization_short_name,
                    'license_id' => $license->client_license_id,
                    'client_id' => $client->client_id,
                    'organization_group_id' => $organizationgrp->organization_group_id

                ]);


            }


            $states = GeneralState::find($request->general_state_id);
            $cities = GeneralCities::find($request->general_city_id);


            $org_location = OrganizationLocation::create([
                'organization_id' => $organization->organization_id,
                'general_state_id' => $request->general_state_id,
                'general_country_id' => $request->general_country_id,
                'location_name' => $cities->city_name,
                'general_city_id' => $request->general_city_id


            ]);

            // // create application activity logs
            // $activity_logs = ApplicationActivityLogs::create([
            //     'affected_user_id' => $request->application_user_id,
            //     'performed_by_user_id' => $request->application_user_id,
            //     'client_id' => $request->client_id ? $request->client_id : $client->client_id,
            //     'organization_id' => $organization->organization_id,
            //     'application_activity_log_type_id' => 1,
            //     'application_module_id' => 10,
            //     'application_module_action_id' => 1,
            //     'activity_data' => json_encode(["message" => "Created type user and logged in automatically"])
            // ]);

            // create application user role 

            // $role = ApplicationUserRoles::create([
            //     'user_role_name' => "Admin"
            // ]);


            // // // create application user role assignments 
            // $role_assignments = ApplicationUserRoleAssignment::create([
            //     'application_user_id' => $request->application_user_id,
            //     'application_user_role_id' => $role->application_user_role_id,
            //     'is_active' => true
            // ]);


            $templatesdata = DB::connection('mysql_second')
                ->table('configuration_templates')
                ->where('general_country_id', $request->general_country_id)
                ->first();

            if ($templatesdata) {
                $templateId = DB::table('organization_configuration_templates')->insertGetId([
                    'organization_id' => $organization->organization_id,
                    'template_name' => $templatesdata->template_name ?? '',
                    'template_code' => $templatesdata->template_code,
                    'description' => $templatesdata->description,
                    'scope' => 'country',
                    'general_country_id' => $request->general_country_id,
                    'general_state_id' => $request->general_state_id,
                    'created_by' => 'template',
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);


                // copy organization business ownwershiop type
                $ownershipTypes = DB::connection('mysql_second')->table('configuration_template_business_ownership_types')->get();

                foreach ($ownershipTypes as $type) {
                    DB::table('organization_business_ownership_types')->insert([
                        'organization_id' => $organization->organization_id,
                        'organization_configuration_template_id' => $templateId,
                        'general_business_ownership_type_category_id' => $type->general_business_ownership_type_category_id,
                        'organization_business_ownership_type_name' => $type->business_ownership_type_name,
                        'created_by' => 'template',
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }


                // copy registration templatyes 
                $registrationTypes = DB::connection('mysql_second')
                    ->table('configuration_template_business_registration_types')
                    ->get();


                foreach ($registrationTypes as $type) {
                    DB::table('organization_business_registration_types')->insert([
                        'organization_id' => $organization->organization_id,

                        'organization_configuration_template_id' => $templateId, // ✅ Use the inserted template ID
                        'business_registration_type_name' => $type->business_registration_type_name,
                        'business_registration_type_code' => $type->business_registration_type_code,
                        'description' => $type->description,
                        'created_by' => 'template',
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }




                // copy employeess address types \
                $addressTypes = DB::connection('mysql_second')
                    ->table('configuration_template_employee_address_types')
                    ->get();

                foreach ($addressTypes as $type) {
                    DB::table('organization_employee_address_types')->insert([
                        'organization_id' => $organization->organization_id,

                        'organization_configuration_template_id' => $templateId, // ✅ Correct FK to organization_configuration_templates
                        'employee_address_type_name' => $type->employee_address_type_name,
                        'created_by' => 'template',
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }


                // Step 1: Copy organization_employment_exit_reason_types and build ID map
                $resontypes = DB::connection('mysql_second')
                    ->table('configuration_template_employment_exit_reason_types')
                    ->get();

                $reasonTypeIdMap = []; // old_id => new_inserted_id
                // return $resontypes;

                foreach ($resontypes as $user) {
                    $newId = DB::table('organization_employment_exit_reason_types')->insertGetId([
                        'organization_id' => $organization->organization_id,
                        'organization_configuration_template_id' => $templateId,
                        'employment_exit_reason_type_name' => $user->exit_reason_type_name,
                        'description' => $user->description,
                        'created_by' => 'template',
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                    // echo $user->exit_reason_type_name;
                    // echo "\n";


                    $reasonTypeIdMap[$user->configuration_template_exit_reason_type_id] = $newId;
                }
                // return $reasonTypeIdMap;

                $exitreason = DB::connection('mysql_second')
                    ->table('configuration_template_employment_exit_reasons')
                    ->get();

                foreach ($exitreason as $user) {
                    DB::table('organization_employment_exit_reasons')->insert([
                        'organization_id' => $organization->organization_id,

                        'organization_configuration_template_id' => $templateId,
                        'organization_employment_exit_reason_type_id' => $reasonTypeIdMap[$user->configuration_template_exit_reason_type_id],
                        'employment_exit_reason_name' => $user->employment_exit_reason_name,
                        'description' => $user->description,
                        'created_by' => 'template',
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }

                // employeement status 
                // $status = DB::connection('mysql_second')->table('configuration_template_employment_statuses')->get();

                // foreach ($status as $user) {
                //     DB::table('organization_employment_statuses')->insert([
                //         'organization_id' => $organization->organization_id,

                //         'organization_configuration_template_id' => $templateId,
                //         'employment_status_name' => $user->employment_status_name,
                //         'created_by' => 'template',
                //         'created_at' => now(),
                //         'updated_at' => now(),
                //     ]);
                // }


                // copy employment types 
                $types = DB::connection('mysql_second')->table('configuration_template_employment_types')->get();

                foreach ($types as $user) {
                    DB::table('organization_employment_types')->insert([
                        'organization_id' => $organization->organization_id,
                        'organization_configuration_template_id' => $templateId,
                        'employment_type_name' => $user->employment_type_name,
                        'created_by' => 'template',
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }


                // employment categories
                $categories = DB::connection('mysql_second')->table('configuration_template_employment_categories')->get();

                foreach ($categories as $user) {
                    DB::table('organization_employment_categories')->insert([
                        'organization_id' => $organization->organization_id,
                        'organization_configuration_template_id' => $templateId,
                        'employment_category_name' => $user->employment_category_name,
                        'description' => $user->description,
                        'created_by' => 'template',
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }




                // leave categories 
                $categories = DB::connection('mysql_second')->table('configuration_template_leave_categories')->get();

                foreach ($categories as $user) {
                    DB::table('organization_leave_categories')->insert([
                        'organization_id' => $organization->organization_id,

                        'organization_configuration_template_id' => $templateId,
                        'leave_category_name' => $user->leave_category_name,
                        'leave_category_code' => $user->leave_category_code,
                        'description' => $user->description,
                        'created_by' => 'template',
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }


                // leave duration types 
                $duration = DB::connection('mysql_second')->table('configuration_template_leave_duration_types')->get();
                foreach ($duration as $user) {
                    DB::table('organization_leave_duration_types')->insert([
                        'organization_id' => $organization->organization_id,

                        'organization_configuration_template_id' => $templateId,
                        'leave_duration_type_name' => $user->leave_duration_type_name,
                        'created_by' => 'template',
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }



                // leave types
                $leaveTypeIdMap = [];

                $leaveTypes = DB::connection('mysql_second')->table('configuration_template_leave_types')->get();

                foreach ($leaveTypes as $user) {
                    $newLeaveTypeId = DB::table('organization_leave_types')->insertGetId([ // ✅ insertGetId to get the new ID
                        'organization_id' => $organization->organization_id,
                        'organization_configuration_template_id' => $templateId,
                        'leave_type_name' => $user->leave_type_name,
                        'leave_type_code' => $user->leave_type_code ?? '',
                        'max_days_allowed' => $user->max_days_allowed,
                        'carry_forward' => $user->carry_forward,
                        'requires_approval' => $user->requires_approval,
                        'is_active' => $user->is_active,
                        'created_by' => 'template',
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);

                    $leaveTypeIdMap[$user->configuration_template_leave_type_id] = $newLeaveTypeId;
                }


                $leaveReasonTypeIdMap = [];
                $reasontypes = DB::connection('mysql_second')->table('configuration_template_leave_reason_types')->get();

                foreach ($reasontypes as $type) {
                    $newId = DB::table('organization_leave_reason_types')->insertGetId([
                        'organization_id' => $organization->organization_id,
                        'organization_configuration_template_id' => $templateId,
                        'organization_leave_type_id' => $leaveTypeIdMap[$type->configuration_template_leave_type_id] ?? null, // ✅ Use the NEW mapped leave_type_id here
                        'leave_reason_type_name' => $type->leave_reason_type_name,
                        'description' => $type->description,
                        'created_by' => 'template',
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);

                    $leaveReasonTypeIdMap[$type->configuration_template_leave_reason_type_id] = $newId;
                }


                // leave reson 
                $leavereason = DB::connection('mysql_second')
                    ->table('configuration_template_leave_reasons')
                    ->get();

                foreach ($leavereason as $reason) {
                    DB::table('organization_leave_reasons')->insert([
                        'organization_id' => $organization->organization_id,
                        'organization_configuration_template_id' => $templateId,
                        'organization_leave_reason_type_id' => $leaveReasonTypeIdMap[$reason->configuration_template_leave_reason_type_id] ?? null,
                        'leave_reason_name' => $reason->leave_reason_name,
                        'description' => $reason->description,
                        'created_by' => 'template',
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }



                // copy configration templotes  employement status to  organization employement status 
                 $empstatusIdMap = [];
                 $empstatus = DB::connection('mysql_second')
                    ->table('configuration_template_employment_statuses')
                    ->get();

                foreach ($empstatus as $status) {
                  $newId=  DB::table('organization_employment_statuses')->insertGetId([
                        'organization_id' => $organization->organization_id,
                        'organization_configuration_template_id' => $templateId,
                        'employment_status_name' => $status->employment_status_name ,
                        'created_by' => 'template',
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);

                     $empstatusIdMap[$status->configuration_template_employment_status_id ] = $newId;

                }

                // copy  the configeration template  employment stages  to emp statges  
                  $empstages = DB::connection('mysql_second')
                    ->table('configuration_template_employment_stages')
                    ->get();

                foreach ($empstages as $stages) {
                    DB::table('organization_employment_stages')->insert([
                        'organization_id' => $organization->organization_id,
                        'organization_configuration_template_id' => $templateId,
                        'organization_employment_status_id' => $empstatusIdMap[$stages->configuration_template_employment_status_id] ?? null,
                        'employment_stage_name' => $stages->employment_stage_name,
                        'created_by' => 'template',
                        'description'=>$stages->description,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }


                // copy intership  employment statasus   to employment stataus 

                $intstatusIdMap = [];
                 $intstatus = DB::connection('mysql_second')
                    ->table('configuration_template_internship_statuses')
                    ->get();

                foreach ($intstatus as $status) {
                  $newId=  DB::table('organization_internship_statuses')->insertGetId([
                        'organization_id' => $organization->organization_id,
                        'organization_configuration_template_id' => $templateId,
                        'internship_status_name' => $status->internship_status_name ,
                        'created_by' => 'template',
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);

                     $intstatusIdMap[$status->configuration_template_internship_status_id] = $newId;

                }

                  $Intstages = DB::connection('mysql_second')
                    ->table('configuration_template_internship_stages')
                    ->get();

                foreach ($Intstages as $stages) {
                    DB::table('organization_internship_stages')->insert([
                        'organization_id' => $organization->organization_id,
                        'organization_configuration_template_id' => $templateId,
                        'organization_internship_status_id' => $intstatusIdMap[$stages->configuration_template_internship_status_id] ?? null,
                        'internship_stage_name' => $stages->internship_stage_name,
                        'created_by' => 'template',
                        'description'=>$stages->description,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }



                // location ownership types 

                $ownership = DB::connection('mysql_second')->table('configuration_template_location_ownership_types')->get();

                foreach ($ownership as $user) {
                    DB::table('organization_location_ownership_types')->insert([
                        'organization_id' => $organization->organization_id,
                        'organization_configuration_template_id' => $templateId,
                        'location_ownership_type_name' => $user->location_ownership_type_name,
                        'description' => $user->description,
                        'created_by' => 'template',
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }


                // residentail ownership type 

                $residentowner = DB::connection('mysql_second')->table('configuration_template_residential_ownership_types')->get();

                foreach ($residentowner as $user) {
                    DB::table('organization_residential_ownership_types')->insert([
                        'organization_id' => $organization->organization_id,

                        'organization_configuration_template_id' => $templateId,
                        'residential_ownership_type_name' => $user->residential_ownership_type_name,
                        'description' => $user->description,
                        'created_by' => 'template',
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }



                // unit types

                $unittypes = DB::connection('mysql_second')->table('configuration_template_unit_types')->get();

                foreach ($unittypes as $user) {
                    DB::table('organization_unit_types')->insert([
                        'organization_id' => $organization->organization_id,
                        'organization_configuration_template_id' => $templateId,
                        'unit_type_name' => $user->unit_type_name,
                        'created_by' => 'template',
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }

                // user types 
                $users = DB::connection('mysql_second')->table('configuration_template_user_types')->get();

                foreach ($users as $user) {
                    DB::table('organization_user_types')->insert([
                        'organization_id' => $organization->organization_id,

                        'organization_configuration_template_id' => $templateId,
                        'user_type_name' => $user->user_type_name,
                        'created_by' => 'template',
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }


                $businessprofile = OrganizationBusinessProfile::create([
                    'organization_id' => $organization->organization_id,

                    'general_business_ownership_type_category_id' => $request->general_business_ownership_type_category_id,
                    'general_industry_id' => $request->general_industry_id,

                ]);


                // work models 
                $models = DB::connection('mysql_second')->table('configuration_template_work_models')->get();

                foreach ($models as $user) {
                    DB::table('organization_work_models')->insert([
                        'organization_id' => $organization->organization_id,

                        'organization_configuration_template_id' => $templateId,
                        'work_model_name' => $user->work_model_name,
                        'created_by' => 'template',
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }

                // work shift types 
                $workshift = DB::connection('mysql_second')->table('configuration_template_work_shift_types')->get();
                foreach ($workshift as $user) {
                    DB::table('organization_work_shift_types')->insert([
                        'organization_id' => $organization->organization_id,

                        'organization_configuration_template_id' => $templateId,
                        'work_shift_type_name' => $user->work_shift_type_name,
                        'work_shift_type_short_name' => $user->work_shift_type_short_name,
                        'created_by' => 'template',
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }



                //   education levels 
                $EducationLevelIdMap = [];
                $educationLevel = DB::connection('mysql_second')->table('configuration_template_education_levels')->get();
                foreach ($educationLevel as $level) {
                    $newId = DB::table('organization_education_levels')->insertGetId([
                        'organization_id' => $organization->organization_id,
                        'organization_configuration_template_id' => $templateId,
                        'education_level_name' => $level->education_level_name,
                        'education_level_short_name' => $level->education_level_short_name,
                        'description' => $level->description,
                        'sort_order' => $level->sort_order,
                        'is_active' => $level->is_active,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);

                    $EducationLevelIdMap[$level->configuration_template_education_level_id] = $newId;
                }


                // degree 
                $degreeIdMap = [];
                $degreetypes = DB::connection('mysql_second')->table('configuration_template_education_degrees')->get();

                foreach ($degreetypes as $type) {
                    $newId = DB::table('organization_education_degrees')->insertGetId([
                        'organization_id' => $organization->organization_id,
                        'organization_configuration_template_id' => $templateId,
                        'organization_education_level_id' => $EducationLevelIdMap[$type->configuration_template_education_level_id] ?? null, // ✅ Use the NEW mapped leave_type_id here
                        'education_degree_name' => $type->education_degree_name,
                        'education_degree_short_name' => $type->education_degree_short_name,
                        'description' => $type->description,
                        'sort_order' => $type->sort_order,
                        'is_active' => $type->is_active,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);

                    $degreeIdMap[$type->configuration_template_education_degree_id] = $newId;
                }

                // stream 
                $EducationStreamIdMap = [];
                $educationSteream = DB::connection('mysql_second')->table('configuration_template_education_streams')->get();
                foreach ($educationSteream as $stream) {
                    $newId = DB::table('organization_education_streams')->insertGetId([
                        'organization_id' => $organization->organization_id,
                        'organization_configuration_template_id' => $templateId,
                        'education_stream_name' => $stream->education_stream_name,
                        'education_stream_short_name' => $stream->education_stream_short_name,
                        'description' => $stream->description,
                        'sort_order' => $stream->sort_order,
                        'is_active' => $stream->is_active,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);

                    $EducationStreamIdMap[$stream->configuration_template_education_stream_id] = $newId;
                }


                // level education stream 
                $educationSteream = DB::connection('mysql_second')->table('configuration_template_education_level_degree_streams')->get();
                foreach ($educationSteream as $stream) {
                    $newId = DB::table('organization_education_level_degree_streams')->insertGetId([
                        'organization_id' => $organization->organization_id,
                        'organization_education_level_id' => $EducationLevelIdMap[$stream->configuration_template_education_level_id] ?? null,
                        'organization_education_degree_id' => $degreeIdMap[$stream->configuration_template_education_degree_id] ?? null,
                        'organization_education_stream_id' => $EducationStreamIdMap[$stream->configuration_template_education_stream_id] ?? null,
                        'is_active' => $stream->is_active,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }

                // organization languages 
                $languages = DB::connection('mysql_second')->table('configuration_template_languages')->get();
                foreach ($languages as $user) {
                    DB::table('organization_languages')->insert([
                        'organization_id' => $organization->organization_id,
                        'organization_configuration_template_id' => $templateId,
                        'language_name' => $user->language_name,
                        'language_code' => $user->language_code,
                        'description' => $user->description,
                        'created_by' => 'template',
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }

            }


            // holiday 
            $holidaytemplate = DB::connection('mysql_second')
                ->table('holiday_templates')
                ->where('general_country_id', $request->general_country_id)
                ->where('general_state_id', $request->general_state_id)
                ->first();

            if ($holidaytemplate) {
                $orgHolidayTemplateId = DB::table('organization_holiday_templates')->insertGetId([
                    'holiday_template_name' => $holidaytemplate->holiday_template_name,
                    'holiday_template_code' => $holidaytemplate->holiday_template_code,
                    'description' => $holidaytemplate->description,
                    'scope' => $holidaytemplate->scope,

                    'general_country_id' => $holidaytemplate->general_country_id,
                    'general_state_id' => $holidaytemplate->general_state_id,
                    'organization_id' => $organization->organization_id,
                    'created_by' => 'template',
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);


                // holiday calendar 
                $holidayCalendarIdMap = []; // template_calendar_id => new_organization_calendar_id

                $holidayCalendars = DB::connection('mysql_second')
                    ->table('holiday_template_holiday_calendars')
                    ->where('holiday_template_id', $holidaytemplate->holiday_template_id) // Filter by this template
                    ->get();

                foreach ($holidayCalendars as $calendar) {
                    $calendarId = DB::table('organization_holiday_calendars')->insertGetId([
                        'organization_holiday_template_id' => $orgHolidayTemplateId,
                        'organization_id' => $organization->organization_id,
                        'holiday_calendar_name' => $calendar->holiday_calendar_name,
                        'holiday_calendar_year_start_date' => $calendar->holiday_calendar_year_start_date,
                        'holiday_calendar_year_end_date' => $calendar->holiday_calendar_year_end_date,
                        'created_by' => 'template',
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);

                    $holidayCalendarIdMap[$calendar->holiday_template_holiday_calendar_id] = $calendarId;
                }


                // holday types
                $holidayTypeIdMap = [];
                $holidayTypes = DB::connection('mysql_second')
                    ->table('holiday_template_holiday_types')
                    ->where('holiday_template_id', $holidaytemplate->holiday_template_id) // Filter by this template
                    ->get();

                foreach ($holidayTypes as $type) {
                    $typeId = DB::table('organization_holiday_types')->insertGetId([
                        'organization_holiday_template_id' => $orgHolidayTemplateId,
                        'organization_id' => $organization->organization_id,
                        'holiday_type_name' => $type->holiday_type_name,
                        'description' => $type->description,
                        'created_by' => 'template',
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);

                    $holidayTypeIdMap[$type->holiday_template_holiday_type_id] = $typeId;
                }

                // holiday template holiday 
                $holidays = DB::connection('mysql_second')
                    ->table('holiday_template_holidays')
                    ->where('holiday_template_id', $holidaytemplate->holiday_template_id) // Filter by this template
                    ->get();

                foreach ($holidays as $holiday) {
                    DB::table('organization_holidays')->insert([
                        'organization_holiday_template_id' => $orgHolidayTemplateId,
                        'organization_id' => $organization->organization_id,
                        'organization_holiday_calendar_id' => $holidayCalendarIdMap[$holiday->holiday_template_holiday_calendar_id] ?? null,
                        'organization_holiday_type_id' => $holidayTypeIdMap[$holiday->holiday_template_holiday_type_id] ?? null,
                        'holiday_date' => $holiday->holiday_date,
                        'holiday_name' => $holiday->holiday_name,
                        'description' => $holiday->description,
                        'is_recurring' => $holiday->is_recurring,
                        'entry_source' => $holiday->entry_source,
                        'created_by' => 'template',
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }

            }

            // now make an auto login feature
            $token = JWTAuth::fromUser($application_user);
            $application_user->account_created = 1;
            $application_user->save();
            $role = [

                "system_role_name" => "Admin"
            ];
            $application_user->load('Client.Organization');
            $application_user->organization = $application_user->Client->Organization;
            $application_user->role = $role;


            $userType = OrganizationUserType::where('organization_id', $organization->organization_id)
                ->where('user_type_name', 'employee') // hardcoded value
                ->first();

            // add the adta in organization user table also 
            $orguser = OrganizationUser::create([
                'organization_id' => $organization->organization_id,
                'application_user_id' => $request->application_user_id,
                'organization_user_type_id' => $userType->organization_user_type_id,

            ]);


            // 10
            // create organizatio user role asignment   whemn person do login initialy
            // add its role in organizatiojn user role assignment 
            $role_assignment = OrganizationUserRoleAssignment::create([
                'organization_id' => $organization->organization_id,
                'assigned_at' => now(),
                'application_user_role_id' => 10,
                'organization_user_id' => $orguser->organization_user_id

            ]);

            // add permission of that admin  

            if (!$request->client_id) {
                ApplicationOrganizationAcive::create([
                    'application_user_id' => $request->application_user_id,
                    'organization_id' => $organization->organization_id,
                ]);
            }


            return response()->json([
                'message' => "Organization added successfully",
                'organization' => $organization,
                'token' => $token,
                'user' => $application_user,

            ], 201);




        } catch (\Illuminate\Database\QueryException $e) {
            return response()->json([
                'error' => 'Database error: ' . $e->getMessage()
            ], 500);

        } catch (\Exception $e) {

            return response()->json([
                'error' => 'Something went wrong: ' . $e->getMessage()
            ], 500);
        }
    }

    public function show(Request $request, $org_id)
    {
        try {
            $request->merge(['organization_id' => $org_id]);
            $validator = Validator::make($request->all(), [
                'organization_id' => 'required|integer|exists:organizations,organization_id',
            ]);
            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()], 422);
            }
            $organization = Organization::findOrFail($org_id);
            $organization->load(
                'businessprofile.generalCategory',
                'businessprofile.generalIndustry',
                'locations.city',
                'locations.state',
                'locations.country'
            );


            return response()->json([
                'message' => "Organization  data",

                'organization' => $organization
            ], 200);

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {

            return response()->json([
                'error' => 'Organization not found with the provided ID.'
            ], 404);
        }

    }
    public function update(Request $request, $org_id)
    {
        try {
            $request->merge(['organization_id' => $org_id]);

            // Validate organization existence
            $validator = Validator::make($request->all(), [
                'organization_id' => 'required|integer|exists:organizations,organization_id',
            ]);
            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()], 422);
            }

            if ($request->has('organization_name')) {
                $request->merge([
                    'organization_name' => trim($request->input('organization_name'))
                ]);
            }

            // Validation rules
            $rules = [
                'organization_name' => 'nullable|string|max:255',
                'organization_short_name' => 'nullable|string|max:100',
                'license_id' => 'nullable|string',
                'general_business_ownership_type_category_id' => 'nullable|integer|exists:general_business_ownership_type_categories,general_business_ownership_type_category_id',
                'general_industry_id' => 'nullable|integer|exists:general_industries,general_industry_id',
                'general_state_id' => 'nullable|integer|exists:general_states,general_state_id',
                'general_city_id' => 'nullable|integer|exists:general_cities,general_city_id',
            ];

            $validator = Validator::make($request->all(), $rules);
            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()], 422);
            }

            $organization = Organization::findOrFail($org_id);
            $businessProfile = OrganizationBusinessProfile::where('organization_id', $org_id)->first();
            $location = OrganizationLocation::where('organization_id', $org_id)->first();

            // Update only if value is not null/empty string
            if ($businessProfile) {
                $bpUpdates = [];

                if ($request->filled('general_business_ownership_type_category_id')) {
                    $bpUpdates['general_business_ownership_type_category_id'] = $request->input('general_business_ownership_type_category_id');
                }
                if ($request->filled('general_industry_id')) {
                    $bpUpdates['general_industry_id'] = $request->input('general_industry_id');
                }

                if (!empty($bpUpdates)) {
                    $businessProfile->update($bpUpdates);
                }
            }

            if ($location) {
                $locUpdates = [];

                if ($request->filled('general_state_id')) {
                    $locUpdates['general_state_id'] = $request->input('general_state_id');
                }

                if ($request->filled('general_city_id')) {
                    $locUpdates['general_city_id'] = $request->input('general_city_id');
                }

                if (!empty($locUpdates)) {
                    $location->update($locUpdates);
                }
            }

            // Only update org if relevant fields are present
            $orgUpdates = [];
            if ($request->filled('organization_name')) {
                $orgUpdates['organization_name'] = $request->input('organization_name');
            }
            if ($request->filled('organization_short_name')) {
                $orgUpdates['organization_short_name'] = $request->input('organization_short_name');
            }
            if ($request->filled('license_id')) {
                $orgUpdates['license_id'] = $request->input('license_id');
            }

            if (!empty($orgUpdates)) {
                $organization->update($orgUpdates);
            }

            return response()->json([
                'message' => "Organization Updated Successfully",
                'organization' => $organization
            ], 200);

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'error' => 'Organization not found with the provided ID.'
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Something went wrong. Please try again later.'
            ], 500);
        }
    }



    public function destroy(Request $request, $org_id)
    {
        try {
            DB::beginTransaction();

            $request->merge(['organization_id' => $org_id]);
            $validator = Validator::make($request->all(), [
                'organization_id' => 'required|integer|exists:organizations,organization_id',
            ]);
            if ($validator->fails()) {
                return response()->json(['error' => $validator->errors()], 422);
            }

            // check for organization count 
            $organizationcount = Organization::count();
            if ($organizationcount <= 1) {
                return response()->json(['message' => 'Organization Can be deleted '], 403);

            }


            $organization = Organization::findOrFail($org_id);

            DB::table('employees')->where('organization_id', $org_id)->delete();
            DB::table('employee_addresses')->where('organization_id', $org_id)->delete();
            DB::table('employee_bank_accounts')->where('organization_id', $org_id)->delete();
            DB::table('employee_contacts')->where('organization_id', $org_id)->delete();
            DB::table('employee_documents')->where('organization_id', $org_id)->delete();
            DB::table('employee_educations')->where('organization_id', $org_id)->delete();
            DB::table('organization_leave_entitlements')->where('organization_id', $org_id)->delete();
            DB::table('employee_leave_balances')->where('organization_id', $org_id)->delete();
            DB::table('organization_education_level_degree_streams')->where('organization_id', $org_id)->delete();
            DB::table('organization_education_degrees')->where('organization_id', $org_id)->delete();
            DB::table('organization_location_ownership_types')->where('organization_id', $org_id)->delete();
            DB::table('organization_leave_reasons')->where('organization_id', $org_id)->delete();
            DB::table('organization_leave_reason_types')->where('organization_id', $org_id)->delete();
            DB::table('organization_user_types')->where('organization_id', $org_id)->delete();
            DB::table('organization_education_levels')->where('organization_id', $org_id)->delete();
            DB::table('organization_education_streams')->where('organization_id', $org_id)->delete();
            DB::table('organization_business_ownership_types')->where('organization_id', $org_id)->delete();
            DB::table('organization_configuration_templates')->where('organization_id', $org_id)->delete();
            DB::table('employee_exits')->where('organization_id', $org_id)->delete();
            DB::table('employee_experiences')->where('organization_id', $org_id)->delete();
            DB::table('employee_family_members')->where('organization_id', $org_id)->delete();
            DB::table('employee_languages')->where('organization_id', $org_id)->delete();
            DB::table('employee_leaves')->where('organization_id', $org_id)->delete();
            DB::table('employee_medicals')->where('organization_id', $org_id)->delete();
            DB::table('organization_business_profiles')->where('organization_id', $org_id)->delete();
            DB::table('organization_business_registrations')->where('organization_id', $org_id)->delete();
            DB::table('organization_business_registration_types')->where('organization_id', $org_id)->delete();
            DB::table('organization_clients')->where('organization_id', $org_id)->delete();
            DB::table('organization_client_contacts')->where('organization_id', $org_id)->delete();
            DB::table('organization_department_locations')->where('organization_id', $org_id)->delete();
            DB::table('organization_designations')->where('organization_id', $org_id)->delete();
            DB::table('organization_departments')->where('organization_id', $org_id)->delete();

            DB::table('organization_employee_address_types')->where('organization_id', $org_id)->delete();
            DB::table('organization_employment_exit_reasons')->where('organization_id', $org_id)->delete();
            DB::table('organization_employment_exit_reason_types')->where('organization_id', $org_id)->delete();
            DB::table('organization_employment_statuses')->where('organization_id', $org_id)->delete();
            DB::table('organization_employment_types')->where('organization_id', $org_id)->delete();
            DB::table('organization_entities')->where('organization_id', $org_id)->delete();
            DB::table('organization_entity_types')->where('organization_id', $org_id)->delete();
            DB::table('organization_holidays')->where('organization_id', $org_id)->delete();

            DB::table('organization_holiday_calendars')->where('organization_id', $org_id)->delete();
            DB::table('organization_holiday_templates')->where('organization_id', $org_id)->delete();
            DB::table('organization_holiday_types')->where('organization_id', $org_id)->delete();
            DB::table('organization_identity_profiles')->where('organization_id', $org_id)->delete();
            DB::table('organization_leave_categories')->where('organization_id', $org_id)->delete();
            DB::table('organization_languages')->where('organization_id', $org_id)->delete();
            DB::table('organization_leave_duration_types')->where('organization_id', $org_id)->delete();
            DB::table('organization_leave_entitlement_rules')->where('organization_id', $org_id)->delete();
            DB::table('organization_leave_types')->where('organization_id', $org_id)->delete();





            DB::table('organization_locations')->where('organization_id', $org_id)->delete();

            DB::table('organization_project_categories')->where('organization_id', $org_id)->delete();
            DB::table('organization_project_milestones')->where('organization_id', $org_id)->delete();
            DB::table('organization_project_projects')->where('organization_id', $org_id)->delete();
            DB::table('organization_project_tasks')->where('organization_id', $org_id)->delete();
            DB::table('organization_project_task_recurrences')->where('organization_id', $org_id)->delete();
            DB::table('organization_project_task_templates')->where('organization_id', $org_id)->delete();
            DB::table('organization_project_task_time_logs')->where('organization_id', $org_id)->delete();
            DB::table('organization_project_task_types')->where('organization_id', $org_id)->delete();
            DB::table('organization_project_teams')->where('organization_id', $org_id)->delete();


            DB::table('organization_project_team_members')->where('organization_id', $org_id)->delete();
            DB::table('organization_project_types')->where('organization_id', $org_id)->delete();
            DB::table('organization_residential_ownership_types')->where('organization_id', $org_id)->delete();
            DB::table('organization_settings')->where('organization_id', $org_id)->delete();



            DB::table('employee_leave_balances')->where('organization_id', $org_id)->delete();
            DB::table('employee_leaves')->where('organization_id', $org_id)->delete();
            DB::table('employee_leave_monthly_summaries')->where('organization_id', $org_id)->delete();
            DB::table('organization_leave_categories')->where('organization_id', $org_id)->delete();
           
            DB::table('organization_leave_duration_types')->where('organization_id', $org_id)->delete();
            DB::table('organization_leave_entitlements')->where('organization_id', $org_id)->delete();
           
            DB::table('organization_units')->where('organization_id', $org_id)->delete();
            DB::table('organization_unit_types')->where('organization_id', $org_id)->delete();
            DB::table('organization_user_role_assignments')->where('organization_id', $org_id)->delete();
            DB::table('organization_user_roles')->where('organization_id', $org_id)->delete();
            DB::table('organization_users')->where('organization_id', $org_id)->delete();


            DB::table('organization_work_models')->where('organization_id', $org_id)->delete();
            DB::table('organization_work_shifts')->where('organization_id', $org_id)->delete();
            DB::table('organization_work_shift_types')->where('organization_id', $org_id)->delete();


            $organization->delete();
            DB::commit();

            return response()->json(['message' => 'Organization deleted successfully'], 200);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'error' => 'Organization not found with the provided ID.'
            ], 404);
        }
    }



    public function createUser(Request $request, $org_id)
    {

        $user = Auth::guard('applicationusers')->user();
        $organizationIds =
            $user->Client->Organization->pluck('organization_id')->toArray();
        if (!in_array($org_id, $organizationIds)) {
            return response()->json([
                'messages' => 'unauthorized'
            ], 401);
        }

        $request->merge(['organization_id' => $org_id]);

        $validator = Validator::make($request->all(), [
            'organization_id' => 'required|integer|exists:organizations,organization_id',
            'email' => 'required|email',
            'full_name' => 'required|string|max:255',
            'hash_password' => 'required|string|min:8',
            'country_phone_code' => 'required|string|max:10',
            'phone_number' => 'required|string|max:20|unique:application_users,phone_number',
            'organization_user_type_id' => 'required|integer|exists:organization_user_types,organization_user_type_id',

        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $user = ApplicationUsers::where('email', $request->email)->first();
        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'User with this email does not exist.',
            ], 404);
        }

        //   check if organization    user  client
        $data = Organization::findOrFail($org_id);
        $client_id = $data->client_id;

        $user->update([
            'full_name' => $request->input('full_name'),
            'password_hash' => Hash::make($request->input('hash_password')),
            'phone_number' => $request->input('phone_number'),
            'country_phone_code' => $request->input('country_phone_code'),
            'client_id' => $client_id
        ]);


        // store that user in organization id
        $storedata = OrganizationUser::create([
            'organization_id' => $org_id,
            'organization_user_type_id' => $request->organization_user_type_id,
            'application_user_id' => $user->application_user_id,

        ]);
        return response()->json([
            'success' => true,
            'message' => 'User Added successfully.',
            'data' => [
                'storedata' => $storedata,
            ],
        ], 201);
    }










}








