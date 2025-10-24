<?php

namespace App\Models\ConfigrationModels;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ConfigrationTemplateEducationLevels extends Model
{
    use HasFactory;

    protected $table = ' configuration_template_education_levels';

    protected $primaryKey = 'configuration_template_education_level_id';
    public $incrementing = true;
    protected $keyType = 'int';

    protected $fillable = [
        'configuration_template_id',
        'education_level_name',
        'education_level_short_name',
        'description',
        'sort_order',
        'is_active'

    ];

}
