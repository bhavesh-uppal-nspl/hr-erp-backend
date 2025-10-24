<?php

namespace App\Models\ConfigrationModels;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ConfigrationTemplateLeaveDurationType extends Model
{
    use HasFactory;

    protected $table = 'configuration_template_leave_duration_types';

    protected $primaryKey = 'configuration_template_leave_duration_type_id';
    public $incrementing = true;
    protected $keyType = 'int';

    protected $fillable = [
        'configuration_template_id',
        'leave_duration_type_name'

    ];

}
