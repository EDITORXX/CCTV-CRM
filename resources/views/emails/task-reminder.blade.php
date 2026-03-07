<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; margin: 0; padding: 0; background: #f4f4f4; }
        .container { max-width: 600px; margin: 30px auto; background: #fff; border-radius: 8px; overflow: hidden; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
        .header { background: #0d6efd; color: #fff; padding: 24px 30px; }
        .header h1 { margin: 0; font-size: 22px; }
        .body { padding: 30px; }
        .info-table { width: 100%; border-collapse: collapse; margin: 20px 0; }
        .info-table td { padding: 10px 12px; border-bottom: 1px solid #eee; }
        .info-table td:first-child { font-weight: bold; color: #666; width: 140px; }
        .badge { display: inline-block; padding: 4px 12px; border-radius: 20px; font-size: 13px; font-weight: bold; }
        .badge-warning { background: #fff3cd; color: #856404; }
        .badge-info { background: #cff4fc; color: #055160; }
        .badge-danger { background: #f8d7da; color: #842029; }
        .footer { padding: 20px 30px; background: #f8f9fa; text-align: center; font-size: 13px; color: #666; }
    </style>
</head>
<body>
<div class="container">
    <div class="header">
        <h1>
            @if($reminderType === 'due')
                Task Due Reminder
            @else
                Task Reminder
            @endif
        </h1>
    </div>
    <div class="body">
        <p>Hello <strong>{{ $task->assignee->name ?? 'Team' }}</strong>,</p>

        @if($reminderType === 'due')
            <p>This is a reminder that the following task is <strong>due tomorrow</strong>:</p>
        @else
            <p>This is a reminder for the following task:</p>
        @endif

        <table class="info-table">
            <tr>
                <td>Task</td>
                <td><strong>{{ $task->title }}</strong></td>
            </tr>
            @if($task->category)
            <tr>
                <td>Category</td>
                <td>{{ $task->category->name }}</td>
            </tr>
            @endif
            <tr>
                <td>Status</td>
                <td>
                    @if($task->status === 'pending')
                        <span class="badge badge-warning">Pending</span>
                    @elseif($task->status === 'in_progress')
                        <span class="badge badge-info">In Progress</span>
                    @endif
                </td>
            </tr>
            @if($task->due_date)
            <tr>
                <td>Due Date</td>
                <td>
                    <span class="{{ $task->due_date->isPast() ? '' : '' }}">
                        {{ $task->due_date->format('d M Y') }}
                    </span>
                </td>
            </tr>
            @endif
            @if($task->notes)
            <tr>
                <td>Notes</td>
                <td>{{ $task->notes }}</td>
            </tr>
            @endif
        </table>

        <p>Please take the necessary action to complete this task on time.</p>
        <p>Thank you.</p>
    </div>
    <div class="footer">
        <p>This is an automated reminder from your task management system.</p>
    </div>
</div>
</body>
</html>
