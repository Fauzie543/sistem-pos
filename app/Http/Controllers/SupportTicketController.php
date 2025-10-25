<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\SupportTicket;
use App\Models\SupportTicketReply;

class SupportTicketController extends Controller
{
    public function store(Request $request)
    {
        $validated = $request->validate([
            'subject' => 'required|string|max:255',
            'message' => 'required|string',
        ]);

        $ticket = SupportTicket::create([
            'company_id' => auth()->user()->company_id,
            'user_id' => auth()->id(),
            'subject' => $validated['subject'],
            'message' => $validated['message'],
            'status' => 'open',
        ]);

        // Balasan pertama disimpan juga (supaya tampil di chat)
        SupportTicketReply::create([
            'support_ticket_id' => $ticket->id,
            'user_id' => auth()->id(),
            'message' => $validated['message'],
        ]);

        return response()->json([
            'success' => true,
            'ticket_id' => $ticket->id,
        ]);
    }
    public function reply(Request $request, SupportTicket $ticket)
    {
        $validated = $request->validate([
            'message' => 'required|string',
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

        return response()->json(['success' => 'Balasan terkirim']);
    }

    // Ambil semua percakapan tiket
    public function replies(SupportTicket $ticket)
    {
        $replies = $ticket->replies()
            ->with('user:id,name')
            ->orderBy('created_at')
            ->get();

        return response()->json([
            'ticket_status' => $ticket->status,
            'replies' => $replies,
        ]);
    }
}