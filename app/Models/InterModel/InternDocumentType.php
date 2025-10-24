<?php

namespace App\Models\InterModel;

use App\Models\OrganizationModel\Organization;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InternDocumentType extends Model
{
    use HasFactory;

    protected $table = 'intern_document_types';

    protected $primaryKey = 'intern_document_type_id';

    public $incrementing = true;
    protected $keyType = 'int';

    protected $fillable = [
        'organization_id',
        'organization_entity_id',
        'document_type_name',
        'document_type_short_name',
        'is_active',

    ];


    

    public function organization()
    {
        return $this->belongsTo(Organization::class, 'organization_id', 'organization_id');
    }

   
}
