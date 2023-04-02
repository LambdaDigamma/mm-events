<?php

namespace LambdaDigamma\MMEvents\Models;

use Illuminate\Database\Eloquent\Concerns\HasTimestamps;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\Pivot;


class TicketAssignment extends Pivot
{
    use HasTimestamps;

    protected $table = "mm_ticket_assignments";
    protected $guarded = ['*', 'id'];

    public $incrementing = true;

    public function ticket()
    {
        return $this->belongsTo(Ticket::class);
    }

    public function event()
    {
        return $this->belongsTo(Event::class);
    }
}
