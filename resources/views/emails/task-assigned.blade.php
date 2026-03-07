<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; margin: 0; padding: 0; background: #f4f4f4; }
        .container { max-width: 600px; margin: 30px auto; background: #fff; border-radius: 8px; overflow: hidden; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
        .header { background: #198754; color: #fff; padding: 24px 30px; }
        .header h1 { margin: 0; font-size: 22px; }
        .body { padding: 30px; }
        .info-table { width: 100%; border-collapse: collapse; margin: 20px 0; }
        .info-table td { padding: 10px 12px; border-bottom: 1px solid #eee; }
        .info-table td:first-child { font-weight: bold; color: #666; width: 140px; }
        .footer { padding: 20px 30px; background: #f8f9fa; text-align: center; font-size: 13px; color: #666; }
    </style>
</head>
<body>
<div class="container">
    <div class="header">
        <h1>New Task Assigned</h1>
    </div>
    <div class="body">
        <p>Hello <strong>{{ $task->assignee->name ?? 'Team' }}</strong>,</p>
        <p>A new task has been assigned to you by <strong>{{ $task->creator->name ?? 'Admin' }}</strong>.</p>

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
            @if($task->customer_name)
            <tr>
                <td>Customer</td>
                <td>{{ $task->customer_name }}@if($task->customer_phone) &mdash; {{ $task->customer_phone }}@endif</td>
            </tr>
            @endif
            @if($task->due_date)
            <tr>
                <td>Due Date</td>
                <td>{{ $task->due_date->format('d M Y') }}</td>
            </tr>
            @endif
            @if($task->notes)
            <tr>
                <td>Notes</td>
                <td>{{ $task->notes }}</td>
            </tr>
            @endif
        </table>

        <p>Please check and complete this task on time.</p>
        <p>Thank you.</p>
    </div>
    <div class="footer">
        <p>This is an automated notification from your task management system.</p>
    </div>
</div>
</body>
</html>
