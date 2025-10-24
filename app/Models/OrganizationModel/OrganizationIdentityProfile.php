<?php

namespace App\Models\OrganizationModel;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrganizationIdentityProfile extends Model
{
    use HasFactory;
    protected $table = 'organization_identity_profiles';

    protected $primaryKey = 'organization_identity_profile_id';

    // Define the fillable fields
    protected $fillable = [
        'organization_id',
        'website',
        'email',
        'phone',
        'organization_entity_id',
        'logo_url',
    ];

    public function organization()
    {
        return $this->belongsTo(Organization::class, 'organization_id', 'organization_id');
    }
}
