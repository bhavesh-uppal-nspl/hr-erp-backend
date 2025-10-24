<?php

namespace App\Models\ConfigrationModels;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ConfigrationTemplateWorkShiftTypes extends Model
{
    use HasFactory;
    protected $table = 'configuration_template_work_shift_types';

    protected $primaryKey = 'configuration_template_work_shift_type_id';
    public $incrementing = true;
    protected $keyType = 'int';

    protected $fillable = [
        'configuration_template_id',
        'work_shift_type_name',
        'work_shift_type_short_name'
    ];

}
