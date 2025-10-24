<?php

namespace App\Models\InterModel;

use App\Models\OrganizationModel\Organization;
use App\Models\OrganizationModel\OrganizationEducationDegree;
use App\Models\OrganizationModel\OrganizationEducationLevel;
use App\Models\OrganizationModel\OrganizationEducationStreams;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InternLeaveCategoryMonthlySummary extends Model
{
    use HasFactory;

    protected $table = 'intern_leave_category_monthly_summaries';

    protected $primaryKey = 'intern_leave_category_monthly_summary_id';

    public $incrementing = true;
    protected $keyType = 'int';

    protected $fillable = [
        'intern_id',
        'organization_id',
        'organization_entity_id',
        'year',
        'month',
        'organization_leave_category_id ',
        'total_leaves',
        'approved_leaves',
        'unapproved_leaves',
        'rejected_leaves',

    ];


    public function organization()
    {
        return $this->belongsTo(Organization::class, 'organization_id', 'organization_id');
    }

   

}
