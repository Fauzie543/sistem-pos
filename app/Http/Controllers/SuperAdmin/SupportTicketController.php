<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\SupportTicket;
use App\Models\SupportTicketReply;
use Illuminate\Http\Request;

class SupportTicketController extends Controller
{
    public function index()
    {
        $tickets = SupportTicket::with(['user', 'company'])
            ->latest()
            ->paginate(10);

        return view('superadmin.support.index', compact('tickets'));
    }

    // Detail tiket + balasan
    public function show(SupportTicket $ticket)
    {
        $ticket->load(['user', 'company', 'replies.user']);
        return view('superadmin.support.show', compact('ticket'));
    }

    // Balas tiket
    public function reply(Request $request, SupportTicket $ticket)
    {
        $validated = $request->validate([
            'message' => 'required|string'
        ]);

        SupportTicketReply::create([
            'support_ticket_id' => $ticket->id,
            'user_id' => auth()->id(),
            'message' => $validated['message'],
        ]);
        $ticket->update([
            'status' => 'in_progress',
            'last_reply_by' => auth()->id(),
        ]);


        return redirect()->back()->with('success', 'Balasan berhasil dikirim.');
    }

    // Tandai tiket selesai
    public function resolve(SupportTicket $ticket)
    {
        $ticket->update(['status' => 'resolved']);
        return redirect()->back()->with('success', 'Tiket ditandai selesai.');
    }
}