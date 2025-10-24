<?php

namespace App\Models\ConfigrationModels;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ConfigrationTemplateLeaveType extends Model
{
    use HasFactory;

    protected $table = 'configuration_template_leave_types';

    protected $primaryKey = 'configuration_template_leave_type_id';
    public $incrementing = true;
    protected $keyType = 'int';

    protected $fillable = [
        'configuration_template_id',
        'leave_type_name',
        'leave_type_code',
        'description',
        'max_days_allowed',
        'carry_forward',
        'requires_approval',
        'is_active'


    ];

}
