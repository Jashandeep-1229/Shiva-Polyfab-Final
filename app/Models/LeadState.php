<?php

namespace App\Models;

use App\Traits\AutoLogsActivity;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LeadState extends Model
{
    use HasFactory, AutoLogsActivity;

    protected $fillable = ['name', 'status'];

    public function cities()
    {
        return $this->hasMany(LeadCity::class, 'state_id');
    }
}
