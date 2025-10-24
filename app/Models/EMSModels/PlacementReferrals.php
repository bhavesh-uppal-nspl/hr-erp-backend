<?php

namespace App\Models\EMSModels;
use Illuminate\Database\Eloquent\Model;

class PlacementReferrals extends Model
{
    protected $table = 'organization_ems_placement_referrals';
    protected $primaryKey = 'organization_ems_placement_referral_id';

    protected $fillable = [
        'organization_id',
        'organization_entity_id',
        'organization_ems_student_id',
        'organization_ems_admission_id',
        'training_program_id',
        'organization_ems_recruitment_agency_id',
        'organization_ems_company_id',
        'referral_date',
        'referral_status',
        'job_role',
        'package_amount',
        'currency_code',
        'joining_date',
        'remarks'
    ];

    public $timestamps = true;

    public function student()
    {
        return $this->hasOne(Student::class , 'organization_ems_student_id');
    }
    public function admission()
    {
        return $this->hasOne(Admission::class , 'organization_ems_admission_id');
    }
    public function trainingProgram()
    {
        return $this->hasOne(TrainingProgram::class ,'organization_ems_training_program_id', 'training_program_id');
    }
    public function agency()
    {
        return $this->hasOne(RecruitmentAgency::class , 'organization_ems_recruitment_agency_id');
    }
    public function company()
    {
        return $this->hasOne(Company::class , 'organization_ems_company_id');
    }


}
