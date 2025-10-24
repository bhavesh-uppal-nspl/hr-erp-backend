<?php
namespace App\Models\OrganizationModel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrganizationLeavePolicy extends Model
{
    use HasFactory;

    protected $table = 'organization_leave_policies';

    protected $primaryKey = 'organization_leave_policy_id';

    public $timestamps = true;

    // Mass assignable fields
    protected $fillable = [
        'organization_id',
        'organization_entity_id',
        'organization_leave_entitlement_id',
        'policy_name',
        'policy_description',
        'usage_period',
        'custom_period_days',
        'max_leaves_per_period',
        'is_active'
    ];

    // Relationship to the organization (optional)
    public function organization()
    {
        return $this->belongsTo(Organization::class, 'organization_id', 'organization_id');
    }
    public function LeaveEntitlement()
    {
        return $this->belongsTo(OrganizationLeaveEntitlement::class, 'organization_leave_entitlement_id', 'organization_leave_entitlement_id');
    }
}
