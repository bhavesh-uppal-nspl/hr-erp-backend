<?php

namespace App\Models\InterModel;

use App\Models\OrganizationModel\Organization;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InterExitRecord extends Model
{
    use HasFactory;

    protected $table = 'intern_exit_records';

    protected $primaryKey = 'intern_exit_record_id';

    public $incrementing = true;
    protected $keyType = 'int';

    protected $fillable = [
        'organization_id',
        'organization_entity_id',
        'intern_id',
        'exit_type',
        'exit_date',
        'last_working_day',
        'reason_for_exit',
        'handover_completed',
        'handover_notes',
        'clearance_status',
        'manager_feedback',
        'intern_feedback',
        'certificate_issued',
        'certificate_issue_date'
    ];

    public function organization()
    {
        return $this->belongsTo(Organization::class, 'organization_id', 'organization_id');
    }

      public function Intern()
    {
        return $this->belongsTo(Interns::class, 'intern_id', 'intern_id');
    }

   
}
