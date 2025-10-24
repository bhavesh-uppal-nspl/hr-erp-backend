<?php

namespace App\Models\OrganizationModel;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RegistrationDocument extends Model
{
    use HasFactory;

    // Table name (optional if it follows Laravel's naming convention)
    protected $table = 'registration_document';

    // Fillable fields
    protected $fillable = [
        'registration_id',
        'type',
        'applicable',
        'document_link',
        'document_number',
        'registration_date',
    ];

    // Cast 'applicable' to boolean
    protected $casts = [
        'applicable' => 'boolean',
        'registration_date' => 'date',
    ];

    // Relationship to OrganizationRegistration
    public function registration()
    {
        return $this->belongsTo(OrganizationRegistration::class, 'registration_id', 'registration_id');
    }
}
