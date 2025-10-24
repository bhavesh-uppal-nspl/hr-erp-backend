<?php

namespace App\Models\OrganizationModel;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrganizationEmployementStatus extends Model
{
    use HasFactory;

    // Define the table associated with the model
    protected $table = 'organization_employment_statuses';

    // Define the primary key
    protected $primaryKey = 'organization_employment_status_id';

    // Enable timestamps (created_at and updated_at)
    public $timestamps = true;

    // Define the fillable fields to allow mass-assignment
    protected $fillable = [
        'organization_id',
        'employment_status_name',
        'organization_configuration_template_id',
        'organization_entity_id',
        'created_by'
    ];


      public function organization()
    {
        return $this->belongsTo(Organization::class, 'organization_id', 'organization_id');
    }



  
  
}
