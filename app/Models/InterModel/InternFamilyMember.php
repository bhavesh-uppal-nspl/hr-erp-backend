<?php

namespace App\Models\InterModel;

use App\Models\OrganizationModel\Organization;
use App\Models\OrganizationModel\OrganizationEducationDegree;
use App\Models\OrganizationModel\OrganizationEducationLevel;
use App\Models\OrganizationModel\OrganizationEducationStreams;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InternFamilyMember extends Model
{
    use HasFactory;

    protected $table = 'intern_family_members';

    protected $primaryKey = 'intern_family_member_id';

    public $incrementing = true;
    protected $keyType = 'int';

    protected $fillable = [
        'intern_id',
        'organization_id',
        'organization_entity_id',
        'full_name',
        'relationship_type',
        'date_of_birth',
        'gender',
        'phone_number',
        'email',
        'address',
        'is_emergency_contact',
        'is_dependent',
        'is_active'

    ];


    public function organization()
    {
        return $this->belongsTo(Organization::class, 'organization_id', 'organization_id');
    }

   

}
