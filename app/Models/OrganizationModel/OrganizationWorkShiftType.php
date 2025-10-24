<?php

namespace App\Models\OrganizationModel;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrganizationWorkShiftType extends Model
{
    use HasFactory;

    // Define the table associated with the model
    protected $table = 'organization_work_shift_types';

    // Define the primary key
    protected $primaryKey = 'organization_work_shift_type_id';

    // Define the fillable fields to protect against mass-assignment vulnerabilities
    protected $fillable = [
        'organization_id',
        'organization_entity_id',
        'work_shift_type_name',
        'organization_configuration_template_id',
        'work_shift_type_short_name',
    ];

    // Define the relationship with the PersonnelMaster model
    public function organization()
    {
        return $this->belongsTo(Organization::class, 'organization_id');
    }

    public function workshift()
    {
        return $this->belongsTo(OrganizationWorkShift::class, 'organization_work_shift_type_id');
    }

}
