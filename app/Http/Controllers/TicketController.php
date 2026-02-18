<?php
namespace App\Http\Controllers;

use App\Models\Ticket;
use App\Models\TicketAssignment;
use App\Models\TicketUpdate;
use App\Models\Customer;
use App\Models\Site;
use App\Models\User;
use Illuminate\Http\Request;

class TicketController extends Controller
{
    public function index()
    {
        $userRole = auth()->user()->companies()->where('companies.id', session('current_company_id'))->first()->pivot->role ?? 'manager';

        $query = Ticket::with(['customer', 'site', 'assignments.technician']);

        if ($userRole === 'technician') {
            $query->whereHas('assignments', function ($q) {
                $q->where('technician_id', auth()->id());
            });
        }

        $tickets = $query->orderBy('created_at', 'desc')->paginate(20);
        return view('tickets.index', compact('tickets'));
    }

    public function create()
    {
        $customers = Customer::orderBy('name')->get();
        $lastTicket = Ticket::where('company_id', session('current_company_id'))->orderBy('id', 'desc')->first();
        $nextNumber = 'TKT-' . str_pad(
            ($lastTicket ? intval(preg_replace('/\D/', '', $lastTicket->ticket_number)) + 1 : 1),
            5, '0', STR_PAD_LEFT
        );
        return view('tickets.create', compact('customers', 'nextNumber'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'customer_id' => 'required|exists:customers,id',
            'site_id' => 'nullable|exists:sites,id',
            'ticket_number' => 'required|string',
            'complaint_type' => 'nullable|string',
            'description' => 'nullable|string',
            'priority' => 'required|in:low,medium,high',
        ]);

        $ticket = Ticket::create([
            'company_id' => session('current_company_id'),
            'ticket_number' => $request->ticket_number,
            'customer_id' => $request->customer_id,
            'site_id' => $request->site_id,
            'complaint_type' => $request->complaint_type,
            'description' => $request->description,
            'priority' => $request->priority,
            'status' => 'open',
            'created_by' => auth()->id(),
        ]);

        return redirect()->route('tickets.show', $ticket)->with('success', 'Ticket created.');
    }

    public function show(Ticket $ticket)
    {
        $ticket->load(['customer', 'site', 'assignments.technician', 'updates.user', 'creator']);
        $technicians = User::whereHas('companies', function ($q) {
            $q->where('companies.id', session('current_company_id'))
              ->where('company_user.role', 'technician');
        })->get();
        return view('tickets.show', compact('ticket', 'technicians'));
    }

    public function edit(Ticket $ticket)
    {
        $customers = Customer::orderBy('name')->get();
        return view('tickets.edit', compact('ticket', 'customers'));
    }

    public function update(Request $request, Ticket $ticket)
    {
        $request->validate([
            'complaint_type' => 'nullable|string',
            'description' => 'nullable|string',
            'priority' => 'required|in:low,medium,high',
            'status' => 'required|in:open,in_progress,resolved,closed',
        ]);
        $ticket->update($request->only(['complaint_type', 'description', 'priority', 'status']));
        return redirect()->route('tickets.show', $ticket)->with('success', 'Ticket updated.');
    }

    public function destroy(Ticket $ticket)
    {
        $ticket->assignments()->delete();
        $ticket->updates()->delete();
        $ticket->delete();
        return redirect()->route('tickets.index')->with('success', 'Ticket deleted.');
    }

    public function assign(Request $request, Ticket $ticket)
    {
        $request->validate(['technician_id' => 'required|exists:users,id']);

        TicketAssignment::create([
            'ticket_id' => $ticket->id,
            'technician_id' => $request->technician_id,
            'assigned_by' => auth()->id(),
            'assigned_at' => now(),
            'notes' => $request->notes,
        ]);

        if ($ticket->status === 'open') {
            $ticket->update(['status' => 'in_progress']);
        }

        return back()->with('success', 'Technician assigned.');
    }

    public function updateStatus(Request $request, Ticket $ticket)
    {
        $request->validate([
            'status' => 'required|in:open,in_progress,resolved,closed',
            'notes' => 'nullable|string',
            'work_done' => 'nullable|string',
        ]);

        $ticket->update(['status' => $request->status]);

        TicketUpdate::create([
            'ticket_id' => $ticket->id,
            'user_id' => auth()->id(),
            'notes' => $request->notes,
            'status_change' => $request->status,
            'work_done' => $request->work_done,
        ]);

        return back()->with('success', 'Ticket status updated.');
    }
}
