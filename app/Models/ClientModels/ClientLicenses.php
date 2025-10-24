<?php

namespace App\Models\ClientModels;
use App\Models\ClientModels\Client;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ClientLicenses extends Model
{
    use HasFactory;

    protected $table = 'client_licenses';

    protected $primaryKey = 'client_license_id';
    public $incrementing = true;
 
    protected $fillable = [
        'client_id',
        'license'
    ];

    public function Client()
    {
        return $this->hasOne(Client::class, 'client_id', 'client_id');
    }

}
