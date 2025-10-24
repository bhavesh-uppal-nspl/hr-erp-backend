<?php

namespace App\Models\OrganizationModel;

use App\Models\GeneralModel\GeneralBusinessRegistrationType;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class OrganizationBusinessRegistration extends Model
{
    use HasFactory;

    // Optional: Set the custom table name
    protected $table = 'organization_business_registrations';

    // Optional: Specify the custom primary key
    protected $primaryKey = 'organization_business_registration_id';

    public $incrementing = true;
    protected $keyType = 'int';

    // Fillable fields
    protected $fillable = [
        'organization_business_registration_type_id',
        'organization_id',
        'registration_applicable',
        'registration_document_url',
        'registration_number',
        'registration_date',
        'registration_expiry_date',
        'organization_entity_id',
        'registration_expiry_date_applicable',
        'created_by'
    ];


    
//       public function getDocumentUrlAttribute($value)
// {
//     return $value ? asset('storage/app/public/' . $value) : null;
// }

public function getRegistrationDocumentUrlAttribute($value)
{
    return $value ? asset(Storage::url($value)) : null;
}


   
    public function organization()
    {
        return $this->belongsTo(Organization::class, 'organization_id', 'organization_id');
    }

     public function RegistrationType()
    {
        return $this->hasMany(OrganizationBusinessRegsitrationType::class, 'organization_business_registration_type_id', 'organization_business_registration_type_id');
    }


  

  
    
}
