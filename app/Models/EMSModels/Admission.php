<?php

namespace App\Models\EMSModels;
use Illuminate\Database\Eloquent\Model;

class Admission extends Model
{
    protected $table = 'organization_ems_admissions';
    protected $primaryKey = 'organization_ems_admission_id';

    protected $fillable = [
        'organization_id',
        'organization_entity_id',
        'organization_ems_student_id',
        'organization_ems_lead_id',
        'training_program_id',
        'organization_ems_demo_session_id',
        'admission_number',
        'admission_date',
        'admission_status',
        'total_fee_amount',
        'discount_amount',
        'discount_reason',
        'net_fee_amount',
        'installment_count',
        'currency_code',
        'preferred_study_slot',
        'preferred_study_times',
        'remarks'
    ];

    public $timestamps = true;

    public function student()
    {
        return $this->hasOne(Student::class , 'organization_ems_student_id');
    }

    public function lead()
    {
        return $this->hasOne(Lead::class , 'organization_ems_lead_id');
    }

    public function trainingProgram()
    {
        return $this->belongsTo(TrainingProgram::class ,'organization_ems_training_program_id', 'training_program_id');
    }
    public function demoSession()
    {
        return $this->hasOne(DemoSessions::class , 'organization_ems_demo_session_id');
    }
   

}
