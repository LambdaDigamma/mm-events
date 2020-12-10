<?php

namespace LambdaDigamma\MMEvents\Tests;

use Carbon\Carbon;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\RefreshDatabase;
use LambdaDigamma\MMEvents\Exceptions\InvalidLink;
use LambdaDigamma\MMEvents\Models\Event;

class EventTest extends TestCase
{
    use DatabaseMigrations;
    use RefreshDatabase;
    
    public function testScopeActive()
    {
        $activeEventStartEnd = Event::factory()
            ->activeStartEnd()
            ->create();

        $activeEventInDeadline = Event::factory()
            ->activeStart()
            ->create();

        $upcomingStartEvent = Event::factory()
            ->upcomingStart()
            ->create();

        $activeEventsDatabase = Event::active()->pluck('id');

        $this->assertTrue($activeEventsDatabase->contains($activeEventStartEnd->id));
        $this->assertTrue($activeEventsDatabase->contains($activeEventInDeadline->id));
        $this->assertFalse($activeEventsDatabase->contains($upcomingStartEvent->id));
    }

    public function testScopePublished()
    {
        $publishedEvents = Event::factory()
            ->count(3)
            ->published()
            ->create();

        $notPublishedEvents = Event::factory()
            ->count(3)
            ->notPublished()
            ->create();

        $events = Event::published()->pluck('id');

        $this->assertTrue($events->contains($publishedEvents->first()->id));
        $this->assertFalse($events->contains($notPublishedEvents->first()->id));
    }

    public function testScopeToday()
    {
        $eventYesterday = Event::factory()
            ->create([
                'start_date' => Carbon::now()->addDays(-1),
            ]);

        $eventToday = Event::factory()
            ->create([
                'start_date' => Carbon::now(),
            ]);

        $eventTomorrow = Event::factory()
            ->create([
                'start_date' => Carbon::now()->addDays(1),
            ]);

        $scopedEventsDatabase = Event::today()->pluck('id');

        $this->assertFalse($scopedEventsDatabase->contains($eventYesterday->id));
        $this->assertTrue($scopedEventsDatabase->contains($eventToday->id));
        $this->assertFalse($scopedEventsDatabase->contains($eventTomorrow->id));
    }

    public function testScopeUpcomingToday()
    {
        $eventPreviousDay = Event::factory()
            ->create([
                'start_date' => Carbon::now()->addDays(-1),
            ]);

        $eventAlreadyActive = Event::factory()
            ->create([
                'start_date' => Carbon::now()->addMinutes(-60),
            ]);

        $eventUpcomingToday = Event::factory()
            ->upcomingToday()
            ->create();

        $eventTomorrow = Event::factory()
            ->create([
                'start_date' => Carbon::now()->addDay(),
            ]);

        $scopedEventsDatabase = Event::upcomingToday()->pluck('id');
        
        $this->assertFalse($scopedEventsDatabase->contains($eventPreviousDay->id));
        $this->assertFalse($scopedEventsDatabase->contains($eventAlreadyActive->id));
        $this->assertTrue($scopedEventsDatabase->contains($eventUpcomingToday->id));
        $this->assertFalse($scopedEventsDatabase->contains($eventTomorrow->id));
    }

    public function testScopeNextDays()
    {
        $eventPreviousDay = Event::factory()
            ->create([
                'start_date' => Carbon::now()->addDays(-1),
            ]);

        $eventAlreadyActive = Event::factory()
            ->create([
                'start_date' => Carbon::now()->addMinutes(-60),
            ]);

        $eventUpcomingToday = Event::factory()
            ->upcomingToday()
            ->create();

        $eventTomorrow = Event::factory()
            ->create([
                'start_date' => Carbon::now()->addDay(),
            ]);

        $scopedEventsDatabase = Event::nextDays()->pluck('id');
        
        $this->assertFalse($scopedEventsDatabase->contains($eventPreviousDay->id));
        $this->assertFalse($scopedEventsDatabase->contains($eventAlreadyActive->id));
        $this->assertFalse($scopedEventsDatabase->contains($eventUpcomingToday->id));
        $this->assertTrue($scopedEventsDatabase->contains($eventTomorrow->id));
    }

    public function scopeSortChoronologically()
    {
        $eventUpcomingToday = Event::factory()
            ->upcomingToday()
            ->create();

        $eventTomorrow = Event::factory()
            ->create([
                'start_date' => Carbon::now()->addDay(),
            ]);

        $eventPreviousDay = Event::factory()
            ->create([
                'start_date' => Carbon::now()->addDays(-1),
            ]);

        $eventAlreadyActive = Event::factory()
            ->create([
                'start_date' => Carbon::now()->addMinutes(-60),
            ]);

        $eventNoDate = Event::factory()
            ->create([
                'start_date' => null,
            ]);

        $scopedEventsDatabase = Event::query()->chronological()->get()->pluck('id');

        $this->assertEquals($scopedEventsDatabase->toArray(), collect([
            $eventPreviousDay->id,
            $eventAlreadyActive->id,
            $eventUpcomingToday->id,
            $eventTomorrow->id,
            $eventNoDate->id,
        ])->toArray());
    }

    public function testIcsExportActiveStart()
    {
        $event = Event::factory()
            ->activeStart()
            ->create();

        $this->assertStringStartsWith('data:text/calendar;charset=utf8;base64,', $event->ics());
    }

    public function testIcsExportFailsNoDates()
    {
        $event = Event::factory()
            ->create([
                'start_date' => null,
                'end_date' => null,
            ]);

        $this->expectException(InvalidLink::class);

        $this->assertStringStartsWith('data:text/calendar;charset=utf8;base64,', $event->ics());
    }

    public function testPublish()
    {
        $event = Event::factory()
            ->upcomingToday()
            ->notPublished()
            ->create();

        $this->assertNull($event->published_at);
        $event->publish();
        $this->assertNotNull($event->published_at);
    }

    public function testUnpublish()
    {
        $event = Event::factory()
            ->upcomingToday()
            ->published()
            ->create();

        $this->assertNotNull($event->published_at);
        $event->unpublish();
        $this->assertNull($event->published_at);
    }
}
