<?php

namespace App\Providers;

use App\Events\CatchProcessed;
use App\Listeners\AwardCatchRewards;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;

class EventServiceProvider extends ServiceProvider
{
    protected $listen = [
        CatchProcessed::class => [
            AwardCatchRewards::class,
        ],
    ];
}
