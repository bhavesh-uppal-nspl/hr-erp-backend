<?php

namespace App\Models\InterModel;

use App\Models\OrganizationModel\Organization;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InternContact extends Model
{
    use HasFactory;

    protected $table = 'intern_contacts';

    protected $primaryKey = 'intern_contact_id';

    public $incrementing = true;
    protected $keyType = 'int';

    protected $fillable = [
        'organization_id',
        'organization_entity_id',
        'intern_id',
        'personal_phone_number',
        'alternate_phone_number',
        'personal_email',
        'emergency_contact_name',
        'emergency_contact_relation',
        'emergency_contact_phone'
    ];


    

    public function organization()
    {
        return $this->belongsTo(Organization::class, 'organization_id', 'organization_id');
    }

   
}
