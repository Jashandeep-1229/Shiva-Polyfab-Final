<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use Illuminate\Database\Eloquent\SoftDeletes;

class ChatMessage extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'customer_id',
        'phone_no',
        'direction',
        'message',
        'message_type',
        'media_id',
        'media_path',
        'wa_message_id',
        'status',
        'read_at',
    ];

    public function customer()
    {
        return $this->belongsTo(AgentCustomer::class, 'customer_id');
    }
}
