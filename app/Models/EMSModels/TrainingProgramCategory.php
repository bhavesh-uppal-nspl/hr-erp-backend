<?php

namespace App\Models\EMSModels;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TrainingProgramCategory extends Model
{
    protected $table = 'organization_ems_training_program_categories';
    protected $primaryKey = 'organization_ems_training_program_category_id';

    protected $fillable = [
        'organization_id',
        'organization_entity_id',
        'training_program_category_name',
        'training_program_category_code',
        'description'
    ];

    public $timestamps = true;

}
