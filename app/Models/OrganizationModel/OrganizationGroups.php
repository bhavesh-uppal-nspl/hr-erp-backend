<?php

namespace App\Models\OrganizationModel;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrganizationGroups extends Model
{
    use HasFactory;

    // Table name (optional if Laravel can't infer it)
    protected $table = 'organization_groups';

    // Primary key
    protected $primaryKey = 'organization_group_id';

    public $incrementing = true;
    protected $keyType = 'int';

    // Fillable fields
    protected $fillable = [
       
        'client_id',
        'organization_group_short_name',
        'organization_group_name',
        'description',
    ];

    // Relationships
    public function businessUnit()
    {
        return $this->belongsTo(OrganizationBusinessUnit::class, 'business_unit_id', 'business_unit_id');
    }


 
}
