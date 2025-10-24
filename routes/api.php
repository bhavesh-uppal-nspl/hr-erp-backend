<?php


use App\Http\Controllers\ApplicationAuthController;
use App\Http\Controllers\ApplicationController\ApplicationErrorLogsController;
use App\Http\Controllers\ApplicationController\ApplicationModuleActionController;
use App\Http\Controllers\ApplicationController\ApplicationModuleController;
use App\Http\Controllers\ApplicationController\ApplicationUserController;
use App\Http\Controllers\ApplicationController\ApplicationUserloginController;
use App\Http\Controllers\ApplicationController\ApplicationUserPermissionAuditLogsController;
use App\Http\Controllers\ApplicationController\ApplicationUserPermissionController;
use App\Http\Controllers\ApplicationController\ApplicationUserRoleAssignmentController;
use App\Http\Controllers\ApplicationController\ApplicationUserRoleAssignmentLogsController;
use App\Http\Controllers\ApplicationController\ApplicationUserRoleController;
use App\Http\Controllers\ApplicationController\ApplicationUserRolePermissionAllController;
use App\Http\Controllers\ApplicationController\ApplicationUserRolePermissionController;
use App\Http\Controllers\AttendenceController\AttendenceBreakTypeController;
use App\Http\Controllers\AttendenceController\AttendenceDeviationReasonController;
use App\Http\Controllers\AttendenceController\AttendenceDeviationReasonTypeController;
use App\Http\Controllers\AttendenceController\AttendenceRecordController;
use App\Http\Controllers\AttendenceController\AttendenceSourceController;
use App\Http\Controllers\AttendenceController\AttendenceStatusTypeController;
use App\Http\Controllers\AttendenceController\AttendenceTimeLogController;
use App\Http\Controllers\ClientController\ClientController;
use App\Http\Controllers\DataGridController\GeneralDataGridController;
use App\Http\Controllers\DataGridController\OrganizationDataGridController;
use App\Http\Controllers\DataGridController\OrganizationUserDataGridContoller;
use App\Http\Controllers\DataGridController\OrganizationUserDataGridController;
use App\Http\Controllers\EmployeeController\EmployeeAddressController;
use App\Http\Controllers\EmployeeController\EmployeeBankAccountController;
use App\Http\Controllers\EmployeeController\EmployeeContactController;
use App\Http\Controllers\EmployeeController\EmployeeController;
use App\Http\Controllers\EmployeeController\EmployeeDocumentController;
use App\Http\Controllers\EmployeeController\EmployeeDocumentLinkController;
use App\Http\Controllers\EmployeeController\EmployeeDocumentTypeController;
use App\Http\Controllers\EmployeeController\EmployeeEducationController;
use App\Http\Controllers\EmployeeController\EmployeeExitController;
use App\Http\Controllers\EmployeeController\EmployeeExperienceController;
use App\Http\Controllers\EmployeeController\EmployeeFamilyMemberController;
use App\Http\Controllers\EmployeeController\EmployeeFilterControler;
use App\Http\Controllers\EmployeeController\EmployeeFormController;
use App\Http\Controllers\EmployeeController\EmployeeIncrementController;
use App\Http\Controllers\EmployeeController\EmployeeLanguageController;
use App\Http\Controllers\EmployeeController\EmployeeLeaveBalanceController;
use App\Http\Controllers\EmployeeController\EmployeeLeaveController;
use App\Http\Controllers\EmployeeController\EmployeeLeaveSummaryController;
use App\Http\Controllers\EmployeeController\EmployeeMedicalController;
use App\Http\Controllers\EmployeeController\EmployeeRecordController;
use App\Http\Controllers\EmployeeController\EmployeeWorkshiftAssignmentController;
use App\Http\Controllers\EmployeeController\EmployeeWorkshiftRotationAssignmentController;
use App\Http\Controllers\EmployeeController\FaceRecognitionController;
use App\Http\Controllers\EMSControllers\AdmissionController;
use App\Http\Controllers\EMSControllers\AssesmentResultsController;
use App\Http\Controllers\EMSControllers\AssesmentsController;
use App\Http\Controllers\EMSControllers\BatchClassesController;
use App\Http\Controllers\EMSControllers\BatchesController;
use App\Http\Controllers\EMSControllers\BatchStudentsController;
use App\Http\Controllers\EMSControllers\CertificatesController;
use App\Http\Controllers\EMSControllers\ClassAttendanceController;
use App\Http\Controllers\EMSControllers\CompaniesController;
use App\Http\Controllers\EMSControllers\DemoSessionsController;
use App\Http\Controllers\EMSControllers\FeeInstallmentsController;
use App\Http\Controllers\EMSControllers\LeadActivitiesController;
use App\Http\Controllers\EMSControllers\LeadContactTimingsController;
use App\Http\Controllers\EMSControllers\LeadsController;
use App\Http\Controllers\EMSControllers\LeadSourcesController;
use App\Http\Controllers\EMSControllers\LeadStagesController;
use App\Http\Controllers\EMSControllers\PlacementReferralsController;
use App\Http\Controllers\EMSControllers\RecruitmentAgenciesController;
use App\Http\Controllers\EMSControllers\StudentsController;
use App\Http\Controllers\EMSControllers\StudentsFeesController;
use App\Http\Controllers\EMSControllers\TrainingProgramCategoriesController;
use App\Http\Controllers\EMSControllers\TrainingProgramController;
use App\Http\Controllers\FunctionalController\EmployeeFunctionRoleController;
use App\Http\Controllers\FunctionalController\OrganizationFunctionRoleController;
use App\Http\Controllers\FunctionalController\OrganizationFunctionRoleSpecilizationController;
use App\Http\Controllers\GeneralController\GeneralBusinessOwnershipTypeCategoryController;
use App\Http\Controllers\GeneralController\GeneralResidentailOwnershipController;
use App\Http\Controllers\GeneralController\GeneralSettingDataTypeController;
use App\Http\Controllers\GeneralController\GeneralSettingTypeController;
use App\Http\Controllers\GeneralController\OrganizationSettingDataTypeController;
use App\Http\Controllers\InternController\InterAttendanceTimeLogController;
use App\Http\Controllers\InternController\InterFormController;
use App\Http\Controllers\InternController\InternAttendanceRecordController;
use App\Http\Controllers\InternController\InternCertificateController;
use App\Http\Controllers\InternController\InternController;
use App\Http\Controllers\InternController\InternDocumentTypeController;
use App\Http\Controllers\InternController\InternExitController;
use App\Http\Controllers\InternController\InternLeaveController;
use App\Http\Controllers\InternController\InternshipStagesController;
use App\Http\Controllers\InternController\InternshipTypeController;
use App\Http\Controllers\InternController\InternStipendController;
use App\Http\Controllers\InternController\IntershipStatusController;
use App\Http\Controllers\OrganizationController\OrganizationAttendanceBreakController;
use App\Http\Controllers\OrganizationController\OrganizationBusinessIdentityProfileController;
use App\Http\Controllers\OrganizationController\OrganizationBusinessOwnershipTypeController;
use App\Http\Controllers\OrganizationController\OrganizationBusinessProfileController;
use App\Http\Controllers\OrganizationController\OrganizationBusinessRegistrationController;
use App\Http\Controllers\OrganizationController\OrganizationBusinessRegistrationTypeController;
use App\Http\Controllers\OrganizationController\OrganizationController;
use App\Http\Controllers\OrganizationController\OrganizationEducationDegreeController;
use App\Http\Controllers\OrganizationController\OrganizationEducationLevelController;
use App\Http\Controllers\OrganizationController\OrganizationEducationStreamController;
use App\Http\Controllers\OrganizationController\OrganizationEmplExitReasonTypeController;
use App\Http\Controllers\OrganizationController\OrganizationEmplomentAddressTypeController;
use App\Http\Controllers\OrganizationController\OrganizationEmployeeIncrementTypeController;
use App\Http\Controllers\OrganizationController\OrganizationEmployeeProfileSectionController;
use App\Http\Controllers\OrganizationController\OrganizationEmploymentExitReasonController;
use App\Http\Controllers\OrganizationController\OrganizationEmploymentStagesController;
use App\Http\Controllers\OrganizationController\OrganizationEmploymentStatusController;
use App\Http\Controllers\OrganizationController\OrganizationEmpTypeController;
use App\Http\Controllers\OrganizationController\OrganizationHolidayCalendarController;
use App\Http\Controllers\OrganizationController\OrganizationHolidayController;
use App\Http\Controllers\OrganizationController\OrganizationHolidayTypeController;
use App\Http\Controllers\OrganizationController\OrganizationIdentityProfileController;
use App\Http\Controllers\OrganizationController\OrganizationLanguageController;
use App\Http\Controllers\OrganizationController\OrganizationLeaveCategoryController;
use App\Http\Controllers\OrganizationController\OrganizationLeaveEntitlmentController;
use App\Http\Controllers\OrganizationController\OrganizationLeavePolicyController;
use App\Http\Controllers\OrganizationController\OrganizationLeaveReasonController;
use App\Http\Controllers\OrganizationController\OrganizationLeaveReasonTypeController;
use App\Http\Controllers\OrganizationController\OrganizationLeaveTypeController;
use App\Http\Controllers\OrganizationController\OrganizationLocationOwnershipTypeController;

use App\Http\Controllers\GeneralController\GeneralIndustryController;
use App\Http\Controllers\GeneralController\GeneralBusinessRegistrationTypeController;
use App\Http\Controllers\GeneralController\GeneralLeaveDurationTypeController;
use App\Http\Controllers\OrganizationController\OrganizationBusinessUnitController;
use App\Http\Controllers\OrganizationController\OrganizationBusinessDivisionController;
use App\Http\Controllers\OrganizationController\OrganizationLocationController;
use App\Http\Controllers\OrganizationController\OrganizationDepartmentController;
use App\Http\Controllers\OrganizationController\OrganizationDepartmentLocationController;
use App\Http\Controllers\OrganizationController\OrganizationSettingController;
use App\Http\Controllers\OrganizationController\OrganizationSettingTypeController;
use App\Http\Controllers\OrganizationController\OrganizationShiftRotationPatternController;
use App\Http\Controllers\OrganizationController\OrganizationUnitController;
use App\Http\Controllers\OrganizationController\OrganizationUnitTypesController;
use App\Http\Controllers\OrganizationController\OrganizationUserController;
use App\Http\Controllers\OrganizationController\OrganizationUserTypeController;
use App\Http\Controllers\OrganizationController\OrganizationWorkModelController;
use App\Http\Controllers\OrganizationController\OrganizationWorkShiftBreakController;
use App\Http\Controllers\OrganizationController\OrganizationWorkShiftController;
use App\Http\Controllers\OrganizationController\OrganizationWorkshiftRotationDaysController;
use App\Http\Controllers\OrganizationController\OrganizationWorkShiftTypeController;
use App\Http\Controllers\OrganizationController\OrganizationDesignationController;
use App\Http\Controllers\GeneralController\GeneralLocationOwnershipTypeController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\PayrollController\PayrollComponentController;
use App\Http\Controllers\PayrollController\PayrollComponentTypesController;
use App\Http\Controllers\PayrollController\PayrollCycleController;
use App\Http\Controllers\PayrollController\PayrollEmployeeSalaryStructureController;
use App\Http\Controllers\PayrollController\PayrollSalaryStructureComponentController;
use App\Http\Controllers\PayrollController\PayrollSlabsController;
use App\Http\Controllers\PermissionController\PermissionController;
use App\Http\Controllers\ProjectControllers\OrganizationClientController;
use App\Http\Controllers\ProjectControllers\OrganizationClientContactController;
use App\Http\Controllers\ProjectControllers\OrganizationProjectCategoryController;
use App\Http\Controllers\ProjectControllers\OrganizationProjectController;
use App\Http\Controllers\ProjectControllers\OrganizationProjectMilestoneController;
use App\Http\Controllers\ProjectControllers\OrganizationProjectTaskController;
use App\Http\Controllers\ProjectControllers\OrganizationProjectTaskRecurrenceController;
use App\Http\Controllers\ProjectControllers\OrganizationProjectTaskTemplateController;
use App\Http\Controllers\ProjectControllers\OrganizationProjectTaskTypeController;
use App\Http\Controllers\ProjectControllers\OrganizationProjectTeamController;
use App\Http\Controllers\ProjectControllers\OrganizationProjectTeamMemberController;
use App\Http\Controllers\ProjectControllers\OrganizationProjectTypeController;
use App\Http\Controllers\ProjectControllers\OrganizationTaskTimeLogController;
use App\Http\Controllers\GeneralController\GeneralCountryController;
use App\Http\Controllers\GeneralController\GeneralStateController;
use App\Http\Controllers\GeneralController\GeneralCityController;
use App\Http\Controllers\GeneralController\GeneralSystemRoleController;
use App\Http\Controllers\OrganizationController\OrganizatioRegidentialOwnerTypeController;
use App\Http\Controllers\ProjectControllers\OrganizationProjectMilestoneTemplatesController;
use App\Http\Controllers\ProjectControllers\OrganizationProjectSubCategoriesController;
use App\Http\Controllers\ProjectControllers\OrganizationProjectTemplatesController;
use App\Http\Controllers\ProjectControllers\OrganizationProjectTemplatesTasksController;
use App\Http\Controllers\ProjectControllers\OrganizationProjectTaskCategoryController;
use App\Http\Controllers\ProjectControllers\OrganizationProjectTaskSubCategoryController;
use App\Models\AttendenceModels\EmployeeAttendenceTimeLog;
use App\Models\OrganizationModel\OrganizationEmploymentIncrementTypes;
use App\Models\PayrollModels\PayrollComponent;
use Illuminate\Support\Facades\Route;

Route::prefix('general')->group(function () {

    Route::prefix('/city')->group(function () {
        Route::get('/', [GeneralCityController::class, 'indexV1']);
    });

    // general indsutry 
    Route::prefix('/industry')->group(function () {
        Route::get('/', [GeneralIndustryController::class, 'index']);
        Route::post('/', [GeneralIndustryController::class, 'store']);
        Route::put('/{industry_id}', [GeneralIndustryController::class, 'update']);
        Route::get('/{industry_id}', [GeneralIndustryController::class, 'show']);
        Route::delete('/{industry_id}', [GeneralIndustryController::class, 'destroy']);
    });



    Route::prefix('/ownership-category')->group(function () {
        Route::get('/', [GeneralBusinessOwnershipTypeCategoryController::class, 'index']);
        Route::post('/', [GeneralBusinessOwnershipTypeCategoryController::class, 'store']);
        Route::put('/{ownership_type_category_id}', [GeneralBusinessOwnershipTypeCategoryController::class, 'update']);
        Route::get('/{ownership_type_category_id}', [GeneralBusinessOwnershipTypeCategoryController::class, 'show']);
        Route::delete('/{ownership_type_category_id}', [GeneralBusinessOwnershipTypeCategoryController::class, 'destroy']);
    });

    // general data type
    Route::prefix('/data-type')->group(function () {
        Route::get('/', [GeneralSettingDataTypeController::class, 'index']);
        Route::post('/', [GeneralSettingDataTypeController::class, 'store']);
        Route::put('/{data_type_id}', [GeneralSettingDataTypeController::class, 'update']);
        Route::get('/{data_type_id}', [GeneralSettingDataTypeController::class, 'show']);
        Route::delete('/{data_type_id}', [GeneralSettingDataTypeController::class, 'destroy']);
    });

    // general business regsitration type
    Route::prefix('/businessregistrationtype')->group(function () {
        Route::get('/', [GeneralBusinessRegistrationTypeController::class, 'index']);
        Route::post('/', [GeneralBusinessRegistrationTypeController::class, 'store']);
        Route::put('/{business_reg_type_id}', [GeneralBusinessRegistrationTypeController::class, 'update']);
        Route::get('/{business_reg_type_id}', [GeneralBusinessRegistrationTypeController::class, 'show']);
        Route::delete('/{business_reg_type_id}', [GeneralBusinessRegistrationTypeController::class, 'destroy']);
    });


    // locatio  ownership type 
    Route::prefix('/locationownershiptype')->group(function () {
        Route::get('/', [GeneralLocationOwnershipTypeController::class, 'index']);
        Route::post('/', [GeneralLocationOwnershipTypeController::class, 'store']);
        Route::put('/{location_ownership_type_id}', [GeneralLocationOwnershipTypeController::class, 'update']);
        Route::get('/{location_ownership_type_id}', [GeneralLocationOwnershipTypeController::class, 'show']);
        Route::delete('/{location_ownership_type_id}', [GeneralLocationOwnershipTypeController::class, 'destroy']);
    });

    // general stsyrem roles 
    Route::prefix('/systemrole')->group(function () {
        Route::get('/', [GeneralSystemRoleController::class, 'index']);
        Route::post('/', [GeneralSystemRoleController::class, 'store']);
        Route::put('/{system_role_id}', [GeneralSystemRoleController::class, 'update']);
        Route::get('/{system_role_id}', [GeneralSystemRoleController::class, 'show']);
        Route::delete('/{system_role_id}', [GeneralSystemRoleController::class, 'destroy']);
    });

    // seting types 
    Route::prefix('/setting-type')->group(function () {
        Route::get('/', [GeneralSettingTypeController::class, 'index']);
        Route::post('/', [GeneralSettingTypeController::class, 'store']);
        Route::put('/{system_role_id}', [GeneralSettingTypeController::class, 'update']);
        Route::get('/{system_role_id}', [GeneralSettingTypeController::class, 'show']);
        Route::delete('/{system_role_id}', [GeneralSettingTypeController::class, 'destroy']);
    });


    // general residential ownership type
    Route::prefix('/residential-ownership')->group(function () {
        Route::get('/', [GeneralResidentailOwnershipController::class, 'index']);
        Route::post('/', [GeneralResidentailOwnershipController::class, 'store']);
        Route::put('/{residential_ownership_type_id}', [GeneralResidentailOwnershipController::class, 'update']);
        Route::get('/{residential_ownership_type_id}', [GeneralSystemRoleController::class, 'show']);
        Route::delete('/{residential_ownership_type_id}', [GeneralResidentailOwnershipController::class, 'destroy']);
    });

    // general countries 
    // Route::prefix('/countries')->group(function () {
    //     Route::get('/', [GeneralCountryController::class, 'index']);
    //     Route::post('/', [GeneralCountryController::class, 'store']);
    //     Route::put('/{country_id}', [GeneralCountryController::class, 'update']);
    //     Route::get('/{country_id}', [GeneralCountryController::class, 'show']);
    //     Route::delete('/{country_id}', [GeneralCountryController::class, 'destroy']);

    //     // general states 
    //     Route::prefix('{general_country_id}/states')->group(function () {
    //         Route::get('/', [GeneralStateController::class, 'index']);
    //         Route::post('/', [GeneralStateController::class, 'store']);
    //         Route::put('/{general_state_id}', [GeneralStateController::class, 'update']);
    //         Route::get('/{general_state_id}', [GeneralStateController::class, 'show']);
    //         Route::delete('/{general_state_id}', [GeneralStateController::class, 'destroy']);

    //         // gheneral cities 

    //     });
    // });


    Route::prefix('/countries')->group(function () {
        Route::get('/', [GeneralCountryController::class, 'index']);
        Route::get('/all', [GeneralCountryController::class, 'indexV2']);
        Route::get('/v1', [GeneralCountryController::class, 'indexV1']);
        Route::post('/', [GeneralCountryController::class, 'store']);
        Route::put('/{country_id}', [GeneralCountryController::class, 'update']);
        Route::get('/{country_id}', [GeneralCountryController::class, 'show']);
        Route::delete('/{country_id}', [GeneralCountryController::class, 'destroy']);

        // general states 
        Route::prefix('{general_country_id}/states')->group(function () {
            Route::get('/{general_state_id}/cities', [GeneralCityController::class, 'indexV2']);
            Route::get('/', [GeneralStateController::class, 'index']);
            Route::get('/v1', [GeneralStateController::class, 'indexV1']);
            Route::post('/', [GeneralStateController::class, 'store']);
            Route::put('/{general_state_id}', [GeneralStateController::class, 'update']);
            Route::get('/{general_state_id}', [GeneralStateController::class, 'show']);
            Route::delete('/{general_state_id}', [GeneralStateController::class, 'destroy']);

            // gheneral cities 

        });
    });





    Route::prefix('/cities')->group(function () {
        Route::get('/', [GeneralCityController::class, 'index']);
        Route::post('/', [GeneralCityController::class, 'store']);
        Route::put('/{general_city_id}', [GeneralCityController::class, 'update']);
        Route::get('/{general_city_id}', [GeneralCityController::class, 'show']);
        Route::delete('/{general_city_id}', [GeneralCityController::class, 'destroy']);
    });

    // leave duration    
    Route::prefix('/leave-duration')->group(function () {
        Route::get('/', [GeneralLeaveDurationTypeController::class, 'index']);
        Route::post('/', [GeneralLeaveDurationTypeController::class, 'store']);
        Route::put('/{duration_id}', [GeneralLeaveDurationTypeController::class, 'update']);
        Route::get('/{duration_id}', [GeneralLeaveDurationTypeController::class, 'show']);
        Route::delete('/{duration_id}', [GeneralLeaveDurationTypeController::class, 'destroy']);
    });
});


Route::prefix('clients')->group(function () {

    Route::prefix('/')->group(function () {
        Route::get('/', [ClientController::class, 'index']);
        Route::post('/', [ClientController::class, 'store']);
        Route::put('/{client_id}', [ClientController::class, 'update']);
        Route::get('/{client_id}', [ClientController::class, 'show']);
        Route::delete('/{client_id}', [ClientController::class, 'destroy']);
    });


});

// Organization Controller
Route::prefix('organizations')->group(
    function () {


        // ectra 
        // organization business ownership type
        Route::prefix('/extra')->group(function () {
            Route::post('/active', [AuthController::class, 'updatestatus']);

        });

        Route::prefix('/businessnownershiptype')->group(function () {
            Route::get('/', [OrganizationBusinessOwnershipTypeController::class, 'index']);
            Route::post('/', [OrganizationBusinessOwnershipTypeController::class, 'store']);
            Route::put('/{organization_ownership_type_id}', [OrganizationBusinessOwnershipTypeController::class, 'update']);
            Route::get('/{organization_ownership_type_id}', [OrganizationBusinessOwnershipTypeController::class, 'show']);
            Route::delete('/{organization_ownership_type_id}', [OrganizationBusinessOwnershipTypeController::class, 'destroy']);
        });

        // create organization usesr 
    


        Route::prefix('/')->group(function () {
            Route::get('/', [OrganizationController::class, 'index']);
            Route::get('/v1', [OrganizationController::class, 'indexV1']);                   /// add this here 
            Route::get('/employees', [OrganizationController::class, 'indexEmployees']);    /// add this here 
            Route::post('/', [OrganizationController::class, 'store']);
            Route::put('/{org_id}', [OrganizationController::class, 'update']);
            Route::get('/{org_id}', [OrganizationController::class, 'show']);
            Route::delete('/{org_id}', [OrganizationController::class, 'destroy']);


            Route::middleware('auth:applicationusers')->group(function () {



                // organization business ownership type
                Route::prefix('/{org_id}/businessownershiptype')->group(function () {
                    Route::get('/', [OrganizationBusinessOwnershipTypeController::class, 'index']);
                    Route::post('/', [OrganizationBusinessOwnershipTypeController::class, 'store']);
                    Route::put('/{ownership_type_id}', [OrganizationBusinessOwnershipTypeController::class, 'update']);
                    Route::get('/{ownership_type_id}', [OrganizationBusinessOwnershipTypeController::class, 'show']);
                    Route::delete('/{ownership_type_id}', [OrganizationBusinessOwnershipTypeController::class, 'destroy']);
                });


                // business profile 
    


                // organization business ownership type
                Route::prefix('/{org_id}/businessprofile')->group(function () {
                    Route::get('/', [OrganizationBusinessProfileController::class, 'index']);
                    Route::post('/', [OrganizationBusinessProfileController::class, 'store']);
                    Route::put('/{profile_id}', [OrganizationBusinessProfileController::class, 'update']);
                    Route::get('/{profile_id}', [OrganizationBusinessProfileController::class, 'show']);
                    Route::delete('/{profile_id}', [OrganizationBusinessProfileController::class, 'destroy']);
                });


                // organization profile
                Route::prefix('/{org_id}/profile')->group(function () {
                    Route::get('/', [OrganizationIdentityProfileController::class, 'index']);
                    Route::post('/', [OrganizationIdentityProfileController::class, 'store']);
                    Route::post('/{profile_id}', [OrganizationIdentityProfileController::class, 'update']);
                    Route::get('/{profile_id}', [OrganizationIdentityProfileController::class, 'show']);
                    Route::delete('/{profile_id}', [OrganizationIdentityProfileController::class, 'destroy']);
                });

                // organization-department
                Route::prefix('/{org_id}/department')->group(function () {
                    Route::get('/', [OrganizationDepartmentController::class, 'index']);
                    Route::post('/', [OrganizationDepartmentController::class, 'store']);
                    Route::put('/{department_id}', [OrganizationDepartmentController::class, 'update']);
                    Route::get('/{department_id}', [OrganizationDepartmentController::class, 'show']);
                    Route::delete('/{department_id}', [OrganizationDepartmentController::class, 'destroy']);

                });

                // department- location 
                Route::prefix('/{org_id}/location-ownership-type')->group(function () {
                    Route::get('/', [OrganizationLocationOwnershipTypeController::class, 'index']);
                    Route::post('/', [OrganizationLocationOwnershipTypeController::class, 'store']);
                    Route::get('/{ownershiptype_id}', [OrganizationLocationOwnershipTypeController::class, 'show']);
                    Route::put('/{ownershiptype_id}', [OrganizationLocationOwnershipTypeController::class, 'update']);
                    Route::delete('/{ownershiptype_id}', [OrganizationLocationOwnershipTypeController::class, 'destroy']);
                });

                // business registration 
                Route::prefix('/{org_id}/business-registration')->group(function () {
                    Route::get('/', [OrganizationBusinessRegistrationController::class, 'index']);
                    Route::post('/', [OrganizationBusinessRegistrationController::class, 'store']);
                    Route::put('/{business_reg_id}', [OrganizationBusinessRegistrationController::class, 'update']);
                    Route::get('/{business_reg_id}', [OrganizationBusinessRegistrationController::class, 'show']);
                    Route::delete('/{business_reg_id}', [OrganizationBusinessRegistrationController::class, 'destroy']);
                });

                // business registration 
                Route::prefix('/{org_id}/business-registration-type')->group(function () {
                    Route::get('/', [OrganizationBusinessRegistrationTypeController::class, 'index']);
                    Route::post('/', [OrganizationBusinessRegistrationTypeController::class, 'store']);
                    Route::put('/{business_reg_type_id}', [OrganizationBusinessRegistrationTypeController::class, 'update']);
                    Route::get('/{business_reg_type_id}', [OrganizationBusinessRegistrationTypeController::class, 'show']);
                    Route::delete('/{business_reg_type_id}', [OrganizationBusinessRegistrationTypeController::class, 'destroy']);
                });

                // organization business unit 
                Route::prefix('/{org_id}/business-unit')->group(function () {
                    Route::get('/', [OrganizationBusinessUnitController::class, 'index']);
                    Route::post('/', [OrganizationBusinessUnitController::class, 'store']);
                    Route::put('/{business_unit_id}', [OrganizationBusinessUnitController::class, 'update']);
                    Route::get('/{business_unit_id}', [OrganizationBusinessUnitController::class, 'show']);
                    Route::delete('/{business_unit_id}', [OrganizationBusinessUnitController::class, 'destroy']);
                });


                // businees ownership type 
                Route::prefix('/{org_id}/business-ownership-type')->group(function () {
                    Route::get('/', [OrganizationBusinessOwnershipTypeController::class, 'index']);
                    Route::post('/', [OrganizationBusinessOwnershipTypeController::class, 'store']);
                    Route::put('/{ownership_type_id}', [OrganizationBusinessOwnershipTypeController::class, 'update']);
                    Route::get('/{ownership_type_id}', [OrganizationBusinessOwnershipTypeController::class, 'show']);
                    Route::delete('/{ownership_type_id}', [OrganizationBusinessOwnershipTypeController::class, 'destroy']);
                });

                // organization business division 
                Route::prefix('/{org_id}/business-division')->group(function () {
                    Route::get('/', [OrganizationBusinessDivisionController::class, 'index']);
                    Route::post('/', [OrganizationBusinessDivisionController::class, 'store']);
                    Route::put('/{business_division_id}', [OrganizationBusinessDivisionController::class, 'update']);
                    Route::get('/{business_division_id}', [OrganizationBusinessDivisionController::class, 'show']);
                    Route::delete('/{business_division_id}', [OrganizationBusinessDivisionController::class, 'destroy']);
                });

                // organization location 
                Route::prefix('/{org_id}/location')->group(function () {
                    Route::get('/', [OrganizationLocationController::class, 'index']);
                    Route::post('/', [OrganizationLocationController::class, 'store']);
                    Route::put('/{location_id}', [OrganizationLocationController::class, 'update']);
                    Route::get('/{location_id}', [OrganizationLocationController::class, 'show']);
                    Route::delete('/{location_id}', [OrganizationLocationController::class, 'destroy']);
                });

                // organization unit types
                Route::prefix('/{org_id}/units-types')->group(function () {
                    Route::get('/', [OrganizationUnitTypesController::class, 'index']);
                    Route::post('/', [OrganizationUnitTypesController::class, 'store']);
                    Route::put('/{unit_type_id}', [OrganizationUnitTypesController::class, 'update']);
                    Route::get('/{unit_type_id}', [OrganizationUnitTypesController::class, 'show']);
                    Route::delete('/{unit_type_id}', [OrganizationUnitTypesController::class, 'destroy']);
                });


                // organization unit 
                Route::prefix('/{org_id}/units')->group(function () {
                    Route::get('/', [OrganizationUnitController::class, 'index']);
                    Route::post('/', [OrganizationUnitController::class, 'store']);
                    Route::put('/{unit_id}', [OrganizationUnitController::class, 'update']);
                    Route::get('/{unit_id}', [OrganizationUnitController::class, 'show']);
                    Route::delete('/{unit_id}', [OrganizationUnitController::class, 'destroy']);
                });

                // organization employemnt residentail  type 
                Route::prefix('/{org_id}/residential-ownership-type')->group(function () {
                    Route::get('/', [OrganizatioRegidentialOwnerTypeController::class, 'index']);
                    Route::post('/', [OrganizatioRegidentialOwnerTypeController::class, 'store']);
                    Route::put('/{residential_type_id}', [OrganizatioRegidentialOwnerTypeController::class, 'update']);
                    Route::get('/{residential_type_id}', [OrganizatioRegidentialOwnerTypeController::class, 'show']);
                    Route::delete('/{residential_type_id}', [OrganizatioRegidentialOwnerTypeController::class, 'destroy']);
                });

                // department- location 
                Route::prefix('/{org_id}/department-location')->group(function () {
                    Route::get('/', [OrganizationDepartmentLocationController::class, 'index']);
                    Route::post('/', [OrganizationDepartmentLocationController::class, 'store']);
                    Route::get('/{department_location_id}', [OrganizationDepartmentLocationController::class, 'show']);
                    Route::put('/{department_location_id}', [OrganizationDepartmentLocationController::class, 'update']);
                    Route::delete('/{department_location_id}', [OrganizationDepartmentLocationController::class, 'destroy']);
                });



                Route::prefix('/{org_id}/designation')->group(function () {
                    Route::get('/', [OrganizationDesignationController::class, 'index']);
                    Route::get('/v1', [OrganizationDesignationController::class, 'indexV1']);
                    Route::post('/', [OrganizationDesignationController::class, 'store']);
                    Route::put('/{designation_id}', [OrganizationDesignationController::class, 'update']);
                    Route::get('/{designation_id}', [OrganizationDesignationController::class, 'show']);
                    Route::delete('/{designation_id}', [OrganizationDesignationController::class, 'destroy']);
                });

                // organization user 
                Route::prefix('/{org_id}/user')->group(function () {
                    Route::get('/', [OrganizationUserController::class, 'index']);
                    Route::get('/{user_id}', [OrganizationUserController::class, 'show']);
                    Route::put('/{user_id}', [OrganizationUserController::class, 'updateUser']);
                    Route::put('/{user_id}/toggle', [OrganizationUserController::class, 'updateStatus']);
                    Route::delete('/{user_id}', [OrganizationUserController::class, 'destroy']);
                    // create new user with email 
                    Route::post('/create-user-v1', [OrganizationUserController::class, 'createuserV1']);
                    // create user v2 email verified 
                    Route::post('/create-user-v2', [OrganizationUserController::class, 'createuserV2']);
                    // enter data 
                    Route::post('/create-user-v3', [OrganizationUserController::class, 'createuserV3']);

                    // create new organization user 
                    Route::post('/create-new-user', [OrganizationUserController::class, 'createUsernew']);


                });

                // organization -employemnt type 
                Route::prefix('/{org_id}/employemnt-type')->group(function () {
                    Route::get('/', [OrganizationEmpTypeController::class, 'index']);
                    Route::post('/', [OrganizationEmpTypeController::class, 'store']);
                    Route::put('/{employment_type_id}', [OrganizationEmpTypeController::class, 'update']);
                    Route::get('/{employment_type_id}', [OrganizationEmpTypeController::class, 'show']);
                    Route::delete('/{employment_type_id}', [OrganizationEmpTypeController::class, 'destroy']);
                });







                // organization-employemt-status 
                Route::prefix('/{org_id}/employment-exit-reason-type')->group(function () {
                    Route::get('/', [OrganizationEmplExitReasonTypeController::class, 'index']);
                    Route::post('/', [OrganizationEmplExitReasonTypeController::class, 'store']);
                    Route::put('/{status_id}', [OrganizationEmplExitReasonTypeController::class, 'update']);
                    Route::get('/{status_id}', [OrganizationEmplExitReasonTypeController::class, 'show']);
                    Route::delete('/{status_id}', [OrganizationEmplExitReasonTypeController::class, 'destroy']);
                });


                Route::prefix('/{org_id}/employee-profile-section')->group(function () {
                    Route::get('/', [OrganizationEmployeeProfileSectionController::class, 'index']);

                });



                // organization-employemt-status 
                Route::prefix('/{org_id}/employment-status')->group(function () {
                    Route::get('/', [OrganizationEmploymentStatusController::class, 'index']);
                    Route::post('/', [OrganizationEmploymentStatusController::class, 'store']);
                    Route::put('/{status_id}', [OrganizationEmploymentStatusController::class, 'update']);
                    Route::get('/{status_id}', [OrganizationEmploymentStatusController::class, 'show']);
                    Route::delete('/{status_id}', [OrganizationEmploymentStatusController::class, 'destroy']);
                });

                Route::prefix('/{org_id}/employment-stages')->group(function () {
                    Route::get('/', [OrganizationEmploymentStagesController::class, 'index']);
                    Route::post('/', [OrganizationEmploymentStagesController::class, 'store']);
                    Route::put('/{status_id}', [OrganizationEmploymentStagesController::class, 'update']);
                    Route::get('/{status_id}', [OrganizationEmploymentStagesController::class, 'show']);
                    Route::delete('/{status_id}', [OrganizationEmploymentStagesController::class, 'destroy']);
                });

                //organization-employemt-exit reason
                Route::prefix('/{org_id}/employemnt-exit-reason')->group(function () {
                    Route::get('/', [OrganizationEmploymentExitReasonController::class, 'index']);
                    Route::get('/getexit/{exitreasonType_id}', [OrganizationEmploymentExitReasonController::class, 'index1']);
                    Route::post('/', [OrganizationEmploymentExitReasonController::class, 'store']);
                    Route::put('/{exit_reason_id}', [OrganizationEmploymentExitReasonController::class, 'update']);
                    Route::get('/{exit_reason_id}', [OrganizationEmploymentExitReasonController::class, 'show']);
                    Route::delete('/{exit_reason_id}', [OrganizationEmploymentExitReasonController::class, 'destroy']);
                });

                // business - identity profilr 
                Route::prefix('/{org_id}/business-identity-profile')->group(function () {
                    Route::get('/', [OrganizationBusinessIdentityProfileController::class, 'index']);
                    Route::post('/', [OrganizationBusinessIdentityProfileController::class, 'store']);
                    Route::put('/', [OrganizationBusinessIdentityProfileController::class, 'update']); // no extra ID in URL
                    Route::get('/business/{business_profile_id}', [OrganizationBusinessIdentityProfileController::class, 'showBusiness']);
                    Route::get('/identity/{identity_profile_id}', [OrganizationBusinessIdentityProfileController::class, 'showIdentity']);
                });



                // organization employment address type 
                Route::prefix('/{org_id}/employemnt-addresstype')->group(function () {
                    Route::get('/', [OrganizationEmplomentAddressTypeController::class, 'index']);
                    Route::post('/', [OrganizationEmplomentAddressTypeController::class, 'store']);
                    Route::put('/{address_type_id}', [OrganizationEmplomentAddressTypeController::class, 'update']);
                    Route::get('/{address_type_id}', [OrganizationEmplomentAddressTypeController::class, 'show']);
                    Route::delete('/{address_type_id}', [OrganizationEmplomentAddressTypeController::class, 'destroy']);
                });



                // organization holiday calendar
                Route::prefix('/{org_id}/holiday-calendar')->group(function () {
                    Route::get('/', [OrganizationHolidayCalendarController::class, 'index']);
                    Route::post('/', [OrganizationHolidayCalendarController::class, 'store']);
                    Route::put('/{calendar_id}', [OrganizationHolidayCalendarController::class, 'update']);
                    Route::get('/{calendar_id}', [OrganizationHolidayCalendarController::class, 'show']);
                    Route::delete('/{calendar_id}', [OrganizationHolidayCalendarController::class, 'destroy']);
                });

                // organization holiday types 
                Route::prefix('/{org_id}/holiday-type')->group(function () {
                    Route::get('/', [OrganizationHolidayTypeController::class, 'index']);
                    Route::post('/', [OrganizationHolidayTypeController::class, 'store']);
                    Route::put('/{holiday_type_id}', [OrganizationHolidayTypeController::class, 'update']);
                    Route::get('/{holiday_type_id}', [OrganizationHolidayTypeController::class, 'show']);
                    Route::delete('/{holiday_type_id}', [OrganizationHolidayTypeController::class, 'destroy']);
                });

                // organization holiday 
                Route::prefix('/{org_id}/holiday')->group(function () {
                    Route::get('/', [OrganizationHolidayController::class, 'index']);
                    Route::post('/', [OrganizationHolidayController::class, 'store']);
                    Route::put('/{holiday_id}', [OrganizationHolidayController::class, 'update']);
                    Route::get('/{holiday_id}', [OrganizationHolidayController::class, 'show']);
                    Route::delete('/{holiday_id}', [OrganizationHolidayController::class, 'destroy']);
                });

                // organization leave category 
                Route::prefix('/{org_id}/leave-category')->group(function () {
                    Route::get('/', [OrganizationLeaveCategoryController::class, 'index']);
                    Route::post('/', [OrganizationLeaveCategoryController::class, 'store']);
                    Route::put('/{leave_category_id}', [OrganizationLeaveCategoryController::class, 'update']);
                    Route::get('/{leave_category_id}', [OrganizationLeaveCategoryController::class, 'show']);
                    Route::delete('/{leave_category_id}', [OrganizationLeaveCategoryController::class, 'destroy']);
                });

                // organization leave type 
                Route::prefix('/{org_id}/leave-type')->group(function () {
                    Route::get('/', [OrganizationLeaveTypeController::class, 'index']);
                    Route::post('/', [OrganizationLeaveTypeController::class, 'store']);
                    Route::put('/{leave_type_id}', [OrganizationLeaveTypeController::class, 'update']);
                    Route::get('/{leave_type_id}', [OrganizationLeaveTypeController::class, 'show']);
                    Route::delete('/{leave_type_id}', [OrganizationLeaveTypeController::class, 'destroy']);

                });


                //organization languages
    
                Route::prefix('/{org_id}/languages')->group(function () {
                    Route::get('/', [OrganizationLanguageController::class, 'index']);
                    Route::post('/', [OrganizationLanguageController::class, 'store']);
                    Route::put('/{language_id}', [OrganizationLanguageController::class, 'update']);
                    Route::get('/{language_id}', [OrganizationLanguageController::class, 'show']);
                    Route::delete('/{language_id}', [OrganizationLanguageController::class, 'destroy']);

                });


                // organization leave reason 
                Route::prefix('/{org_id}/leave-reason-type')->group(function () {
                    Route::get('/', [OrganizationLeaveReasonTypeController::class, 'index']);
                    Route::post('/', [OrganizationLeaveReasonTypeController::class, 'store']);
                    Route::put('/{leave_reason_type_id}', [OrganizationLeaveReasonTypeController::class, 'update']);
                    Route::get('/{leave_reason_type_id}', [OrganizationLeaveReasonTypeController::class, 'show']);
                    Route::delete('/{leave_reason_type_id}', [OrganizationLeaveReasonTypeController::class, 'destroy']);

                });

                // organization leave reason 
                Route::prefix('/{org_id}/leave-reason')->group(function () {
                    Route::get('/', [OrganizationLeaveReasonController::class, 'index']);
                    Route::post('/', [OrganizationLeaveReasonController::class, 'store']);
                    Route::put('/{leave_reason_id}', [OrganizationLeaveReasonController::class, 'update']);
                    Route::get('/{leave_reason_id}', [OrganizationLeaveReasonController::class, 'show']);
                    Route::delete('/{leave_reason_id}', [OrganizationLeaveReasonController::class, 'destroy']);

                });

                // organization setting type
    
                // organization setting 
                Route::prefix('/{org_id}/setting')->group(function () {
                    Route::get('/', [OrganizationSettingController::class, 'index']);
                    Route::post('/', [OrganizationSettingController::class, 'store']);
                    Route::put('/{setting_id}', [OrganizationSettingController::class, 'update']);
                    Route::get('/{setting_id}', [OrganizationSettingController::class, 'show']);
                    Route::delete('/{setting_id}', [OrganizationSettingController::class, 'destroy']);

                });

                // organization work shift type 
                Route::prefix('/{org_id}/workshift-type')->group(function () {
                    Route::get('/', [OrganizationWorkShiftTypeController::class, 'index']);
                    Route::post('/', [OrganizationWorkShiftTypeController::class, 'store']);
                    Route::put('/{shift_type_id}', [OrganizationWorkShiftTypeController::class, 'update']);
                    Route::get('/{shift_type_id}', [OrganizationWorkShiftTypeController::class, 'show']);
                    Route::delete('/{shift_type_id}', [OrganizationWorkShiftTypeController::class, 'destroy']);

                });

                // work shift 
                Route::prefix('/{org_id}/workshift')->group(function () {
                    Route::get('/', [OrganizationWorkShiftController::class, 'index']);
                    Route::post('/', [OrganizationWorkShiftController::class, 'store']);
                    Route::put('/{work_shift_id}', [OrganizationWorkShiftController::class, 'update']);
                    Route::get('/{work_shift_id}', [OrganizationWorkShiftController::class, 'show']);
                    Route::get('/shiftdata/{employee_id}', [OrganizationWorkShiftController::class, 'getEmployeeWorkShift']);
                    Route::get('/shiftdata/intern/{intern_id}', [OrganizationWorkShiftController::class, 'getInternWorkShift']);
                    Route::delete('/{work_shift_id}', [OrganizationWorkShiftController::class, 'destroy']);

                });

                // work model 
                Route::prefix('/{org_id}/work-model')->group(function () {
                    Route::get('/', [OrganizationWorkModelController::class, 'index']);
                    Route::post('/', [OrganizationWorkModelController::class, 'store']);
                    Route::put('/{work_model_id}', [OrganizationWorkModelController::class, 'update']);
                    Route::get('/{work_model_id}', [OrganizationWorkModelController::class, 'show']);
                    Route::delete('/{work_model_id}', [OrganizationWorkModelController::class, 'destroy']);

                });

                // work model 
                Route::prefix('/{org_id}/user-type')->group(function () {
                    Route::get('/', [OrganizationUserTypeController::class, 'index']);
                    Route::post('/', [OrganizationUserTypeController::class, 'store']);
                    Route::put('/{work_model_id}', [OrganizationUserTypeController::class, 'update']);
                    Route::get('/{work_model_id}', [OrganizationUserTypeController::class, 'show']);
                    Route::delete('/{work_model_id}', [OrganizationUserTypeController::class, 'destroy']);

                });

                // employee
                Route::prefix('/{org_id}/employee')->group(function () {
                    Route::get('/', [EmployeeController::class, 'index']);
                    Route::get('/all', [EmployeeController::class, 'indexV1']);
                    Route::post('/', [EmployeeController::class, 'store']);
                    Route::put('/{employee_id}', [EmployeeController::class, 'update']);
                    Route::get('/{employee_id}', [EmployeeController::class, 'show']);
                    Route::delete('/{employee_id}', [EmployeeController::class, 'destroy']);

                });

                Route::prefix('/{org_id}/interns')->group(function () {
                    Route::get('/', [InternController::class, 'index']);
                    Route::get('/all', [InternController::class, 'indexV1']);
                    Route::post('/', [InternController::class, 'store']);
                    Route::put('/{intern_id}', [InternController::class, 'update']);
                    Route::get('/{intern_id}', [InternController::class, 'show']);
                    Route::delete('/{intern_id}', [InternController::class, 'destroy']);

                });





                // employee contact 
                Route::prefix('/{org_id}/employee-contact')->group(function () {
                    Route::get('/', [EmployeeContactController::class, 'index']);
                    Route::post('/', [EmployeeContactController::class, 'store']);
                    Route::put('/{contact_id}', [EmployeeContactController::class, 'update']);
                    Route::get('/{contact_id}', [EmployeeContactController::class, 'show']);
                    Route::delete('/{contact_id}', [EmployeeContactController::class, 'destroy']);

                });



                // employee-leaves
                Route::prefix('/{org_id}/employee-leave')->group(function () {
                    Route::get('/', [EmployeeLeaveController::class, 'index']);
                    Route::post('/', [EmployeeLeaveController::class, 'store']);
                    Route::put('/{leave_id}', [EmployeeLeaveController::class, 'update']);
                    Route::get('/{leave_id}', [EmployeeLeaveController::class, 'show']);
                    Route::delete('/{leave_id}', [EmployeeLeaveController::class, 'destroy']);

                });

                // education apis 
    
                Route::prefix('/{org_id}/language')->group(function () {
                    Route::get('/', [OrganizationLanguageController::class, 'index']);
                    Route::post('/', [OrganizationLanguageController::class, 'store']);
                    Route::put('/{language_id}', [OrganizationLanguageController::class, 'update']);
                    Route::get('/{language_id}', [OrganizationLanguageController::class, 'show']);
                    Route::delete('/{language_id}', [OrganizationLanguageController::class, 'destroy']);

                });

                // education level 
    
                Route::prefix('/{org_id}/education-level')->group(function () {
                    Route::get('/', [OrganizationEducationLevelController::class, 'index']);
                    Route::post('/', [OrganizationEducationLevelController::class, 'store']);
                    Route::put('/{level_id}', [OrganizationEducationLevelController::class, 'update']);
                    Route::get('/{level_id}', [OrganizationEducationLevelController::class, 'show']);
                    Route::delete('/{level_id}', [OrganizationEducationLevelController::class, 'destroy']);

                    // education stream 
                    Route::prefix('/{level_id}/education-degree')->group(function () {
                        Route::get('/', [OrganizationEducationDegreeController::class, 'index']);
                        Route::post('/', [OrganizationEducationDegreeController::class, 'store']);
                        Route::put('/{degree_id}', [OrganizationEducationDegreeController::class, 'update']);
                        Route::get('/{degree_id}', [OrganizationEducationDegreeController::class, 'show']);
                        Route::delete('/{degree_id}', [OrganizationEducationDegreeController::class, 'destroy']);

                    });

                });

                // education stream 
                Route::prefix('/{org_id}/education-stream')->group(function () {
                    Route::get('/', [OrganizationEducationStreamController::class, 'index']);
                    Route::post('/', [OrganizationEducationStreamController::class, 'store']);
                    Route::put('/{stream_id}', [OrganizationEducationStreamController::class, 'update']);
                    Route::get('/{stream_id}', [OrganizationEducationStreamController::class, 'show']);
                    Route::delete('/{stream_id}', [OrganizationEducationStreamController::class, 'destroy']);

                });



                // education stream 
                Route::prefix('/{org_id}/employee-store')->group(function () {
                    Route::post('/store1', [EmployeeFormController::class, 'store1']);
                    Route::post('/store2', [EmployeeFormController::class, 'store2']);
                    Route::get('/update/{employee_id}', [EmployeeFormController::class, 'update']);
                });

                // intern form controllr  
                Route::prefix('/{org_id}/intern-store')->group(function () {
                    Route::post('/store1', [InterFormController::class, 'store1']);
                    Route::post('/store2', [InterFormController::class, 'store2']);
                    Route::get('/update/{intern_id}', [InterFormController::class, 'update']);
                });

                // eMPLOYEE eDUCATION 
                Route::prefix('/{org_id}/employee-education')->group(function () {
                    Route::get('/', [EmployeeEducationController::class, 'index']);
                    Route::post('/', [EmployeeEducationController::class, 'store']);
                    Route::put('/{education_id}', [EmployeeEducationController::class, 'update']);
                    Route::get('/{education_id}', [EmployeeEducationController::class, 'show']);
                    Route::delete('/{education_id}', [EmployeeEducationController::class, 'destroy']);

                });

                // employee languages known  
                Route::prefix('/{org_id}/employee-language')->group(function () {
                    Route::get('/', [EmployeeLanguageController::class, 'index']);
                    Route::post('/', action: [EmployeeLanguageController::class, 'store']);
                    Route::put('/{language_id}', [EmployeeLanguageController::class, 'update']);
                    Route::get('/{language_id}', [EmployeeLanguageController::class, 'show']);
                    Route::delete('/{language_id}', [EmployeeLanguageController::class, 'destroy']);

                });

                // employee family details 
                Route::prefix('/{org_id}/employee-family-details')->group(function () {
                    Route::get('/', [EmployeeFamilyMemberController::class, 'index']);
                    Route::post('/', [EmployeeFamilyMemberController::class, 'store']);
                    Route::put('/{degree_id}', [EmployeeFamilyMemberController::class, 'update']);
                    Route::get('/{degree_id}', [EmployeeFamilyMemberController::class, 'show']);
                    Route::delete('/{degree_id}', [EmployeeFamilyMemberController::class, 'destroy']);

                });

                // address 
    
                Route::prefix('/{org_id}/employee-address-details')->group(function () {
                    Route::get('/', [EmployeeAddressController::class, 'index']);
                    Route::post('/', [EmployeeAddressController::class, 'store']);
                    Route::put('/{degree_id}', [EmployeeAddressController::class, 'update']);
                    Route::get('/{degree_id}', [EmployeeAddressController::class, 'show']);
                    Route::delete('/{degree_id}', [EmployeeAddressController::class, 'destroy']);

                });

                // work experience 
                Route::prefix('/{org_id}/employee-work-experience')->group(function () {
                    Route::get('/', [EmployeeExperienceController::class, 'index']);
                    Route::post('/', [EmployeeExperienceController::class, 'store']);
                    Route::put('/{experience_id}', [EmployeeExperienceController::class, 'update']);
                    Route::get('/{experience_id}', [EmployeeExperienceController::class, 'show']);
                    Route::delete('/{experience_id}', [EmployeeExperienceController::class, 'destroy']);

                });


                Route::prefix('/{org_id}/employee-entitlements')->group(function () {

                    Route::get('/', [OrganizationLeaveEntitlmentController::class, 'index']);
                    Route::post('/', [OrganizationLeaveEntitlmentController::class, 'store']);
                    Route::put('/{entitle_id}', [OrganizationLeaveEntitlmentController::class, 'update']);
                    Route::get('/{entitle_id}', [OrganizationLeaveEntitlmentController::class, 'show']);
                    Route::delete('/{entitle_id}', [OrganizationLeaveEntitlmentController::class, 'destroy']);

                });

                // medical details 
                Route::prefix('/{org_id}/employee-medical')->group(function () {
                    Route::get('/', [EmployeeMedicalController::class, 'index']);
                    Route::post('/', [EmployeeMedicalController::class, 'store']);
                    Route::put('/{medical_id}', [EmployeeMedicalController::class, 'update']);
                    Route::get('/{medical_id}', [EmployeeMedicalController::class, 'show']);
                    Route::delete('/{medical_id}', [EmployeeMedicalController::class, 'destroy']);

                });


                // medical details 
                Route::prefix('/{org_id}/employee-bank-details')->group(function () {
                    Route::get('/', [EmployeeBankAccountController::class, 'index']);
                    Route::post('/', [EmployeeBankAccountController::class, 'store']);
                    Route::put('/{bank_id}', [EmployeeBankAccountController::class, 'update']);
                    Route::get('/{bank_id}', [EmployeeBankAccountController::class, 'show']);
                    Route::delete('/{bank_id}', [EmployeeBankAccountController::class, 'destroy']);

                });

                // employee exit 
                Route::prefix('/{org_id}/employee-exit')->group(function () {
                    Route::get('/', [EmployeeExitController::class, 'index']);
                    Route::post('/', [EmployeeExitController::class, 'store']);
                    Route::put('/{exit_id}', [EmployeeExitController::class, 'update']);
                    Route::get('/{exit_id}', [EmployeeExitController::class, 'show']);
                    Route::delete('/{exit_id}', [EmployeeExitController::class, 'destroy']);

                });


                // attandance    Apis 
                Route::prefix('/{org_id}/attendance-status-type')->group(function () {
                    Route::get('/', [AttendenceStatusTypeController::class, 'index']);
                    Route::post('/', [AttendenceStatusTypeController::class, 'store']);
                    Route::put('/{type_id}', [AttendenceStatusTypeController::class, 'update']);
                    Route::get('/{type_id}', [AttendenceStatusTypeController::class, 'show']);
                    Route::delete('/{type_id}', [AttendenceStatusTypeController::class, 'destroy']);

                });

                // attendence source
    
                Route::prefix('/{org_id}/attendance-source')->group(function () {
                    Route::get('/', [AttendenceSourceController::class, 'index']);
                    Route::post('/', [AttendenceSourceController::class, 'store']);
                    Route::put('/{source_id}', [AttendenceSourceController::class, 'update']);
                    Route::get('/{source_id}', [AttendenceSourceController::class, 'show']);
                    Route::delete('/{source_id}', [AttendenceSourceController::class, 'destroy']);

                });


                Route::prefix('/{org_id}/attendance-deviation-reason-type')->group(function () {
                    Route::get('/', [AttendenceDeviationReasonTypeController::class, 'index']);
                    Route::post('/', [AttendenceDeviationReasonTypeController::class, 'store']);
                    Route::put('/{type_id}', [AttendenceDeviationReasonTypeController::class, 'update']);
                    Route::get('/{type_id}', [AttendenceDeviationReasonTypeController::class, 'show']);
                    Route::delete('/{type_id}', [AttendenceDeviationReasonTypeController::class, 'destroy']);

                });


                Route::prefix('/{org_id}/attendance-deviation-reason')->group(function () {
                    Route::get('/', [AttendenceDeviationReasonController::class, 'index']);
                    Route::post('/', [AttendenceDeviationReasonController::class, 'store']);
                    Route::put('/{reason_id}', [AttendenceDeviationReasonController::class, 'update']);
                    Route::get('/{reason_id}', [AttendenceDeviationReasonController::class, 'show']);
                    Route::get('/reasontype/{reason_type_id}', [AttendenceDeviationReasonController::class, 'getDeviationReasonsByType']);
                    Route::delete('/{reason_id}', [AttendenceDeviationReasonController::class, 'destroy']);

                });

                Route::prefix('/{org_id}/attendance-break-type')->group(function () {
                    Route::get('/', [AttendenceBreakTypeController::class, 'index']);
                    Route::post('/', [AttendenceBreakTypeController::class, 'store']);
                    Route::put('/{type_id}', [AttendenceBreakTypeController::class, 'update']);
                    Route::get('/{type_id}', [AttendenceBreakTypeController::class, 'show']);
                    Route::delete('/{type_id}', [AttendenceBreakTypeController::class, 'destroy']);
                });

                Route::prefix('/{org_id}/attendance-employee-record')->group(function () {
                    Route::get('/', [AttendenceRecordController::class, 'index']);
                    Route::get('/report', [AttendenceRecordController::class, 'reportdata']);
                });

                Route::prefix('/{org_id}/attendance-time-logs')->group(function () {
                    Route::get('/', [AttendenceTimeLogController::class, 'index']);
                    Route::post('/', [AttendenceTimeLogController::class, 'store']);
                    Route::put('/{log_id}', [AttendenceTimeLogController::class, 'update']);
                    Route::get('/{log_id}', [AttendenceTimeLogController::class, 'show']);
                    Route::delete('/{log_id}', [AttendenceTimeLogController::class, 'destroy']);
                });


                Route::prefix('/{org_id}/employemnt-document-type')->group(function () {
                    Route::get('/', [EmployeeDocumentTypeController::class, 'index']);
                    Route::post('/', [EmployeeDocumentTypeController::class, 'store']);
                    Route::put('/{document_type_id}', [EmployeeDocumentTypeController::class, 'update']);
                    Route::get('/{document_type_id}', [EmployeeDocumentTypeController::class, 'show']);
                    Route::delete('/{document_type_id}', [EmployeeDocumentTypeController::class, 'destroy']);
                });

                Route::prefix('/{org_id}/employemnt-document')->group(function () {
                    Route::get('/', [EmployeeDocumentController::class, 'index']);
                    Route::post('/', [EmployeeDocumentController::class, 'store']);
                    Route::put('/{document_id}', [EmployeeDocumentController::class, 'update']);
                    Route::get('/{document_id}', [EmployeeDocumentController::class, 'show']);
                    Route::delete('/{document_id}', [EmployeeDocumentController::class, 'destroy']);
                });



                Route::prefix('/{org_id}/employemnt-document-links')->group(function () {
                    Route::get('/', [EmployeeDocumentLinkController::class, 'index']);
                    Route::post('/', [EmployeeDocumentLinkController::class, 'store']);
                    Route::put('/{link_id}', [EmployeeDocumentLinkController::class, 'update']);
                    Route::get('/{link_id}', [EmployeeDocumentLinkController::class, 'show']);
                    Route::delete('/{link_id}', [EmployeeDocumentLinkController::class, 'destroy']);
                });



                Route::prefix('/{org_id}/attendance-breaks')->group(function () {
                    Route::get('/', [OrganizationAttendanceBreakController::class, 'index']);
                    Route::post('/', [OrganizationAttendanceBreakController::class, 'store']);
                    Route::put('/{break_id}', [OrganizationAttendanceBreakController::class, 'update']);
                    Route::get('/{break_id}', [OrganizationAttendanceBreakController::class, 'show']);
                    Route::delete('/{break_id}', [OrganizationAttendanceBreakController::class, 'destroy']);
                });


                Route::prefix('/{org_id}/workshift-breaks')->group(function () {
                    Route::get('/', [OrganizationWorkShiftBreakController::class, 'index']);
                    Route::post('/', [OrganizationWorkShiftBreakController::class, 'store']);
                    Route::put('/{shift_id}', [OrganizationWorkShiftBreakController::class, 'update']);
                    Route::get('/{shift_id}', [OrganizationWorkShiftBreakController::class, 'show']);
                    Route::delete('/{shift_id}', [OrganizationWorkShiftBreakController::class, 'destroy']);
                });



                // employee shift assignments 
                Route::prefix('/{org_id}/workshift-assignment')->group(function () {
                    Route::get('/', [EmployeeWorkshiftAssignmentController::class, 'index']);
                    Route::post('/', [EmployeeWorkshiftAssignmentController::class, 'store']);
                    Route::put('/{assignment_id}', [EmployeeWorkshiftAssignmentController::class, 'update']);
                    Route::get('/{assignment_id}', [EmployeeWorkshiftAssignmentController::class, 'show']);
                    Route::delete('/{assignment_id}', [EmployeeWorkshiftAssignmentController::class, 'destroy']);
                });


                Route::prefix('/{org_id}/workshift-rotation-pattern')->group(function () {
                    Route::get('/', [OrganizationShiftRotationPatternController::class, 'index']);
                    Route::post('/', [OrganizationShiftRotationPatternController::class, 'store']);
                    Route::put('/{pattern_id}', [OrganizationShiftRotationPatternController::class, 'update']);
                    Route::get('/{pattern_id}', [OrganizationShiftRotationPatternController::class, 'show']);
                    Route::delete('/{pattern_id}', [OrganizationShiftRotationPatternController::class, 'destroy']);
                });



                Route::prefix('/{org_id}/workshift-rotation-days')->group(function () {
                    Route::get('/', [OrganizationWorkshiftRotationDaysController::class, 'index']);
                    Route::post('/', [OrganizationWorkshiftRotationDaysController::class, 'store']);
                    Route::put('/{rotaton_id}', [OrganizationWorkshiftRotationDaysController::class, 'update']);
                    Route::get('/{rotaton_id}', [OrganizationWorkshiftRotationDaysController::class, 'show']);
                    Route::delete('/{rotaton_id}', [OrganizationWorkshiftRotationDaysController::class, 'destroy']);
                });



                Route::prefix('/{org_id}/workshift-rotation-assignment')->group(function () {
                    Route::get('/', [EmployeeWorkshiftRotationAssignmentController::class, 'index']);
                    Route::post('/', [EmployeeWorkshiftRotationAssignmentController::class, 'store']);
                    Route::put('/{assignment_id}', [EmployeeWorkshiftRotationAssignmentController::class, 'update']);
                    Route::get('/{assignment_id}', [EmployeeWorkshiftRotationAssignmentController::class, 'show']);
                    Route::delete('/{assignment_id}', [EmployeeWorkshiftRotationAssignmentController::class, 'destroy']);
                });


                Route::prefix('/{org_id}/leave-policy')->group(function () {
                    Route::get('/', [OrganizationLeavePolicyController::class, 'index']);
                    Route::post('/', [OrganizationLeavePolicyController::class, 'store']);
                    Route::put('/{policy_id}', [OrganizationLeavePolicyController::class, 'update']);
                    Route::get('/{policy_id}', [OrganizationLeavePolicyController::class, 'show']);
                    Route::delete('/{policy_id}', [OrganizationLeavePolicyController::class, 'destroy']);
                });

                Route::prefix('/{org_id}/increment-type')->group(function () {
                    Route::get('/', [OrganizationEmployeeIncrementTypeController::class, 'index']);
                    Route::post('/', [OrganizationEmployeeIncrementTypeController::class, 'store']);
                    Route::put('/{type_id}', [OrganizationEmployeeIncrementTypeController::class, 'update']);
                    Route::get('/{type_id}', [OrganizationEmployeeIncrementTypeController::class, 'show']);
                    Route::delete('/{type_id}', [OrganizationEmployeeIncrementTypeController::class, 'destroy']);
                });
                Route::prefix('/{org_id}/increment')->group(function () {
                    Route::get('/', [EmployeeIncrementController::class, 'index']);
                    Route::post('/', [EmployeeIncrementController::class, 'store']);
                    Route::put('/{increment_id}', [EmployeeIncrementController::class, 'update']);
                    Route::get('/{increment_id}', [EmployeeIncrementController::class, 'show']);
                    Route::delete('/{increment_id}', [EmployeeIncrementController::class, 'destroy']);
                });
                Route::prefix('/{org_id}/employee-records')->group(function () {
                    Route::get('/', [EmployeeRecordController::class, 'index']);
                    Route::post('/', [EmployeeRecordController::class, 'store']);
                    Route::put('/{record_id}', [EmployeeRecordController::class, 'update']);
                    Route::get('/{record_id}', [EmployeeRecordController::class, 'show']);
                    Route::delete('/{record_id}', [EmployeeRecordController::class, 'destroy']);
                });
                Route::prefix('/{org_id}/payroll-component-type')->group(function () {
                    Route::get('/', [PayrollComponentTypesController::class, 'index']);
                    Route::post('/', [PayrollComponentTypesController::class, 'store']);
                    Route::put('/{record_id}', [PayrollComponentTypesController::class, 'update']);
                    Route::get('/{record_id}', [PayrollComponentTypesController::class, 'show']);
                    Route::delete('/{record_id}', [PayrollComponentTypesController::class, 'destroy']);
                });
                Route::prefix('/{org_id}/payroll-component')->group(function () {
                    Route::get('/', [PayrollComponentController::class, 'index']);
                    Route::post('/', [PayrollComponentController::class, 'store']);
                    Route::put('/{type_id}', [PayrollComponentController::class, 'update']);
                    Route::get('/{type_id}', [PayrollComponentController::class, 'show']);
                    Route::delete('/{type_id}', [PayrollComponentController::class, 'destroy']);
                });

                Route::prefix('/{org_id}/payroll-component-slab')->group(function () {
                    Route::get('/', [PayrollSlabsController::class, 'index']);
                    Route::post('/', [PayrollSlabsController::class, 'store']);
                    Route::put('/{type_id}', [PayrollSlabsController::class, 'update']);
                    Route::get('/{type_id}', [PayrollSlabsController::class, 'show']);
                    Route::delete('/{type_id}', [PayrollSlabsController::class, 'destroy']);
                });

                Route::prefix('/{org_id}/payroll-cycle')->group(function () {
                    Route::get('/', [PayrollCycleController::class, 'index']);
                    Route::post('/', [PayrollCycleController::class, 'store']);
                    Route::put('/{type_id}', [PayrollCycleController::class, 'update']);
                    Route::get('/{type_id}', [PayrollCycleController::class, 'show']);
                    Route::delete('/{type_id}', [PayrollCycleController::class, 'destroy']);
                });

                Route::prefix('/{org_id}/employee-salary-structure')->group(function () {
                    Route::get('/', [PayrollEmployeeSalaryStructureController::class, 'index']);
                    Route::post('/', [PayrollEmployeeSalaryStructureController::class, 'store']);
                    Route::put('/{structure_id}', [PayrollEmployeeSalaryStructureController::class, 'update']);
                    Route::get('/{structure_id}', [PayrollEmployeeSalaryStructureController::class, 'show']);
                    Route::delete('/{structure_id}', [PayrollEmployeeSalaryStructureController::class, 'destroy']);
                });

                Route::prefix('/{org_id}/payroll/salary-component')->group(function () {
                    Route::get('/', [PayrollSalaryStructureComponentController::class, 'index']);
                    Route::post('/', [PayrollSalaryStructureComponentController::class, 'store']);
                    Route::put('/{structure_id}', [PayrollSalaryStructureComponentController::class, 'update']);
                    Route::get('/{structure_id}', [PayrollSalaryStructureComponentController::class, 'show']);
                    Route::delete('/{structure_id}', [PayrollSalaryStructureComponentController::class, 'destroy']);
                });


                Route::post('/datagrid/get-by-context', [GeneralDataGridController::class, 'getDataGridByContext']);
                Route::delete('/datagrid/get-by-context', [GeneralDataGridController::class, 'getDataGridByContextDelete']);

                // General DataGrid Controller
                Route::prefix('general-datagrids')->group(function () {
                    Route::get('/', [GeneralDataGridController::class, 'index']);
                    Route::post('/', [GeneralDataGridController::class, 'store']);
                    Route::get('/{datagrid_id}', [GeneralDataGridController::class, 'show']);
                    Route::put('/{datagrid_id}', [GeneralDataGridController::class, 'update']);
                    Route::delete('/{datagrid_id}', [GeneralDataGridController::class, 'destroy']);
                });

                // Organization DataGrid Controller
                Route::get(
                    '/organization-datagrids/organization/{organization_id}',
                    [OrganizationDataGridController::class, 'getByOrganizationAndKey']
                );
                Route::apiResource('/organization-datagrids', OrganizationDataGridController::class);

                // organization user datagrid controller
                Route::apiResource('/organization-user-datagrids', OrganizationUserDataGridContoller::class);



                Route::prefix('/{org_id}/internship-type')->group(function () {
                    Route::get('/', [InternshipTypeController::class, 'index']);
                    Route::post('/', [InternshipTypeController::class, 'store']);
                    Route::put('/{intern_id}', [InternshipTypeController::class, 'update']);
                    Route::get('/{intern_id}', [InternshipTypeController::class, 'show']);
                    Route::delete('/{intern_id}', [InternshipTypeController::class, 'destroy']);
                });

                Route::prefix('/{org_id}/intern-exit')->group(function () {
                    Route::get('/', [InternExitController::class, 'index']);
                    Route::post('/', [InternExitController::class, 'store']);
                    Route::put('/{exit_id}', [InternExitController::class, 'update']);
                    Route::get('/{exit_id}', [InternExitController::class, 'show']);
                    Route::delete('/{exit_id}', [InternExitController::class, 'destroy']);
                });



                Route::prefix('/{org_id}/internship-status')->group(function () {
                    Route::get('/', [IntershipStatusController::class, 'index']);
                    Route::post('/', [IntershipStatusController::class, 'store']);
                    Route::put('/{intern_id}', [IntershipStatusController::class, 'update']);
                    Route::get('/{intern_id}', [IntershipStatusController::class, 'show']);
                    Route::delete('/{intern_id}', [IntershipStatusController::class, 'destroy']);
                });


                Route::prefix('/{org_id}/internship-stages')->group(function () {
                    Route::get('/', [InternshipStagesController::class, 'index']);
                    Route::post('/', [InternshipStagesController::class, 'store']);
                    Route::put('/{intern_id}', [InternshipStagesController::class, 'update']);
                    Route::get('/{intern_id}', [InternshipStagesController::class, 'show']);
                    Route::delete('/{intern_id}', [InternshipStagesController::class, 'destroy']);
                });



                Route::prefix('/{org_id}/intern-leaves')->group(function () {
                    Route::get('/', [InternLeaveController::class, 'index']);
                    Route::post('/', [InternLeaveController::class, 'store']);
                    Route::put('/{leave_id}', [InternLeaveController::class, 'update']);
                    Route::get('/{leave_id}', [InternLeaveController::class, 'show']);
                    Route::delete('/{leave_id}', [InternLeaveController::class, 'destroy']);
                });


                Route::prefix('/{org_id}/intern-document-type')->group(function () {
                    Route::get('/', [InternDocumentTypeController::class, 'index']);
                    Route::post('/', [InternDocumentTypeController::class, 'store']);
                    Route::put('/{intern_id}', [InternDocumentTypeController::class, 'update']);
                    Route::get('/{intern_id}', [InternDocumentTypeController::class, 'show']);
                    Route::delete('/{intern_id}', [InternDocumentTypeController::class, 'destroy']);
                });

                Route::prefix('/{org_id}/intern-stipend')->group(function () {
                    Route::get('/', [InternStipendController::class, 'index']);
                    Route::post('/', [InternStipendController::class, 'store']);
                    Route::put('/{stipend_id}', [InternStipendController::class, 'update']);
                    Route::get('/{stipend_id}', [InternStipendController::class, 'show']);
                    Route::delete('/{stipend_id}', [InternStipendController::class, 'destroy']);
                });


                Route::prefix('/{org_id}/intern-time-logs')->group(function () {
                    Route::get('/', [InterAttendanceTimeLogController::class, 'index']);
                    Route::post('/', [InterAttendanceTimeLogController::class, 'store']);
                    Route::put('/{log_id}', [InterAttendanceTimeLogController::class, 'update']);
                    Route::get('/{log_id}', [InterAttendanceTimeLogController::class, 'show']);
                    Route::delete('/{log_id}', [InterAttendanceTimeLogController::class, 'destroy']);
                });


                Route::prefix('/{org_id}/intern-certificate')->group(function () {
                    Route::get('/', [InternCertificateController::class, 'index']);
                    Route::post('/', [InternCertificateController::class, 'store']);
                    Route::put('/{certificate_id}', [InternCertificateController::class, 'update']);
                    Route::get('/{certificate_id}', [InternCertificateController::class, 'show']);
                    Route::delete('/{certificate_id}', [InternCertificateController::class, 'destroy']);
                });

                Route::prefix('/{org_id}/func-role')->group(function () {
                    Route::get('/', [OrganizationFunctionRoleController::class, 'index']);
                    Route::post('/', [OrganizationFunctionRoleController::class, 'store']);
                    Route::put('/{role_id}', [OrganizationFunctionRoleController::class, 'update']);
                    Route::get('/{role_id}', [OrganizationFunctionRoleController::class, 'show']);
                    Route::delete('/{role_id}', [OrganizationFunctionRoleController::class, 'destroy']);
                });


                Route::prefix('/{org_id}/func-role-spec')->group(function () {
                    Route::get('/', [OrganizationFunctionRoleSpecilizationController::class, 'index']);
                    Route::post('/', [OrganizationFunctionRoleSpecilizationController::class, 'store']);
                    Route::put('/{role_id}', [OrganizationFunctionRoleSpecilizationController::class, 'update']);
                    Route::get('/{role_id}', [OrganizationFunctionRoleSpecilizationController::class, 'show']);
                    Route::delete('/{role_id}', [OrganizationFunctionRoleSpecilizationController::class, 'destroy']);
                });



                Route::prefix('/{org_id}/emp-func-role')->group(function () {
                    Route::get('/', [EmployeeFunctionRoleController::class, 'index']);
                    Route::post('/', [EmployeeFunctionRoleController::class, 'store']);
                    Route::put('/{role_id}', [EmployeeFunctionRoleController::class, 'update']);
                    Route::get('/{role_id}', [EmployeeFunctionRoleController::class, 'show']);
                    Route::delete('/{role_id}', [EmployeeFunctionRoleController::class, 'destroy']);
                });


                Route::prefix('/{org_id}/intern-attendance-record')->group(function () {
                    Route::get('/', [InternAttendanceRecordController::class, 'index']);

                    Route::get('/{log_id}', [InternAttendanceRecordController::class, 'show']);

                });


            });
        });

    }
);

// application Role
Route::prefix('/department-locations')->group(function () {
    Route::get('/all', [OrganizationDepartmentLocationController::class, 'getLocationsByDepartment']);
});


Route::prefix('/department-designation')->group(function () {
    Route::get('/all', [OrganizationDesignationController::class, 'getDesignationByDepartment']);
});

// Application Controller
Route::prefix('application')->group(function () {

    // Application Controller
    Route::prefix('/user')->group(function () {
        Route::get('/', [ApplicationUserController::class, 'index']);
        Route::post('/', [ApplicationUserController::class, 'store']);
        Route::put('/{user_id}', [ApplicationUserController::class, 'update']);
        Route::get('/{user_id}', [ApplicationUserController::class, 'show']);
        Route::delete('/{user_id}', [ApplicationUserController::class, 'destroy']);
        Route::post('/create-userV1', [ApplicationUserController::class, 'createUser']);

    });


    // application Role
    Route::prefix('/userrole')->group(function () {
        Route::get('/', [ApplicationUserRoleController::class, 'index']);
        Route::post('/', [ApplicationUserRoleController::class, 'store']);
        Route::put('/{user_role_id}', [ApplicationUserRoleController::class, 'update']);
        Route::get('/{user_role_id}', [ApplicationUserRoleController::class, 'show']);
        Route::delete('/{user_role_id}', [ApplicationUserRoleController::class, 'destroy']);

    });

    Route::prefix('/userrole-assignment')->group(function () {
        Route::get('/', [ApplicationUserRoleAssignmentController::class, 'index']);
        Route::post('/', [ApplicationUserRoleAssignmentController::class, 'store']);
        Route::put('/{user_role_assignment_id}', [ApplicationUserRoleAssignmentController::class, 'update']);
        Route::get('/{user_role_assignment_id}', [ApplicationUserRoleAssignmentController::class, 'show']);
        Route::delete('/{user_role_assignment_id}', [ApplicationUserRoleAssignmentController::class, 'destroy']);

    });

    Route::prefix('/user-permission')->group(function () {
        Route::get('/', [ApplicationUserPermissionController::class, 'index']);
        Route::post('/', [ApplicationUserPermissionController::class, 'store']);
        Route::put('/{permission_id}', [ApplicationUserPermissionController::class, 'update']);
        Route::get('/{permission_id}', [ApplicationUserPermissionController::class, 'show']);
        Route::delete('/{permission_id}', [ApplicationUserPermissionController::class, 'destroy']);

    });

    Route::prefix('/userrole-assignmentlogs')->group(function () {
        Route::get('/', [ApplicationUserRoleAssignmentLogsController::class, 'index']);
        Route::post('/', [ApplicationUserRoleAssignmentLogsController::class, 'store']);
        Route::put('/{role_assignment_id}', [ApplicationUserRoleAssignmentLogsController::class, 'update']);
        Route::get('/{role_assignment_id}', [ApplicationUserRoleAssignmentLogsController::class, 'show']);
        Route::delete('/{role_assignment_id}', [ApplicationUserRoleAssignmentLogsController::class, 'destroy']);

    });

    Route::prefix('/userrole-permission')->group(function () {
        Route::get('/', [ApplicationUserRolePermissionController::class, 'index']);
        Route::post('/', [ApplicationUserRolePermissionController::class, 'store']);
        Route::get('/{roleId}/permission', [ApplicationUserRolePermissionController::class, 'show']);
        Route::put('/{user_role_permission_id}', [ApplicationUserRolePermissionController::class, 'update']);
        Route::get('/{user_role_permission_id}', [ApplicationUserRolePermissionController::class, 'show']);
        Route::delete('/{user_role_permission_id}', [ApplicationUserRolePermissionController::class, 'destroy']);

    });

    // application Role
    Route::prefix('/module')->group(function () {
        Route::get('/', [ApplicationModuleController::class, 'index']);
        Route::post('/', [ApplicationModuleController::class, 'store']);
        Route::put('/{module_id}', [ApplicationModuleController::class, 'update']);
        Route::get('/{module_id}', [ApplicationModuleController::class, 'show']);
        Route::delete('/{module_id}', [ApplicationModuleController::class, 'destroy']);

    });


    // application Role
    Route::prefix('/module-action')->group(function () {
        Route::get('/', [ApplicationModuleActionController::class, 'index']);
        Route::post('/', [ApplicationModuleActionController::class, 'store']);
        Route::put('/{module_action_id}', [ApplicationModuleActionController::class, 'update']);
        Route::get('/{module_action_id}', [ApplicationModuleActionController::class, 'show']);
        Route::delete('/{module_action_id}', [ApplicationModuleActionController::class, 'destroy']);

    });

    // application Role
    Route::prefix('/error-logs')->group(function () {
        Route::get('/', [ApplicationErrorLogsController::class, 'index']);
        Route::post('/', [ApplicationErrorLogsController::class, 'store']);
        Route::put('/{error_log_id}', [ApplicationErrorLogsController::class, 'update']);
        Route::get('/{error_log_id}', [ApplicationErrorLogsController::class, 'show']);
        Route::delete('/{error_log_id}', [ApplicationErrorLogsController::class, 'destroy']);

    });


    // application Role
    Route::prefix('/login-logs')->group(function () {
        Route::get('/', [ApplicationUserloginController::class, 'index']);
        Route::post('/', [ApplicationUserloginController::class, 'store']);
        Route::put('/{login_log_id}', [ApplicationUserloginController::class, 'update']);
        Route::get('/{login_log_id}', [ApplicationUserloginController::class, 'show']);
        Route::delete('/{login_log_id}', [ApplicationUserloginController::class, 'destroy']);

    });

    // application Role
    Route::prefix('/permission-auditlogs')->group(function () {
        Route::get('/', [ApplicationUserPermissionAuditLogsController::class, 'index']);
        Route::post('/', [ApplicationUserPermissionAuditLogsController::class, 'store']);
        Route::put('/{permission_audit_log_id}', [ApplicationUserPermissionAuditLogsController::class, 'update']);
        Route::get('/{permission_audit_log_id}', [ApplicationUserPermissionAuditLogsController::class, 'show']);
        Route::delete('/{permission_audit_log_id}', [ApplicationUserPermissionAuditLogsController::class, 'destroy']);

    });


    // Role Permission 
    Route::prefix('/role-permissions')->group(function () {
        Route::get('/', [ApplicationUserRolePermissionAllController::class, 'getAllPermissionsForAllRoles']);
        Route::post('/', [ApplicationUserRolePermissionAllController::class, 'storeOrUpdatePermissions']);
        Route::get('/{roleId}', [ApplicationUserRolePermissionAllController::class, 'storeOrUpdatePermissions']);
        Route::get('/particular/{roleId}', [ApplicationUserRolePermissionAllController::class, 'getPermissionsByPermissionId']);
        Route::delete('/particular/{roleId}', [ApplicationUserRolePermissionAllController::class, 'deletePermissionsByRoleId']);


    });



});

// AuthController 
Route::prefix('/auth')->group(function () {
    Route::post('/login', [ApplicationAuthController::class, 'loginWithEmail']);
    Route::post('/forgot-password', [ApplicationAuthController::class, 'forgotPassword']);
    Route::post('/reset-password', [ApplicationAuthController::class, 'resetPassword']);
    Route::post('/verified-otp', [ApplicationAuthController::class, 'verifyotp']);
    Route::post('/create-user', [ApplicationAuthController::class, 'createuserV1']);

    Route::middleware('auth:applicationusers')->group(function () {
        Route::post('/change-password', [ApplicationAuthController::class, 'changePassword']);
        Route::get('/logout', [ApplicationAuthController::class, 'logout']);
        Route::get('/token-login', [ApplicationAuthController::class, 'loginWithToken']);

    });
});

// check for the permissions  
Route::get('/user/{userId}/has-access', [PermissionController::class, 'checkUserAccess']);


Route::prefix('project-management')->group(function () {

    Route::middleware('auth:applicationusers')->group(function () {

        Route::apiResource('clients', OrganizationClientController::class);
        Route::apiResource('client-contacts', OrganizationClientContactController::class);

        Route::apiResource('project-types', OrganizationProjectTypeController::class);
        Route::apiResource('project-categories', OrganizationProjectCategoryController::class);
        Route::apiResource('project-sub-categories', OrganizationProjectSubCategoriesController::class);  // 
        Route::apiResource('project-templates', OrganizationProjectTemplatesController::class);  // 
        Route::apiResource('project-template-milestone', OrganizationProjectMilestoneTemplatesController::class);
        Route::apiResource('project-template-tasks', OrganizationProjectTemplatesTasksController::class);       //////// iska fortnend banaana

        Route::apiResource('projects', OrganizationProjectController::class);
        Route::apiResource('project-teams', OrganizationProjectTeamController::class);
        Route::apiResource('project-team-members', OrganizationProjectTeamMemberController::class);
        Route::apiResource('project-milestone', OrganizationProjectMilestoneController::class);

        Route::get('project-tasks/employee-tasks', [OrganizationProjectTaskController::class, 'getEmployeeTasks']);
        Route::apiResource('project-tasks', OrganizationProjectTaskController::class);

        Route::apiResource('project-task-recurrence', OrganizationProjectTaskRecurrenceController::class);
        Route::apiResource('project-task-template', OrganizationProjectTaskTemplateController::class);
        Route::apiResource('project-task-category', OrganizationProjectTaskCategoryController::class);  //
        Route::apiResource('project-task-sub-category', OrganizationProjectTaskSubCategoryController::class);  //
        Route::apiResource('project-task-type', OrganizationProjectTaskTypeController::class);
        Route::apiResource('project-task-timelog', OrganizationTaskTimeLogController::class);

    });

});


// get filter employees all 
Route::prefix('/organizations/{org_id}/filter-employee')->group(function () {
    Route::get('/', [EmployeeFilterControler::class, 'getallFilterEmployee']);

});


// get filter employees all 
Route::prefix('/organizations/{org_id}/leave-reason-types/{type_id}/leave-reasons')->group(function () {
    Route::get('/', [OrganizationLeaveReasonController::class, 'getByType']);

});


Route::post('/face/recognize', [FaceRecognitionController::class, 'recognize']);

Route::get('organizations/{org_id}/leave-balances/taken', [EmployeeLeaveBalanceController::class, 'employeesWhoTookLeave']);

// employee leave summary of particlar organization id 
Route::prefix('/organizations/{org_id}/employee-monthlyleave-summary')->group(function () {
    Route::get('/', [EmployeeLeaveSummaryController::class, 'getMonthlySummary']);
});



Route::prefix('/organization/data-type')->group(function () {

    Route::prefix('/')->group(function () {
        Route::get('/', [OrganizationSettingDataTypeController::class, 'index']);
        Route::post('/', [OrganizationSettingDataTypeController::class, 'store']);

    });


});


Route::prefix('/setting-type')->group(function () {
    Route::get('/', [OrganizationSettingTypeController::class, 'index']);


});


Route::prefix('/organization/{org_id}/employee-all')->group(function () {
    Route::get('/', [EmployeeController::class, 'getAllEmployees']);


});

Route::prefix('organizations/{org_id}/employee-leaves-all')->group(function () {
    Route::get('/', [EmployeeLeaveController::class, 'AllLeaves']);

});

Route::prefix('organizations/{org_id}/department-all')->group(function () {
    Route::get('/', [OrganizationDepartmentController::class, 'getAll']);

});

Route::prefix('organizations/{org_id}/designation-all')->group(function () {
    Route::get('/', [OrganizationDesignationController::class, 'getAll']);

});





Route::prefix('general-datagrids')->group(function () {
    Route::get('/', [GeneralDataGridController::class, 'index']);
    Route::post('/', [GeneralDataGridController::class, 'store']);
    Route::get('/{datagrid_id}', [GeneralDataGridController::class, 'show']);
    Route::put('/{datagrid_id}', [GeneralDataGridController::class, 'update']);
    Route::delete('/{datagrid_id}', [GeneralDataGridController::class, 'destroy']);
});






Route::prefix('ems')->group(function () {
    Route::middleware('auth:applicationusers')->group(function () {

        Route::apiResource('training-programs-categories', TrainingProgramCategoriesController::class);
        Route::apiResource('training-programs', TrainingProgramController::class);

        Route::apiResource('students', StudentsController::class);
        Route::apiResource('students-fees', StudentsFeesController::class);

        Route::apiResource('leads', LeadsController::class);
        Route::apiResource('lead-stages', LeadStagesController::class);
        Route::apiResource('lead-sources', LeadSourcesController::class);
        Route::apiResource('lead-activities', LeadActivitiesController::class);
        Route::apiResource('lead-contact-timings', LeadContactTimingsController::class);

        Route::apiResource('assesments', AssesmentsController::class);
        Route::apiResource('assesment-results', AssesmentResultsController::class);

        Route::apiResource('batches', BatchesController::class);
        Route::apiResource('batch-classes', BatchClassesController::class);
        Route::apiResource('batch-students', BatchStudentsController::class);

        Route::apiResource('recruitment-agencies', RecruitmentAgenciesController::class);
        Route::apiResource('companies', CompaniesController::class);
        Route::apiResource('admissions', AdmissionController::class);
        Route::apiResource('fee-installments', FeeInstallmentsController::class);
        Route::apiResource('classes-attendance', ClassAttendanceController::class);
        Route::apiResource('certificates', CertificatesController::class);
        Route::apiResource('placement-referrals', PlacementReferralsController::class);
        Route::apiResource('demo-sessions', DemoSessionsController::class);

    });

});



Route::prefix('organizations/{org_id}/filter/intern')->group(function () {
    Route::get('/', [InternController::class, 'getallFilterInter']);

});




Route::middleware('auth:applicationusers')->group(function () {

    Route::post('/datagrid/get-by-context', [GeneralDataGridController::class, 'getDataGridByContext']);
    Route::delete('/datagrid/get-by-context', [GeneralDataGridController::class, 'getDataGridByContextDelete']);

    // General DataGrid Controller
    Route::prefix('general-datagrids')->group(function () {
        Route::get('/', [GeneralDataGridController::class, 'index']);
        Route::post('/', [GeneralDataGridController::class, 'store']);
        Route::get('/{datagrid_id}', [GeneralDataGridController::class, 'show']);
        Route::put('/{datagrid_id}', [GeneralDataGridController::class, 'update']);
        Route::delete('/{datagrid_id}', [GeneralDataGridController::class, 'destroy']);
    });

    // Organization DataGrid Controller
    Route::get(
        '/organization-datagrids/organization/{organization_id}',
        [OrganizationDataGridController::class, 'getByOrganizationAndKey']
    );
    Route::apiResource('/organization-datagrids', OrganizationDataGridController::class);

    // organization user datagrid controller
    Route::apiResource('/organization-user-datagrids', OrganizationUserDataGridContoller::class);




});



















