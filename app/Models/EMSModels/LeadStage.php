<?php

namespace App\Models\EMSModels;
use Illuminate\Database\Eloquent\Model;

class LeadStage extends Model
{
    protected $table = 'organization_ems_lead_stages';
    protected $primaryKey = 'organization_ems_lead_stage_id';

    protected $fillable = [
        'organization_id',
        'organization_entity_id',
        'lead_stage_name',
        'lead_stage_short_name',
        'lead_stage_sequence_number',
        'description'
    ];

    public $timestamps = true;

    
}
