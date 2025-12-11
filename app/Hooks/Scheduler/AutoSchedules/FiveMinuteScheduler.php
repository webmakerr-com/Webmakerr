<?php

namespace Webmakerr\App\Hooks\Scheduler\AutoSchedules;


use Webmakerr\App\Hooks\Scheduler\JobRunner;

class FiveMinuteScheduler
{
    public function register(): void
    {
        webmakerr_add_action('webmakerr_cart/scheduler/five_minutes_tasks', [$this, 'handle']);
    }

    public function handle(): void
    {
        (new JobRunner())->start();
    }
}
