<?php

namespace App\Models\OrganizationModel;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PersonnelContactDetail extends Model
{
    use HasFactory;

    // Define the table associated with the model
    protected $table = 'personnel_contact_details';

    // Define the primary key
    protected $primaryKey = 'contact_id';

    // Define the fillable fields to protect against mass-assignment vulnerabilities
    protected $fillable = [
        'personnel_id',
        'personal_email',
        'work_email',
        'personal_phone_no',
        'alternate_personal_email',
        'alternate_personal_phone_no',
        'emergency_contact_name',
        'emergency_personal_contact_phone_no',
        'emergency_contact_relation',
        'preferred_contact_method',
    ];

    // Define the relationship with the PersonnelMaster model
    public function personnel()
    {
        return $this->belongsTo(PersonnelMaster::class, 'personnel_id', 'id');
    }

    // Optional: Add any other methods or custom logic as needed
}
