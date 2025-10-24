<?php

namespace App\Models\ConfigrationModels;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ConfigrationTemplateBusinessRegistrationType extends Model
{
    use HasFactory;

    protected $table = 'configuration_template_business_registration_types';

    protected $primaryKey = 'configuration_template_business_registration_type_id';
    public $incrementing = true;
    protected $keyType = 'int';

    protected $fillable = [
        'configuration_template_id',
        'business_registration_type_name',
        'business_registration_type_code',
        'description'

    ];

}
