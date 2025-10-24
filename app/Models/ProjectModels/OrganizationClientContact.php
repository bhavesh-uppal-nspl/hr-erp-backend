<?php

namespace App\Models\ProjectModels;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OrganizationClientContact extends Model
{
    protected $table = 'organization_client_contacts';
    protected $primaryKey = 'organization_client_contact_id';

    protected $fillable = [
        'organization_client_id',
        'organization_id',
        'organization_entity_id',
        'contact_name',
        'designation',
        'stakeholder_role',
        'email',
        'phone',
        'alternate_phone',
        'is_primary_contact',
    ];

    protected $casts = [
        'is_primary_contact' => 'boolean',
    ];

    public $timestamps = true;

    // Relationships
    public function client(): BelongsTo
    {
        return $this->belongsTo(OrganizationClient::class, 'organization_client_id');
    }

}
