<?php

namespace App\Http\Controllers;

use App\Models\Ticket;
use App\Models\TicketResponse;
use App\Models\TicketHistory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class TicketResponseController extends Controller
{
    public function store(Request $request, Ticket $ticket)
    {
        $this->authorize('respond', $ticket);

        $request->validate([
            'message' => 'required|string',
            'attachment' => 'nullable|file|max:5120'
        ]);

        $path = null;
        if ($request->hasFile('attachment')) {
            $path = $request->file('attachment')->store('ticket_attachments', 'public');
        }

        $response = TicketResponse::create([
            'ticket_id' => $ticket->id,
            'user_id' => Auth::id(),
            'message' => $request->message,
            'attachment' => $path,
            'is_internal' => $request->boolean('is_internal'),
        ]);

        TicketHistory::create([
            'ticket_id' => $ticket->id,
            'user_id' => Auth::id(),
            'action' => 'response_added',
            'notes' => substr($request->message, 0, 200),
            'meta' => json_encode(['response_id' => $response->id, 'attachment' => $path])
        ]);

        return back()->with('success', 'Balasan berhasil dikirim.');
    }
}
