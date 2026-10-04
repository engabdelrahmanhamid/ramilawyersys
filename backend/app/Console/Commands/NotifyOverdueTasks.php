<?php

namespace App\Console\Commands;

use App\Models\Notification;
use App\Models\Task;
use App\Repos\NotificationRepo;
use Illuminate\Console\Command;

class NotifyOverdueTasks extends Command
{
    protected $signature = 'tasks:notify-overdue
        {--dry-run : Only print what would be sent}
        {--max-days=30 : Skip tasks that became late longer ago than this (avoids a flood on the first run)}';

    protected $description = 'Notify the assigned employee once when an open task passes its end date';

    const TITLE = 'مهمة متأخرة';

    public function handle()
    {
        $sent = 0;
        Task::where('status', 1)
            ->whereNotNull('admin_id')
            ->whereNotNull('end_date')
            ->orderBy('id')
            ->chunkById(200, function ($tasks) use (&$sent) {
                foreach ($tasks as $task) {
                    if (!$task->isOverdue())
                        continue;
                    if ($task->dueAt()->lt(now()->subDays((int) $this->option('max-days'))))
                        continue;
                    $alreadySent = Notification::where('model_type', 'task')
                        ->where('model_id', $task->id)
                        ->where('title', self::TITLE)
                        ->exists();
                    if ($alreadySent)
                        continue;
                    $days = $task->dueAt()->copy()->startOfDay()->diffInDays(now()->startOfDay());
                    $description = $days > 0
                        ? 'تجاوزت المهمة موعدها بـ ' . $days . ' يوم'
                        : 'انتهى موعد المهمة اليوم ولم تُنجز';
                    if ($this->option('dry-run')) {
                        $this->line("task #{$task->id} -> admin #{$task->admin_id}: {$description}");
                    } else {
                        NotificationRepo::create(self::TITLE, $description, $task->id, 'task', $task->admin_id);
                    }
                    $sent++;
                }
            });
        $this->info(($this->option('dry-run') ? 'Would notify: ' : 'Notified: ') . $sent);
        return 0;
    }
}
