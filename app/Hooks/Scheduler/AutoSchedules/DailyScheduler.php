<?php

namespace Webmakerr\App\Hooks\Scheduler\AutoSchedules;

use Webmakerr\App\Hooks\Scheduler\JobRunner;

class DailyScheduler
{
    public function register(): void
    {
        webmakerr_add_action('webmakerr_cart/scheduler/daily_tasks', [$this, 'handle']);
    }

    public function handle()
    {
        // do your daily tasks here
    }

}