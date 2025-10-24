<?php

namespace App\Models\OrganizationModel;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrganizationLanguages extends Model
{
    use HasFactory;
    protected $table = 'organization_languages';

    protected $primaryKey = 'organization_language_id';

    // Define the fillable fields
    protected $fillable = [
        'organization_id',
        'language_name',
        'language_code',
        'description',
        'created_by',
        'organization_entity_id',
        'organization_configuration_template_id'
    ];

     public function organization()
    {
        return $this->belongsTo(Organization::class, 'organization_id', 'organization_id');
    }
}
