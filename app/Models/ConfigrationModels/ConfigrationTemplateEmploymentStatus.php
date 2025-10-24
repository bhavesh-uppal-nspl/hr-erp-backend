<?php

namespace App\Models\ConfigrationModels;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ConfigrationTemplateEmploymentStatus extends Model
{
    use HasFactory;

    protected $table = 'configuration_template_employment_statuses';

    protected $primaryKey = 'configuration_template_employment_status_id';
    public $incrementing = true;
    protected $keyType = 'int';

    protected $fillable = [
        'configuration_template_id',
        'employment_status_name',
        'description'

    ];

}
