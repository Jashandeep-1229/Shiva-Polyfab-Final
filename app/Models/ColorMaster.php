<?php

namespace App\Models;

use App\Traits\AutoLogsActivity;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ColorMaster extends Model
{
    use HasFactory, SoftDeletes, AutoLogsActivity;
    protected $fillable = ['user_id', 'name', 'is_temporary', 'status'];
}
