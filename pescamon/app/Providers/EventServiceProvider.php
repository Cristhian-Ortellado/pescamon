<?php

namespace App\Providers;

use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;

class EventServiceProvider extends ServiceProvider
{
    /**
     * Laravel auto-registers listeners in app/Listeners by convention
     * (AwardCatchRewards type-hints CatchProcessed), so no explicit mapping
     * is needed here — adding one would fire the listener twice.
     */
    protected $listen = [];
}
