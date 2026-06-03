<h2>Hasil Pencarian Ticket</h2>

@if ($tickets->count() == 0)
    <p>Tidak ada ticket ditemukan.</p>
@else
    @foreach ($tickets as $ticket)
        <div>
            <strong>{{ $ticket->ticket_number }}</strong> - {{ $ticket->title }}
        </div>
    @endforeach

    {{ $tickets->links() }}
@endif
