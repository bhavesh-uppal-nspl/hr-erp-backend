<?php

namespace App\Models\EMSModels;
use Illuminate\Database\Eloquent\Model;

class Assesments extends Model
{
    protected $table = 'organization_ems_assessments';
    protected $primaryKey = 'organization_ems_assessment_id';

    protected $fillable = [
        'organization_id',
        'organization_entity_id',
        'training_program_id',
        'organization_ems_batch_id',
        'assessment_name',
        'assessment_type',
        'max_score',
        'passing_score',
        'assessment_date',
        'status',
        'remarks'
    ];

    public $timestamps = true;

    public function trainingProgram()
    {
        return $this->hasOne(TrainingProgram::class , 'organization_ems_training_program_id','training_program_id');
    }
    public function batch()
    {
        return $this->hasOne(Batch::class , 'organization_ems_batch_id');
    }

}
