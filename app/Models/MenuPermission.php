<?php

namespace App\Models;

use App\Traits\AutoLogsActivity;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MenuPermission extends Model
{
    use HasFactory, AutoLogsActivity;

    protected $fillable = [
        'user_id',
        'role_name',
        'menu_key',
        'can_view',
        'can_add',
        'can_edit',
        'can_next_process',
        'data_access',
    ];
}
