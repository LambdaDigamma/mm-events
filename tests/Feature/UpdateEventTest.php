<?php

namespace LambdaDigamma\MMEvents\Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use LambdaDigamma\MMEvents\Models\Event;
use LambdaDigamma\MMEvents\Tests\TestCase;
use Orchestra\Testbench\Factories\UserFactory;

class UpdateEventTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function authenticated_users_can_update_an_event()
    {
        $this->assertCount(0, Event::all());

        $admin = UserFactory::new()->create();
        $event = Event::factory()->create(['name' => 'Initial name']);

        $this->assertEquals($event->name, 'Initial name');

        $response = $this->actingAs($admin)->put(route('admin.events.update', $event), [
            'name' => 'New Name',
        ]);

        tap(Event::first(), function ($updatedEvent) use ($response) {
            $this->assertEquals('New Name', $updatedEvent->name);
            $response->assertRedirect();
        });
    }
}
