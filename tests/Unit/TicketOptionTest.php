<?php

use LambdaDigamma\MMEvents\Models\Ticket;
use LambdaDigamma\MMEvents\Models\TicketOption;

it('can be created', function () {

    $ticket = Ticket::factory()->published()->create([]);

    $ticketOption = TicketOption::create([
        'name' => 'Test',
        'price' => 10,
        'ticket_id' => $ticket->id,
    ]);

    expect($ticketOption->name)->toBe('Test');
    $ticketOption = TicketOption::factory()->for($ticket)->create();

    expect($ticketOption)->ticket->id->toBe($ticket->id);

});
