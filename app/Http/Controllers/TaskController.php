<?php

namespace App\Http\Controllers;

use App\Mail\TaskAssignedMail;
use App\Models\Company;
use App\Models\Customer;
use App\Models\Task;
use App\Models\TaskCategory;
use App\Models\User;
use App\Services\FcmService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

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
        $customers = Customer::orderBy('name')->get();

        return view('tasks.create', compact('categories', 'technicians', 'userRole', 'customers'));
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
            'customer_id' => 'nullable|exists:customers,id',
            'customer_name' => 'nullable|string|max:255',
            'customer_phone' => 'nullable|string|max:50',
        ];

        if ($userRole !== 'technician') {
            $rules['assigned_to'] = 'required|exists:users,id';
        }

        $validated = $request->validate($rules);

        $customerId = $validated['customer_id'] ?? null;
        $customerName = $validated['customer_name'] ?? null;
        $customerPhone = $validated['customer_phone'] ?? null;

        if ($request->filled('new_customer_name')) {
            $newCustomer = $this->createNewCustomer($request);
            $customerId = $newCustomer->id;
            $customerName = $newCustomer->name;
            $customerPhone = $newCustomer->phone;
        } elseif ($customerId) {
            $customer = Customer::find($customerId);
            if ($customer) {
                $customerName = $customerName ?: $customer->name;
                $customerPhone = $customerPhone ?: $customer->phone;
            }
        }

        $task = Task::create([
            'company_id' => session('current_company_id'),
            'title' => $validated['title'],
            'task_category_id' => $validated['task_category_id'] ?? null,
            'notes' => $validated['notes'] ?? null,
            'assigned_to' => $userRole === 'technician' ? auth()->id() : $validated['assigned_to'],
            'created_by' => auth()->id(),
            'due_date' => $validated['due_date'] ?? null,
            'reminder_date' => $validated['reminder_date'] ?? null,
            'status' => 'pending',
            'customer_id' => $customerId,
            'customer_name' => $customerName,
            'customer_phone' => $customerPhone,
        ]);

        $this->sendAssignmentNotifications($task);

        return redirect()->route('tasks.index')->with('success', 'Task created successfully.');
    }

    public function show(Task $task)
    {
        $task->load(['category', 'assignee', 'creator', 'customer']);
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
        $customers = Customer::orderBy('name')->get();

        return view('tasks.edit', compact('task', 'categories', 'technicians', 'userRole', 'customers'));
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
            'customer_id' => 'nullable|exists:customers,id',
            'customer_name' => 'nullable|string|max:255',
            'customer_phone' => 'nullable|string|max:50',
        ];

        if ($userRole !== 'technician') {
            $rules['assigned_to'] = 'required|exists:users,id';
        }

        $validated = $request->validate($rules);

        $customerId = $validated['customer_id'] ?? null;
        $customerName = $validated['customer_name'] ?? null;
        $customerPhone = $validated['customer_phone'] ?? null;

        if ($request->filled('new_customer_name')) {
            $newCustomer = $this->createNewCustomer($request);
            $customerId = $newCustomer->id;
            $customerName = $newCustomer->name;
            $customerPhone = $newCustomer->phone;
        } elseif ($customerId) {
            $customer = Customer::find($customerId);
            if ($customer) {
                $customerName = $customerName ?: $customer->name;
                $customerPhone = $customerPhone ?: $customer->phone;
            }
        }

        $oldAssignee = $task->assigned_to;

        $data = [
            'title' => $validated['title'],
            'task_category_id' => $validated['task_category_id'] ?? null,
            'notes' => $validated['notes'] ?? null,
            'due_date' => $validated['due_date'] ?? null,
            'reminder_date' => $validated['reminder_date'] ?? null,
            'customer_id' => $customerId,
            'customer_name' => $customerName,
            'customer_phone' => $customerPhone,
        ];

        if ($userRole !== 'technician') {
            $data['assigned_to'] = $validated['assigned_to'];
        }

        if (($data['reminder_date'] ?? null) !== ($task->reminder_date ? $task->reminder_date->format('Y-m-d') : null)) {
            $data['custom_reminder_sent'] = false;
        }
        if (($data['due_date'] ?? null) !== ($task->due_date ? $task->due_date->format('Y-m-d') : null)) {
            $data['due_reminder_sent'] = false;
        }

        $task->update($data);

        if (isset($data['assigned_to']) && $data['assigned_to'] != $oldAssignee) {
            $task->refresh();
            $this->sendAssignmentNotifications($task);
        }

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

    private function createNewCustomer(Request $request): Customer
    {
        $customer = Customer::create([
            'company_id' => session('current_company_id'),
            'name' => $request->input('new_customer_name'),
            'phone' => $request->input('new_customer_phone'),
            'email' => $request->input('new_customer_email'),
            'address' => $request->input('new_customer_address'),
            'created_by' => auth()->id(),
        ]);

        if ($request->boolean('create_login') && $customer->email) {
            $existingUser = User::where('email', $customer->email)->first();

            if (!$existingUser) {
                $password = Str::random(10);
                $user = User::create([
                    'name' => $customer->name,
                    'email' => $customer->email,
                    'password' => $password,
                    'phone' => $customer->phone,
                    'is_active' => true,
                ]);

                $user->companies()->attach(session('current_company_id'), ['role' => 'customer']);

                $companyName = Company::find(session('current_company_id'))->name ?? config('app.name');

                try {
                    Mail::to($customer->email)->send(
                        new \App\Mail\CustomerWelcomeMail($customer->name, $customer->email, $password, $companyName)
                    );
                } catch (\Exception $e) {
                    Log::warning('Failed to send customer welcome email: ' . $e->getMessage());
                }
            } else {
                if (!$existingUser->companies()->where('companies.id', session('current_company_id'))->exists()) {
                    $existingUser->companies()->attach(session('current_company_id'), ['role' => 'customer']);
                }
            }
        }

        return $customer;
    }

    private function sendAssignmentNotifications(Task $task): void
    {
        $task->loadMissing(['assignee', 'creator', 'category']);

        try {
            Mail::to($task->assignee->email)->send(new TaskAssignedMail($task));
        } catch (\Exception $e) {
            Log::warning('Failed to send task assignment email: ' . $e->getMessage());
        }

        try {
            $fcm = app(FcmService::class);
            if ($fcm->isConfigured()) {
                $fcm->sendToUser(
                    $task->assigned_to,
                    'New Task Assigned',
                    'Task: ' . $task->title . ($task->due_date ? ' | Due: ' . $task->due_date->format('d M Y') : ''),
                );
            }
        } catch (\Exception $e) {
            Log::warning('Failed to send task assignment FCM: ' . $e->getMessage());
        }
    }
}
