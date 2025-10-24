<?php

namespace App\Models\OrganizationModel;
use App\Models\GeneralModel\GeneralBusinessOwnershipTypeCategory;
use App\Models\GeneralModel\GeneralIndustry;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrganizationBusinessProfile extends Model
{
    use HasFactory;

    protected $table = 'organization_business_profiles';

    // Optional: Specify the custom primary key
    protected $primaryKey = 'organization_business_profile_id';

    public $incrementing = true;
    protected $keyType = 'int';

    // Fillable fields
    protected $fillable = [
        'organization_id',
        'general_business_ownership_type_category_id',
        'number_of_employees',
        'organization_business_ownership_type_id',
        'establishment_date',
        'organization_entity_id',
        'general_industry_id'
      
    ];

   
    public function organization()
    {
        return $this->belongsTo(Organization::class, 'organization_id', 'organization_id');
    }


     public function Entity()
    {
        return $this->hasMany(OrganizationEntities::class, 'organization_entity_id', 'organization_entity_id');
    }

      public function generalIndustry()
    {
        return $this->hasOne(GeneralIndustry::class, 'general_industry_id', 'general_industry_id');
    }

       public function generalCategory()
    {
        return $this->hasOne(GeneralBusinessOwnershipTypeCategory::class, 'general_business_ownership_type_category_id', 'general_business_ownership_type_category_id');
    }


        public function businessOwnership()
    {
        return $this->hasOne(OrganizationBusinessOwnnershipType::class, 'organization_business_ownership_type_id', 'organization_business_ownership_type_id');
    }

  

  

  
    
}
