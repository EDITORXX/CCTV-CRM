<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreCustomerRequest;
use App\Mail\CustomerWelcomeMail;
use App\Models\Company;
use App\Models\Customer;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class CustomerController extends Controller
{
    public function index(Request $request)
    {
        $query = Customer::where('company_id', session('current_company_id'));

        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%");
            });
        }

        $customers = $query->latest()->paginate(20)->withQueryString();

        return view('customers.index', compact('customers'));
    }

    public function create()
    {
        return view('customers.create');
    }

    public function store(StoreCustomerRequest $request)
    {
        $customer = Customer::create(array_merge($request->validated(), [
            'company_id' => session('current_company_id'),
            'created_by' => auth()->id(),
        ]));

        if ($customer->email) {
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
                        new CustomerWelcomeMail($customer->name, $customer->email, $password, $companyName)
                    );
                } catch (\Exception $e) {
                    \Log::warning('Failed to send customer welcome email: ' . $e->getMessage());
                }
            } else {
                if (!$existingUser->companies()->where('companies.id', session('current_company_id'))->exists()) {
                    $existingUser->companies()->attach(session('current_company_id'), ['role' => 'customer']);
                }
            }
        }

        return redirect()->route('customers.index')->with('success', 'Customer created successfully.');
    }

    public function show(Customer $customer)
    {
        $customer->load(['sites', 'invoices' => function ($q) {
            $q->latest()->take(10);
        }, 'tickets' => function ($q) {
            $q->latest()->take(10);
        }]);

        return view('customers.show', compact('customer'));
    }

    public function edit(Customer $customer)
    {
        return view('customers.edit', compact('customer'));
    }

    public function update(StoreCustomerRequest $request, Customer $customer)
    {
        $customer->update($request->validated());

        return redirect()->route('customers.show', $customer)->with('success', 'Customer updated successfully.');
    }

    public function destroy(Customer $customer)
    {
        $customer->delete();

        return redirect()->route('customers.index')->with('success', 'Customer deleted successfully.');
    }
}
