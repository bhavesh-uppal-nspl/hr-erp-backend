<?php

namespace App\Models\ConfigrationModels;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ConfigrationTemplateEmployeeAddressType extends Model
{
    use HasFactory;

    protected $table = 'configuration_template_employee_address_types';

    protected $primaryKey = 'configuration_template_employee_address_type_id';
    public $incrementing = true;
    protected $keyType = 'int';

    protected $fillable = [
        'configuration_template_id',
        'employee_address_type_name',
        'description'

    ];

}
