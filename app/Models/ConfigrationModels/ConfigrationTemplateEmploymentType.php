<?php

namespace App\Models\ConfigrationModels;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ConfigrationTemplateEmploymentType extends Model
{
    use HasFactory;

    protected $table = 'configuration_template_employment_types';

    protected $primaryKey = 'configuration_template_employment_type_id';
    public $incrementing = true;
    protected $keyType = 'int';

    protected $fillable = [
        'configuration_template_id',
        'employment_type_name',
        'description'

    ];

}
