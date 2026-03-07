<?php

namespace App\Http\Controllers;

use App\Models\TaskCategory;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class TaskCategoryController extends Controller
{
    public function index()
    {
        $categories = TaskCategory::withCount('tasks')->orderBy('name')->get();

        return view('task-categories.index', compact('categories'));
    }

    public function store(Request $request)
    {
        $companyId = session('current_company_id');

        $validated = $request->validate([
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('task_categories')->where('company_id', $companyId),
            ],
        ]);

        TaskCategory::create([
            'company_id' => $companyId,
            'name' => $validated['name'],
            'is_default' => false,
            'created_by' => auth()->id(),
        ]);

        return redirect()->route('task-categories.index')->with('success', 'Task category added.');
    }

    public function destroy(TaskCategory $taskCategory)
    {
        if ($taskCategory->is_default) {
            return back()->with('error', 'Default categories cannot be deleted.');
        }

        $taskCategory->delete();

        return redirect()->route('task-categories.index')->with('success', 'Task category deleted.');
    }
}
