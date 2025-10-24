<?php

namespace App\Models\OrganizationModel;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PersonnelEmploymentStatus extends Model
{
    use HasFactory;

    // Define the table associated with the model
    protected $table = 'personnel_employee_status';

    // Define the primary key (optional if it follows Laravel's default)
    protected $primaryKey = 'empoyment_status_id';

    // Define the fillable fields to protect against mass-assignment vulnerabilities
    protected $fillable = [
        'status_name',
        'status_description',
    ];

    // Optional: Add any other methods or custom logic as needed
}
