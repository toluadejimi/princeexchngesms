<?php

namespace App\Http\Controllers;

use App\Models\SupportTicket;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SupportController extends Controller
{
    public function index(Request $request): View
    {
        $tickets = SupportTicket::where('user_id', $request->user()->id)
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return view('support.index', ['tickets' => $tickets]);
    }

    public function create(): View
    {
        return view('support.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'subject' => 'required|string|max:255',
            'message' => 'required|string|max:5000',
        ]);

        SupportTicket::create([
            'user_id' => $request->user()->id,
            'subject' => $validated['subject'],
            'message' => $validated['message'],
            'status' => SupportTicket::STATUS_OPEN,
        ]);

        return redirect()->route('support.index')->with('message', 'Your complaint has been submitted. We will respond soon.');
    }

    public function show(Request $request, int $id): View|RedirectResponse
    {
        $ticket = SupportTicket::where('user_id', $request->user()->id)->findOrFail($id);
        return view('support.show', ['ticket' => $ticket]);
    }
}
