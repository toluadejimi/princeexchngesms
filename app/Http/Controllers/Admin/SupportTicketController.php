<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SupportTicket;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SupportTicketController extends Controller
{
    public function index(Request $request): View
    {
        $query = SupportTicket::with('user:id,name,email')->latest();

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $tickets = $query->paginate(20)->withQueryString();

        return view('admin.support.index', ['tickets' => $tickets]);
    }

    public function show(SupportTicket $ticket): View
    {
        $ticket->load('user:id,name,email');
        return view('admin.support.show', ['ticket' => $ticket]);
    }

    public function reply(Request $request, SupportTicket $ticket): RedirectResponse
    {
        $validated = $request->validate([
            'admin_reply' => 'required|string|max:5000',
        ]);

        $ticket->update([
            'admin_reply' => $validated['admin_reply'],
            'replied_at' => now(),
        ]);

        return redirect()->route('admin.support.show', $ticket)->with('success', 'Reply sent.');
    }

    public function close(SupportTicket $ticket): RedirectResponse
    {
        $ticket->update([
            'status' => SupportTicket::STATUS_CLOSED,
            'closed_at' => now(),
        ]);

        return redirect()->route('admin.support.show', $ticket)->with('success', 'Ticket closed.');
    }
}
