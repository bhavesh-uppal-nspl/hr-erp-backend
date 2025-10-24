<?php

namespace App\Models\EMSModels;
use Illuminate\Database\Eloquent\Model;

class TrainingProgram extends Model
{
    protected $table = 'organization_ems_training_programs';
    protected $primaryKey = 'organization_ems_training_program_id';

    protected $fillable = [
        'organization_id',
        'organization_entity_id',
        'organization_ems_training_program_category_id',
        'training_program_name',
        'training_program_code',
        'description',
        'duration_in_hours',
        'is_active'
    ];

    public $timestamps = true;

    protected $casts = [
        'is_active' => 'boolean'
    ];

    public function category()
    {
        return $this->hasOne(TrainingProgramCategory::class, 'organization_ems_training_program_category_id');
    }
}
