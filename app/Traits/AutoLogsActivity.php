<?php

namespace App\Traits;

use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

trait AutoLogsActivity {
    use LogsActivity;

    public function getActivitylogOptions(): LogOptions
    {
        $logName = str_replace('App\Models\\', '', get_class($this));
        
        return LogOptions::defaults()
            ->logAll()
            ->useLogName($logName)
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }
}
