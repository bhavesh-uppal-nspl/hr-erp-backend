<?php

namespace App\Models\EMSModels;
use Illuminate\Database\Eloquent\Model;

class LeadSource extends Model
{
    protected $table = 'organization_ems_lead_sources';
    protected $primaryKey = 'organization_ems_lead_source_id';

    protected $fillable = [
        'organization_id',
        'organization_entity_id',
        'lead_source_name',
        'lead_source_short_name',
        'description'
    ];

    public $timestamps = true;


}
