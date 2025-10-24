<?php

namespace App\Models\OrganizationModel;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrganizationResidentailOwnershipType extends Model
{
    use HasFactory;
    protected $table = 'organization_residential_ownership_types';
    protected $primaryKey = 'organization_residential_ownership_type_id';
    public $incrementing = true;
    protected $keyType = 'int';
    protected $fillable = [
        'residential_ownership_type_name',
        'organization_id',
        'description',
        'organization_configuration_template_id',
        'organization_entity_id',
        'created_by'
    ];

     public function organization()
    {
        return $this->belongsTo(Organization::class, 'organization_id', 'organization_id');
    }


   
}