<?php

namespace App\Models\ConfigrationModels;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ConfigrationTemplateLocationOwnershipType extends Model
{
    use HasFactory;

    protected $table = 'configuration_template_location_ownership_types';

    protected $primaryKey = 'configuration_template_location_ownership_type_id';
    public $incrementing = true;
    protected $keyType = 'int';

    protected $fillable = [
        'configuration_template_id',
        'location_ownership_type_name',
        'description'


    ];

}
