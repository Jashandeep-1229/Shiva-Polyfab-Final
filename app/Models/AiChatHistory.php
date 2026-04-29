<?php

namespace App\Models;

use App\Traits\AutoLogsActivity;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AiChatHistory extends Model
{
    use HasFactory, AutoLogsActivity;

    protected $fillable = [
        'user_id',
        'session_id',
        'user_message',
        'ai_response',
        'model_used',
        'response_time_ms',
    ];

    protected $casts = [
        'response_time_ms' => 'integer',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
