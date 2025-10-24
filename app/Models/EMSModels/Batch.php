<?php

namespace App\Models\EMSModels;
use Illuminate\Database\Eloquent\Model;

class Batch extends Model
{
    protected $table = 'organization_ems_batches';
    protected $primaryKey = 'organization_ems_batch_id';

    protected $fillable = [
        'organization_id',
        'organization_entity_id',
        'training_program_id',
        'batch_code',
        'batch_name',
        'start_date',
        'end_date',
        'batch_mode',
        'status',
        'preferred_study_slot',
        'timing_details',
        'remarks'
    ];

    public $timestamps = true;

    public function trainingProgram()
    {
        return $this->hasOne(TrainingProgram::class ,'organization_ems_training_program_id', 'training_program_id');
    }


}
