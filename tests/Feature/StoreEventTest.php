<?php

namespace LambdaDigamma\MMEvents\Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use LambdaDigamma\MMEvents\Models\Event;
use LambdaDigamma\MMEvents\Tests\TestCase;
use Orchestra\Testbench\Factories\UserFactory;

class StoreEventTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function authenticated_user_can_store_an_event()
    {
        $this->assertCount(0, Event::all());

        $this
            ->actingAs(UserFactory::new()->create())
            ->post(route('admin.events.store'), [
                'name' => 'New Name',
                'description' => 'An optional description',
            ])
            ->assertStatus(302);

        $event = Event::withNotPublished()->first();
        $this->assertEquals('New Name', $event->name);
        $this->assertEquals('An optional description', $event->description);
    }

    /** @test */
    public function authenticated_user_can_store_an_event_json()
    {
        $this->assertCount(0, Event::all());

        $this
            ->actingAs(UserFactory::new()->create())
            ->postJson(route('admin.events.store'), [
                'name' => 'New Name',
                'description' => 'An optional description',
            ])
            ->assertStatus(302)
            ->assertJson([
                'id' => 1,
                'name' => 'New Name',
                'description' => 'An optional description',
            ]);

        $event = Event::withNotPublished()->first();
        $this->assertEquals('New Name', $event->name);
        $this->assertEquals('An optional description', $event->description);
    }
}
