<?php
namespace App\Models\OrganizationModel;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PersonnelType extends Model
{
    use HasFactory;

    // Define the table associated with the model
    protected $table = 'personnel_types';

    // Define the primary key (optional if it follows Laravel's default)
    protected $primaryKey = 'personnel_type_id';

    // Define the fillable fields to protect against mass-assignment vulnerabilities
    protected $fillable = [
        'type_name',
        'is_active',
    ];

    // Set default value for the 'is_active' attribute if not provided
    protected $attributes = [
        'is_active' => 1,
    ];

    // Optional: You can define any other custom logic here, such as accessor or mutators
}
