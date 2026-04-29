<?php

namespace App\Models;

use Spatie\Activitylog\Models\Activity as SpatieActivity;
use Illuminate\Database\Eloquent\SoftDeletes;

class Activity extends SpatieActivity
{
    use SoftDeletes;
}
