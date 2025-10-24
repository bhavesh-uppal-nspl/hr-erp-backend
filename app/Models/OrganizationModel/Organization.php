<?php


namespace App\Models\OrganizationModel;

use App\Http\Controllers\OrganizationController\OrganizationBusinessRegistrationTypeController;
use App\Http\Controllers\OrganizationController\OrganizationLeaveReasonTypeController;
use App\Models\ClientModels\Client;
use App\Models\EmployeesModel\EmployeeAddress;
use App\Models\EmployeesModel\EmployeeContact;
use App\Models\EmployeesModel\EmployeeExit;
use App\Models\EmployeesModel\EmployeeLeaves;
use App\Models\EmployeesModel\Employees;
use App\Models\GeneralModel\GeneralBusinessOwnershipType;
use App\Models\GeneralModel\GeneralIndustry;
use App\Models\OrganizationModel\OrganizationHolidayCalendar;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;


class Organization extends Model
{
    use HasFactory;

    // If your table name doesn't follow Laravel's default plural convention
    protected $table = 'organizations';

    // Specify the primary key if it's not 'id'
    protected $primaryKey = 'organization_id';

    // If the primary key is not auto-incrementing or not an integer, mention this
    public $incrementing = true;
    protected $keyType = 'int';

    // Fields that can be mass-assigned
    protected $fillable = [
        'organization_name',
        'client_id',
        'organization_short_name',
        'license_id',
        'organization_group_id'

    ];


    public function industry()
    {
        return $this->hasOne(GeneralIndustry::class, 'general_industry_id', 'general_industry_id');
    }

    public function businessprofile()
    {
        return $this->hasOne( OrganizationBusinessProfile::class, 'organization_id', 'organization_id');
    }



    public function employeecontact()
    {
        return $this->hasMany(EmployeeContact::class, 'organization_id', 'organization_id');
    }

    public function departments()
    {
        return $this->hasMany(OrganizationDepartment::class, 'organization_id', 'organization_id');
    }
    public function leaves()
    {
        return $this->hasMany(EmployeeLeaves::class, 'organization_id', 'organization_id');
    }


    public function unitTypes()
    {
        return $this->hasMany(OrganizationUnitTypes::class, 'organization_id', 'organization_id');
    }

    public function units()
    {
        return $this->hasOne(OrganizationUnits::class, 'organization_id', 'organization_id');
    }

    public function locationOwnershipType()
    {
        return $this->hasMany(OrganizationLocationOwnershipType::class, 'organization_id', 'organization_id');
    }

    public function employeexit()
    {
        return $this->hasMany(EmployeeExit::class, 'organization_id', 'organization_id');
    }

    public function departmentlocations()
    {
        return $this->hasMany(OrganizationDepartmentLocation::class, 'organization_id', 'organization_id');
    }

    public function locations()
    {
        return $this->hasOne(OrganizationLocation::class, 'organization_id', 'organization_id');
    }


       public function OrganizationUser()
    {
        return $this->hasMany(OrganizationUser::class, 'organization_id', 'organization_id');
    }

    public function businesUnit()
    {
        return $this->hasMany(OrganizationBusinessUnit::class, 'organization_id', 'organization_id');
    }

    public function employemnttype()
    {
        return $this->hasMany(OrganizationEmployementType::class, 'organization_id', 'organization_id');
    }


    public function divisions()
    {
        return $this->hasMany(OrganizationBusinessDivision::class, 'organization_id', 'organization_id');
    }

    public function profile()
    {
        return $this->hasOne(OrganizationIdentityProfile::class, 'organization_id', 'organization_id');
    }


    public function ownershipType()
    {
        return $this->hasOne(GeneralBusinessOwnershipType::class, 'general_business_ownership_type_id', 'general_business_ownership_type_id');
    }

    public function businessOwnershipType()
    {
        return $this->hasMany(OrganizationBusinessOwnnershipType::class, 'general_business_ownership_type_id', 'general_business_ownership_type_id');
    }





    public function businessregistration()
    {
        return $this->hasMany(OrganizationBusinessRegistration::class, 'organization_id', 'organization_id');
    }


    public function businessregistrationtype()
    {
        return $this->hasMany(OrganizationBusinessRegsitrationType::class, 'organization_id', 'organization_id');
    }


    public function Entities()
    {
        return $this->hasOne(OrganizationEntities::class, 'organization_id', 'organization_id');
    }


    public function user()
    {
        return $this->hasMany(OrganizationUser::class, 'organization_id', 'organization_id');
    }

    public function userType()
    {
        return $this->hasMany(OrganizationUserType::class, 'organization_id', 'organization_id');
    }


    public function workModels()
    {
        return $this->hasMany(OrganizationWorkModel::class, 'organization_id', 'organization_id');
    }


    public function designations()
    {
        return $this->hasMany(OrganizationDesignation::class, 'organization_id', 'organization_id');
    }



    public function employmentstatus()
    {
        return $this->hasMany(OrganizationEmployementStatus::class, 'organization_id', 'organization_id');
    }


    public function employees()
    {
        return $this->hasMany(Employees::class, 'organization_id', 'organization_id');
    }



    public function exitreason()
    {
        return $this->hasMany(OrganizationEmployementExistReason::class, 'organization_id', 'organization_id');
    }

    public function exitreasontypes()
    {
        return $this->hasMany(OrganizationEmpExitReasonType::class, 'organization_id', 'organization_id');
    }


    public function addressType()
    {
        return $this->hasMany(OrganizationEmpAddType::class, 'organization_id', 'organization_id');
    }

    public function residentialtype()
    {
        return $this->hasMany(OrganizationResidentailOwnershipType::class, 'organization_id', 'organization_id');
    }


    public function holidaycalender()
    {
        return $this->hasMany(OrganizationHolidayCalendar::class, 'organization_id', 'organization_id');
    }


    public function holidaytypes()
    {
        return $this->hasMany(OrganizationHolidayTypes::class, 'organization_id', 'organization_id');
    }

    public function holidays()
    {
        return $this->hasMany(OrganizationHoliday::class, 'organization_id', 'organization_id');
    }

    public function leavecategory()
    {
        return $this->hasMany(OrganizationLeaveCategory::class, 'organization_id', 'organization_id');
    }


    public function leavetype()
    {
        return $this->hasMany(OrganizationLeaveType::class, 'organization_id', 'organization_id');
    }

    public function leavereasontypes()
    {
        return $this->hasMany(OrganizationLeaveReasonType::class, 'organization_id', 'organization_id');
    }
    public function leavereason()
    {
        return $this->hasMany(OrganizationLeaveReason::class, 'organization_id', 'organization_id');
    }
    public function employemttype()
    {
        return $this->hasMany(OrganizationEmployementType::class, 'organization_id', 'organization_id');
    }

    public function setttingtype()
    {
        return $this->hasMany(OrganizationSettingType::class, 'organization_id', 'organization_id');
    }

    public function setttings()
    {
        return $this->hasMany(OrganizationSetting::class, 'organization_id', 'organization_id');
    }

    public function workshifttype()
    {
        return $this->hasMany(OrganizationWorkShiftType::class, 'organization_id', 'organization_id');
    }

    public function workshift()
    {
        return $this->hasMany(OrganizationWorkShift::class, 'organization_id', 'organization_id');
    }

    public function employeeaddress()
    {
        return $this->hasMany(EmployeeAddress::class, 'organization_id', 'organization_id');
    }

    //   public function BusinessOwnershipType()
    // {
    //     return $this->hasMany(OrganizationBusinessOwnnershipType::class, 'organization_id', 'organization_id');
    // }

    public function Client()
    {
        return $this->hasOne(Client::class, 'client_id', 'client_id');
    }

}
