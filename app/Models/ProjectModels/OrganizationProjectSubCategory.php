<?php

namespace App\Models\ProjectModels;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class OrganizationProjectSubCategory extends Model
{
    protected $table = 'organization_project_subcategories';
    protected $primaryKey = 'organization_project_subcategory_id';

    protected $fillable = [
        'organization_id',
        'organization_entity_id',
        'organization_project_category_id',
        'project_subcategory_name',
        'project_subcategory_short_name',
        'description',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public $timestamps = true;

    public function category()
    {
        return $this->belongsTo(OrganizationProjectCategory::class, 'organization_project_category_id');
    }
}
