<?php

namespace App\Models\ConfigrationModels;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ConfigrationTemplateEmployeeExitReasonType extends Model
{
    use HasFactory;

    protected $table = 'configuration_template_employment_exit_reasons';

    protected $primaryKey = 'configuration_template_employment_exit_reason_id';
    public $incrementing = true;
    protected $keyType = 'int';

    protected $fillable = [
        'configuration_template_id',
        'configuration_template_exit_reason_type_id',
        'employment_exit_reason_name',
        'description'

    ];

}
