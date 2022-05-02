<?php

namespace LambdaDigamma\MMEvents\Tests\Feature\Admin;

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
        $this->assertNull($event->extras->get('collection'));

        $response = $this->actingAs($admin)->put(route('admin.events.update', $event), [
            'name' => 'New Name',
            'collection' => 'Some collection',
        ]);

        tap(Event::withNotPublished()->first(), function ($updatedEvent) use ($response) {
            $this->assertEquals('New Name', $updatedEvent->name);
            $this->assertEquals('Some collection', $updatedEvent->extras?->get('collection'));
            $response->assertRedirect();
        });
    }

    /** @test */
    public function authenticated_users_can_update_an_event_remove_collection()
    {
        $this->assertCount(0, Event::all());

        $admin = UserFactory::new()->create();
        $event = Event::factory()->create([
            'name' => 'Some name',
            'extras' => ['collection' => 'Festival']
        ]);

        $this->assertEquals('Some name', $event->name);
        $this->assertEquals('Festival', $event->extras->get('collection'));

        $response = $this->actingAs($admin)->put(route('admin.events.update', $event), [
            'name' => 'New Name',
            'collection' => null,
        ]);

        tap(Event::withNotPublished()->first(), function ($updatedEvent) use ($response) {
            $this->assertEquals('New Name', $updatedEvent->name);
            $this->assertNull($updatedEvent->extras?->get('collection'));
            $response->assertRedirect();
        });
    }
}
