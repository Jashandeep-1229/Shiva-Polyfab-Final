<?php

namespace App\Models;

use App\Traits\AutoLogsActivity;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AgentLeadHistory extends Model
{
    use HasFactory, AutoLogsActivity;

    protected $fillable = ['agent_lead_id', 'user_id', 'type', 'description'];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
