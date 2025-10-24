<?php

namespace App\Models\ClientModels;
use App\Models\ApplicationModels\ApplicationUsers;
use App\Models\OrganizationModel\Organization;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Client extends Model
{
    use HasFactory;

    protected $table = 'clients';

    protected $primaryKey = 'client_id';
    public $incrementing = true;
    protected $keyType = 'int';

    protected $fillable = [
        'client_name',
    ];

    // Relationships
    public function ApplicationUser()
    {
        return $this->belongsTo(ApplicationUsers::class, 'client_id', 'client_id');
    }

     public function Organization()
    {
        return $this->hasMany(Organization::class, 'client_id', 'client_id');
    }
   
}
