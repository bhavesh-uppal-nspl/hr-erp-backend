<?php

namespace App\Models\OrganizationModel;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrganizationRegistrationType extends Model
{
    use HasFactory;

    // Optional: Set the custom table name
    protected $table = 'organization_registrations';

    // Optional: Specify the custom primary key
    protected $primaryKey = 'registration_id';

    public $incrementing = true;
    protected $keyType = 'int';

    // Fillable fields
    protected $fillable = [
        'organization_id',
    ];

    // Define the relationship with OrganizationMaster
    public function organization()
    {
        return $this->belongsTo(Organization::class, 'organization_id', 'organization_id');
    }


    public function documents()
    {
        return $this->hasMany(RegistrationDocument::class, 'registration_id', 'registration_id');
    }
    
}
