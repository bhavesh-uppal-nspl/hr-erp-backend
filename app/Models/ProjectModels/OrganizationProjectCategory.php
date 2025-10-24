<?php

namespace App\Models\ProjectModels;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class OrganizationProjectCategory extends Model
{
    protected $table = 'organization_project_categories';
    protected $primaryKey = 'organization_project_category_id';

    protected $fillable = [
        'organization_id',
        'organization_entity_id',
        'project_category_name',
        'project_category_short_name',
        'description',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public $timestamps = true;

    public function projects(): HasMany
    {
        return $this->hasMany(OrganizationProject::class, 'organization_project_category_id');
    }
}
