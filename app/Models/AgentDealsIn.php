<?php

namespace App\Models;

use App\Traits\AutoLogsActivity;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AgentDealsIn extends Model
{
    use HasFactory, AutoLogsActivity;

    protected $fillable = ['name', 'status'];
}
