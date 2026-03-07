<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Task extends Model
{
    use \App\Traits\BelongsToCompany;

    protected $fillable = [
        'company_id', 'title', 'task_category_id', 'notes',
        'assigned_to', 'created_by', 'due_date', 'reminder_date',
        'due_reminder_sent', 'custom_reminder_sent',
        'status', 'completed_at',
        'customer_id', 'customer_name', 'customer_phone',
    ];

    protected $casts = [
        'due_date' => 'date',
        'reminder_date' => 'datetime',
        'completed_at' => 'datetime',
        'due_reminder_sent' => 'boolean',
        'custom_reminder_sent' => 'boolean',
    ];

    public function category()
    {
        return $this->belongsTo(TaskCategory::class, 'task_category_id');
    }

    public function assignee()
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }
}
