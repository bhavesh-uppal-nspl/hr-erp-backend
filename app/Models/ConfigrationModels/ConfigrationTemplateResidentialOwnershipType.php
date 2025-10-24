<?php

namespace App\Models\ConfigrationModels;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ConfigrationTemplateResidentialOwnershipType extends Model
{
    use HasFactory;

    protected $table = 'configuration_template_residential_ownership_types';

    protected $primaryKey = 'configuration_template_residential_ownership_type_id';
    public $incrementing = true;
    protected $keyType = 'int';

    protected $fillable = [
        'configuration_template_id',
        'residential_ownership_type_name',
        'description'


    ];

}
