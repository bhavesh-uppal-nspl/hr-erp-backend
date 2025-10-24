<?php

namespace App\Models\OrganizationModel;
use App\Models\GeneralModel\GeneralBusinessOwnershipTypeCategory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrganizationBusinessOwnnershipType extends Model
{
    use HasFactory;

    protected $table = 'organization_business_ownership_types';

    // Optional: Specify the custom primary key
    protected $primaryKey = 'organization_business_ownership_type_id';

    public $incrementing = true;
    protected $keyType = 'int';

    // Fillable fields
    protected $fillable = [
        'organization_id',
        'organization_configuration_template_id',
        'organization_business_ownership_type_name',
        'created_by',
        'general_business_ownership_type_category_id',
        'organization_entity_id'
      
    ];

   
    public function organization()
    {
        return $this->belongsTo(Organization::class, 'organization_id', 'organization_id');
    }

      public function generalownershipCategory()
    {
        return $this->hasMany(GeneralBusinessOwnershipTypeCategory::class, 'general_business_ownership_type_category_id', 'general_business_ownership_type_category_id');
    }

  

  
    
}
