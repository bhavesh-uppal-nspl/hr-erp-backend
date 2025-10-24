<?php

namespace App\Models\OrganizationModel;

use App\Models\GeneralModel\GeneralBusinessRegistrationType;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrganizationBusinessRegsitrationType extends Model
{
    use HasFactory;

    // Optional: Set the custom table name
    protected $table = 'organization_business_registration_types';

    // Optional: Specify the custom primary key
    protected $primaryKey = 'organization_business_registration_type_id';

    public $incrementing = true;
    protected $keyType = 'int';

    // Fillable fields
    protected $fillable = [
        'organization_configuration_template_id',
        'organization_id',
        'business_registration_type_name',
        'business_registration_type_code',
        'description',
        'created_by',
        'organization_entity_id'
    ];

   
    public function organization()
    {
        return $this->belongsTo(Organization::class, 'organization_id', 'organization_id');
    }

    public function businessregistration()
    {
        return $this->belongsTo(OrganizationBusinessRegistration::class, 'organization_business_registration_type_id', 'organization_business_registration_type_id');
    }


  
    
}
