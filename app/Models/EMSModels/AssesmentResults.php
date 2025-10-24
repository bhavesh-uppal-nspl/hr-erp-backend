<?php

namespace App\Models\EMSModels;
use Illuminate\Database\Eloquent\Model;

class AssesmentResults extends Model
{
    protected $table = 'organization_ems_assessment_results';
    protected $primaryKey = 'organization_ems_assessment_result_id';

    protected $fillable = [
        'organization_id',
        'organization_entity_id',
        'organization_ems_assessment_id',
        'organization_ems_student_id',
        'organization_ems_admission_id',
        'score_obtained',
        'result_status',
        'remarks'
    ];

    public $timestamps = true;

    public function assesment()
    {
        return $this->belongsTo(Assesments::class , 'organization_ems_assessment_id');
    }
    public function student()
    {
        return $this->hasOne(Student::class , 'organization_ems_student_id');
    }
    public function admission()
    {
        return $this->hasOne(Admission::class , 'organization_ems_admission_id');
    }

}
