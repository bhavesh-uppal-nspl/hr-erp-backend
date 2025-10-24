<?php

namespace App\Models\EmployeesModel;

use App\Models\AttendenceModels\EmployeeAttendenceTimeLog;
use App\Models\OrganizationModel\Organization;
use App\Models\OrganizationModel\OrganizationBusinessDivision;
use App\Models\OrganizationModel\OrganizationBusinessUnit;
use App\Models\OrganizationModel\OrganizationDepartment;
use App\Models\OrganizationModel\OrganizationDepartmentLocation;
use App\Models\OrganizationModel\OrganizationDesignation;
use App\Models\OrganizationModel\OrganizationEmployementStatus;
use App\Models\OrganizationModel\OrganizationEmployementType;
use App\Models\OrganizationModel\OrganizationUnits;
use App\Models\OrganizationModel\OrganizationWorkModel;
use App\Models\OrganizationModel\OrganizationWorkShift;
use App\Models\EmployeesModel\EmployeeAddress;
use App\Models\EmployeesModel\EmployeeExperience;
use App\Models\EmployeesModel\EmployeeContact;
use App\Models\EmployeesModel\EmployeeFamilyMember;
use App\Models\EmployeesModel\EmployeeEducation;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Employees extends Model
{
    use HasFactory;

    protected $table = 'employees';

    protected $primaryKey = 'employee_id';
    public $incrementing = true;
    protected $keyType = 'int';

    protected $fillable = [
        'organization_id',
        'employee_code',
        'first_name',
        'middle_name',
        'last_name',
        'date_of_birth',
        'gender',
        'marital_status',
        'profile_image_url',
        'organization_entity_id',
        'organization_unit_id',
        'organization_department_location_id',
        'organization_designation_id',
        'organization_employment_type_id',
        'organization_work_model_id',
        'organization_work_shift_id',
        'organization_employment_status_id',
        'organization_employment_stage_id',
        'date_of_joining',
        'reporting_manager_id',
        'organization_user_id '
    ];

    protected $casts = [
        'date_of_birth' => 'date',
        'date_of_joining' => 'date',
        'disability_flag' => 'boolean',
    ];


//     public function getDateOfBirthAttribute($value)
// {
//     return $value ? \Carbon\Carbon::parse($value)->format('d-m-Y') : null;
// }

// public function getDateOfJoiningAttribute($value)
// {
//     return $value ? \Carbon\Carbon::parse($value)->format('d-m-Y') : null;
// }




    // Example relationships
    public function organization()
    {
        return $this->belongsTo(Organization::class, 'organization_id', 'organization_id');
    }

    public function leaveBalances()
    {
        return $this->hasMany(EmployeeLeaveBalances::class, 'employee_id', 'employee_id');
    }


    public function Attendance()
    {
        return $this->hasMany(EmployeeAttendenceTimeLog::class, 'employee_id', 'employee_id');
    }


    public function TodayLatestAttendance()
    {
        return $this->hasOne(EmployeeAttendenceTimeLog::class, 'employee_id', 'employee_id')
            ->whereDate('created_at', Carbon::today())
            ->latest('created_at');
    }



    public function businessunit()
    {
        return $this->belongsTo(OrganizationBusinessUnit::class, 'organization_business_unit_id', 'organization_business_unit_id');
    }

    public function getProfileImageUrlAttribute($value)
    {
        return $value ? asset('storage/' . $value) : null;
    }

    //   for online       

    //    public function getProfileImageUrlAttribute($value)
    // {
    //     return $value ? asset('storage/app/public/employees/' . $value) : null;
    // }



    public function departmentLocation()
    {
        return $this->hasMany(OrganizationDepartmentLocation::class, 'organization_department_location_id', 'organization_department_location_id');
    }


    public function address()
    {
        return $this->belongsTo(EmployeeAddress::class, 'employee_id', 'employee_id');
    }


    public function businessdivision()
    {
        return $this->belongsTo(OrganizationBusinessDivision::class, 'organization_business_division_id', 'organization_business_division_id');
    }

     public function Education()
    {
        return $this->hasMany(EmployeeEducation::class, 'employee_id', 'employee_id');
    }



    public function contact()
    {
        return $this->belongsTo(EmployeeContact::class, 'employee_id', 'employee_id');
    }



    public function designation()
    {
        return $this->belongsTo(OrganizationDesignation::class, 'organization_designation_id', 'organization_designation_id');
    }

    public function employemnettype()
    {
        return $this->belongsTo(OrganizationEmployementType::class, 'organization_employment_type_id', 'organization_employment_type_id');
    }

    public function workmodel()
    {
        return $this->belongsTo(OrganizationWorkModel::class, 'organization_work_model_id', 'organization_work_model_id');
    }

    public function organizationunit()
    {
        return $this->belongsTo(OrganizationUnits::class, 'organization_unit_id', 'organization_unit_id');
    }



    public function workshift()
    {
        return $this->belongsTo(OrganizationWorkShift::class, 'organization_work_shift_id', 'organization_work_shift_id');
    }

    public function employmentsatus()
    {
        return $this->belongsTo(OrganizationEmployementStatus::class, 'organization_employment_status_id', 'organization_employment_status_id');
    }

    


    public function manager()
    {
        return $this->belongsTo(Employees::class, 'reporting_manager_id', 'employee_id');
    }


        public function experience()
    {
        return $this->belongsTo(EmployeeExperience::class, 'employee_id', 'employee_id');
    }


        public function family()
    {
        return $this->hasMany(EmployeeFamilyMember::class, 'employee_id', 'employee_id');
    }




    protected static function booted()
{
    // This handles both create and update
    static::saved(function ($employee) {
        $originalLocationId = $employee->getOriginal('organization_department_location_id');
        $currentLocationId  = $employee->organization_department_location_id;

        // If department changed (or first time created), decrement old department
        if ($originalLocationId && $originalLocationId != $currentLocationId) {
            $oldLocation = OrganizationDepartmentLocation::find($originalLocationId);
            if ($oldLocation) {
                OrganizationDepartment::where('organization_department_id', $oldLocation->organization_department_id)
                    ->decrement('department_employees_count');
            }
        }

        // Increment count for new department
        if ($currentLocationId && $originalLocationId != $currentLocationId) {
            $newLocation = OrganizationDepartmentLocation::find($currentLocationId);
            if ($newLocation) {
                OrganizationDepartment::where('organization_department_id', $newLocation->organization_department_id)
                    ->increment('department_employees_count');
            }
        }
    });

    // Handle delete
    static::deleted(function ($employee) {
        $locationId = $employee->getOriginal('organization_department_location_id') 
                      ?? $employee->organization_department_location_id;

        if ($locationId) {
            $loc = OrganizationDepartmentLocation::find($locationId);
            if ($loc) {
                OrganizationDepartment::where('organization_department_id', $loc->organization_department_id)
                    ->decrement('department_employees_count');
            }
        }
    });
}


   




}
