<?php

namespace Webmakerr\App\Hooks\Scheduler\AutoSchedules;

use Webmakerr\App\Helpers\Status;
use Webmakerr\App\Models\ScheduledAction;

class HourlyScheduler
{
    public function register(): void
    {
        webmakerr_add_action('webmakerr_cart/scheduler/hourly_tasks', [$this, 'handle'], 10);
    }

    public function handle()
    {
        // hourly tasks, remove all completed tasks
        $this->removeCompleteTasks();

    }


    private function removeCompleteTasks()
    {
        ScheduledAction::query()->where('status', Status::SCHEDULE_COMPLETED)
            ->limit(5000)
            ->delete();
    }
}
