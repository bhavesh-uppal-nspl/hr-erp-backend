<?php

namespace App\Models\OrganizationModel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrganizationSettingType extends Model
{
    use HasFactory;
    protected $table = 'organization_setting_types';
    protected $primaryKey = 'organization_setting_type_id';
    public $timestamps = true;
    protected $fillable = [
        'setting_type_name',
        'organization_entity_id'
    ];

  

   
}

