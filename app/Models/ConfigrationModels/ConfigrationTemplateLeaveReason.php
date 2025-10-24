<?php

namespace App\Models\ConfigrationModels;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ConfigrationTemplateLeaveReason extends Model
{
    use HasFactory;

    protected $table = 'configuration_template_leave_reasons';

    protected $primaryKey = 'configuration_template_leave_reason_id';
    public $incrementing = true;
    protected $keyType = 'int';

    protected $fillable = [
        'configuration_template_id',
        'configuration_template_leave_reason_type_id',
        'leave_reason_name',
        'description'
    ];

}
