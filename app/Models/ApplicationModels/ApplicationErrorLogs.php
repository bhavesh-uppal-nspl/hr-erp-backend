<?php

namespace App\Models\ApplicationModels;
use Illuminate\Database\Eloquent\Model;

class ApplicationErrorLogs extends Model
{

    protected $table = 'application_error_logs';
    protected $primaryKey = 'error_log_id';
    public $timestamps = false;

    protected $fillable = [
        'client_id',
        'triggered_by_user_id',
        'error_message',
        'application_module_id',
        'application_module_action_id',
        'stack_trace',
        'url_or_endpoint',
        'error_type',
        'payload_data',
        'error_type',
        'severity',
        'created_at',
        'updated_at',
    ];
    protected $casts = [
        'payload_data' => 'array',
        'logged_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'user_id' => 'integer',
        'client_id' => 'integer',
    ];
    public function User()
    {
        return $this->hasMany(ApplicationUsers::class, 'triggered_by_user_id', 'application_user_id');
    }


}
