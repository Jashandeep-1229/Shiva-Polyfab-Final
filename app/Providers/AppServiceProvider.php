<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        view()->composer(['lead.*', 'layouts.lead.*'], function ($view) {
            $tenDaysAgo = now()->subDays(10);
            $repeatSuggestionCount = \App\Models\JobCard::whereNotNull('lead_id')
                ->whereNotNull('complete_date')
                ->where('complete_date', '<=', $tenDaysAgo)
                ->whereHas('lead', function($q) {
                    $q->where('is_repeat', 0);
                })->count();

            $agentRepeatSuggestionCount = \App\Models\JobCard::whereNotNull('agent_lead_id')
                ->whereNotNull('complete_date')
                ->where('complete_date', '<=', $tenDaysAgo)
                ->count();
            
            $view->with([
                'repeatSuggestionCount' => $repeatSuggestionCount,
                'agentRepeatSuggestionCount' => $agentRepeatSuggestionCount
            ]);
        });
    }
}
