<?php

namespace App\Models\ConfigrationModels;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ConfigrationTemplateLeaveReasonType extends Model
{
    use HasFactory;

    protected $table = 'configuration_template_leave_reason_types';

    protected $primaryKey = 'configuration_template_leave_reason_type_id';
    public $incrementing = true;
    protected $keyType = 'int';

    protected $fillable = [
        'configuration_template_id',
        'configuration_template_leave_type_id',
        'leave_reason_type_name',
        'description'
    ];

}
