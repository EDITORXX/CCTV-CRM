<?php

namespace App\Http\Controllers;

use App\Models\Task;
use App\Models\TaskCategory;
use App\Models\User;
use Illuminate\Http\Request;

class TaskController extends Controller
{
    private function getUserRole()
    {
        $pivot = auth()->user()->companies()
            ->where('companies.id', session('current_company_id'))
            ->first();
        return $pivot ? $pivot->pivot->role : null;
    }

    public function index()
    {
        $userRole = $this->getUserRole();

        $query = Task::with(['category', 'assignee', 'creator']);

        if ($userRole === 'technician') {
            $query->where(function ($q) {
                $q->where('assigned_to', auth()->id())
                  ->orWhere('created_by', auth()->id());
            });
        }

        $tasks = $query->orderBy('created_at', 'desc')->get();
        $categories = TaskCategory::orderBy('name')->get();
        $technicians = User::whereHas('companies', function ($q) {
            $q->where('companies.id', session('current_company_id'))
              ->where('company_user.role', 'technician');
        })->get();

        return view('tasks.index', compact('tasks', 'categories', 'technicians', 'userRole'));
    }

    public function create()
    {
        $userRole = $this->getUserRole();
        $categories = TaskCategory::orderBy('name')->get();
        $technicians = User::whereHas('companies', function ($q) {
            $q->where('companies.id', session('current_company_id'))
              ->where('company_user.role', 'technician');
        })->get();

        return view('tasks.create', compact('categories', 'technicians', 'userRole'));
    }

    public function store(Request $request)
    {
        $userRole = $this->getUserRole();

        $rules = [
            'title' => 'required|string|max:255',
            'task_category_id' => 'nullable|exists:task_categories,id',
            'notes' => 'nullable|string',
            'due_date' => 'nullable|date',
            'reminder_date' => 'nullable|date',
        ];

        if ($userRole !== 'technician') {
            $rules['assigned_to'] = 'required|exists:users,id';
        }

        $validated = $request->validate($rules);

        Task::create([
            'company_id' => session('current_company_id'),
            'title' => $validated['title'],
            'task_category_id' => $validated['task_category_id'] ?? null,
            'notes' => $validated['notes'] ?? null,
            'assigned_to' => $userRole === 'technician' ? auth()->id() : $validated['assigned_to'],
            'created_by' => auth()->id(),
            'due_date' => $validated['due_date'] ?? null,
            'reminder_date' => $validated['reminder_date'] ?? null,
            'status' => 'pending',
        ]);

        return redirect()->route('tasks.index')->with('success', 'Task created successfully.');
    }

    public function show(Task $task)
    {
        $task->load(['category', 'assignee', 'creator']);
        $userRole = $this->getUserRole();

        return view('tasks.show', compact('task', 'userRole'));
    }

    public function edit(Task $task)
    {
        $userRole = $this->getUserRole();
        $categories = TaskCategory::orderBy('name')->get();
        $technicians = User::whereHas('companies', function ($q) {
            $q->where('companies.id', session('current_company_id'))
              ->where('company_user.role', 'technician');
        })->get();

        return view('tasks.edit', compact('task', 'categories', 'technicians', 'userRole'));
    }

    public function update(Request $request, Task $task)
    {
        $userRole = $this->getUserRole();

        $rules = [
            'title' => 'required|string|max:255',
            'task_category_id' => 'nullable|exists:task_categories,id',
            'notes' => 'nullable|string',
            'due_date' => 'nullable|date',
            'reminder_date' => 'nullable|date',
        ];

        if ($userRole !== 'technician') {
            $rules['assigned_to'] = 'required|exists:users,id';
        }

        $validated = $request->validate($rules);

        $data = [
            'title' => $validated['title'],
            'task_category_id' => $validated['task_category_id'] ?? null,
            'notes' => $validated['notes'] ?? null,
            'due_date' => $validated['due_date'] ?? null,
            'reminder_date' => $validated['reminder_date'] ?? null,
        ];

        if ($userRole !== 'technician') {
            $data['assigned_to'] = $validated['assigned_to'];
        }

        // Reset reminder flags if dates changed
        if (($data['reminder_date'] ?? null) !== ($task->reminder_date ? $task->reminder_date->format('Y-m-d') : null)) {
            $data['custom_reminder_sent'] = false;
        }
        if (($data['due_date'] ?? null) !== ($task->due_date ? $task->due_date->format('Y-m-d') : null)) {
            $data['due_reminder_sent'] = false;
        }

        $task->update($data);

        return redirect()->route('tasks.show', $task)->with('success', 'Task updated successfully.');
    }

    public function destroy(Task $task)
    {
        $task->delete();
        return redirect()->route('tasks.index')->with('success', 'Task deleted.');
    }

    public function markComplete(Task $task)
    {
        $task->update([
            'status' => 'completed',
            'completed_at' => now(),
        ]);

        return back()->with('success', 'Task marked as completed.');
    }

    public function markInProgress(Task $task)
    {
        $task->update([
            'status' => 'in_progress',
            'completed_at' => null,
        ]);

        return back()->with('success', 'Task marked as in progress.');
    }
}
