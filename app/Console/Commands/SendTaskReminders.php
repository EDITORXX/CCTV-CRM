<?php

namespace App\Console\Commands;

use App\Mail\TaskReminderMail;
use App\Models\Task;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;

class SendTaskReminders extends Command
{
    protected $signature = 'tasks:send-reminders';

    protected $description = 'Send email reminders for tasks with custom reminder dates and upcoming due dates.';

    public function handle(): int
    {
        $sentCount = 0;

        // Custom reminder: reminder_date has passed and not yet sent
        $customReminders = Task::with(['assignee', 'category'])
            ->where('status', '!=', 'completed')
            ->whereNotNull('reminder_date')
            ->where('reminder_date', '<=', now())
            ->where('custom_reminder_sent', false)
            ->get();

        foreach ($customReminders as $task) {
            if (!$task->assignee || !$task->assignee->email) {
                $this->warn("No email for task #{$task->id}: {$task->title}");
                continue;
            }

            Mail::to($task->assignee->email)->send(new TaskReminderMail($task, 'custom'));
            $task->update(['custom_reminder_sent' => true]);
            $sentCount++;
        }

        // Due date reminder: due_date is tomorrow and not yet sent
        $tomorrow = now()->addDay()->toDateString();

        $dueReminders = Task::with(['assignee', 'category'])
            ->where('status', '!=', 'completed')
            ->whereNotNull('due_date')
            ->whereDate('due_date', $tomorrow)
            ->where('due_reminder_sent', false)
            ->get();

        foreach ($dueReminders as $task) {
            if (!$task->assignee || !$task->assignee->email) {
                $this->warn("No email for task #{$task->id}: {$task->title}");
                continue;
            }

            Mail::to($task->assignee->email)->send(new TaskReminderMail($task, 'due'));
            $task->update(['due_reminder_sent' => true]);
            $sentCount++;
        }

        $this->info("Task reminders sent: {$sentCount}");

        return self::SUCCESS;
    }
}
