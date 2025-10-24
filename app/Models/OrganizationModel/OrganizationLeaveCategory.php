<?php

namespace App\Models\OrganizationModel;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrganizationLeaveCategory extends Model
{
    use HasFactory;

    protected $table = 'organization_leave_categories';

    // Define the primary key
    protected $primaryKey = 'organization_leave_category_id';

    // Enable timestamps (created_at and updated_at)
    public $timestamps = true;

    // Mass assignable fields
    protected $fillable = [
        'organization_id',
        'leave_category_code',
        'organization_configuration_template_id',
        'leave_category_name',
        'organization_entity_id',
        'description',
        'created_by'
    ];

    // Relationship to the organization (optional)
    public function organization()
    {
        return $this->belongsTo(Organization::class, 'organization_id', 'organization_id');
    }
}
