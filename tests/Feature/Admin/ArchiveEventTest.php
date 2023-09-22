<?php

use LambdaDigamma\MMEvents\Models\Event;
use Orchestra\Testbench\Factories\UserFactory;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\postJson;

test('event can be archived', function () {
    actingAs(UserFactory::new()->create());
    $event = Event::factory()->published()->create();
    expect($event->archived_at)->toBeNull();

    postJson("/admin/events/{$event->id}/archive")->assertStatus(200);
    expect(Event::query()->withNotPublished()->withArchived()->find($event->id)->archived_at)
        ->not->toBeNull();
});

test('not published event can be archived', function () {
    actingAs(UserFactory::new()->create());
    $event = Event::factory()->create();
    expect($event->archived_at)->toBeNull();

    postJson("/admin/events/{$event->id}/archive")->assertStatus(200);
    expect(Event::query()->withNotPublished()->withArchived()->find($event->id)->archived_at)
        ->not->toBeNull();
});

test('archived event can be unarchived', function () {
    actingAs(UserFactory::new()->create());
    $event = Event::factory()->published()->archived()->create();
    expect($event->archived_at)->not->toBeNull();

    postJson("/admin/events/{$event->id}/unarchive")->assertStatus(200);
    expect(Event::query()->withNotPublished()->withArchived()->find($event->id)->archived_at)->toBeNull();
});

test('archived not published event can be unarchived', function () {
    actingAs(UserFactory::new()->create());
    $event = Event::factory()->archived()->create();
    expect($event->archived_at)->not->toBeNull();

    postJson("/admin/events/{$event->id}/unarchive")->assertStatus(200);
    expect(Event::query()->withNotPublished()->withArchived()->find($event->id)->archived_at)->toBeNull();
});
