<?php

namespace LambdaDigamma\MMEvents\Tests\Unit;

use Carbon\Carbon;
use Exception;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\RefreshDatabase;
use LambdaDigamma\MMEvents\Exceptions\InvalidLink;
use LambdaDigamma\MMEvents\Models\Event;
use LambdaDigamma\MMEvents\Tests\TestCase;

class EventTest extends TestCase
{
    use DatabaseMigrations;
    use RefreshDatabase;

    public function testScopeActive()
    {
        $activeEventStartEnd = Event::factory()
            ->published()
            ->activeStartEnd()
            ->create();

        $activeEventInDeadline = Event::factory()
            ->published()
            ->activeStart()
            ->create();

        $upcomingStartEvent = Event::factory()
            ->published()
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

        $events = Event::query()->pluck('id');

        $this->assertTrue($events->contains($publishedEvents->first()->id));
        $this->assertFalse($events->contains($notPublishedEvents->first()->id));
    }

    public function testScopeToday()
    {
        $eventYesterday = Event::factory()
            ->published()
            ->create([
                'start_date' => Carbon::now()->addDays(-1),
            ]);

        $eventToday = Event::factory()
            ->published()
            ->create([
                'start_date' => Carbon::now(),
            ]);

        $eventTomorrow = Event::factory()
            ->published()
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
            ->published()
            ->create([
                'start_date' => Carbon::now()->addDays(-1),
            ]);

        $eventAlreadyActive = Event::factory()
            ->published()
            ->create([
                'start_date' => Carbon::now()->addMinutes(-60),
            ]);

        $eventUpcomingToday = Event::factory()
            ->published()
            ->upcomingToday()
            ->create();

        $eventTomorrow = Event::factory()
            ->published()
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
            ->published()
            ->create([
                'start_date' => Carbon::now()->addDays(-1),
            ]);

        $eventAlreadyActive = Event::factory()
            ->published()
            ->create([
                'start_date' => Carbon::now()->addMinutes(-60),
            ]);

        $eventUpcomingToday = Event::factory()
            ->published()
            ->upcomingToday()
            ->create();

        $eventTomorrow = Event::factory()
            ->published()
            ->create([
                'start_date' => Carbon::now()->addDay(),
            ]);

        $scopedEventsDatabase = Event::nextDays()->pluck('id');

        $this->assertFalse($scopedEventsDatabase->contains($eventPreviousDay->id));
        $this->assertFalse($scopedEventsDatabase->contains($eventAlreadyActive->id));
        $this->assertFalse($scopedEventsDatabase->contains($eventUpcomingToday->id));
        $this->assertTrue($scopedEventsDatabase->contains($eventTomorrow->id));
    }

    public function scopeSortChronologically()
    {
        $eventUpcomingToday = Event::factory()
            ->published()
            ->upcomingToday()
            ->create();

        $eventTomorrow = Event::factory()
            ->published()
            ->create([
                'start_date' => Carbon::now()->addDay(),
            ]);

        $eventPreviousDay = Event::factory()
            ->published()
            ->create([
                'start_date' => Carbon::now()->addDays(-1),
            ]);

        $eventAlreadyActive = Event::factory()
            ->published()
            ->create([
                'start_date' => Carbon::now()->addMinutes(-60),
            ]);

        $eventNoDate = Event::factory()
            ->published()
            ->create([
                'start_date' => null,
            ]);

        $scopedEventsDatabase = Event::query()
            ->withNotPublished()
            ->chronological()
            ->get()
            ->pluck('id');

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
            ->published()
            ->activeStart()
            ->create();

        $this->assertStringStartsWith('data:text/calendar;charset=utf8;base64,', $event->ics());
    }

    public function testIcsExportFailsNoDates()
    {
        $event = Event::factory()
            ->published()
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

    public function testSettingMixedAttendance()
    {
        $event = Event::factory()
            ->upcomingToday()
            ->published()
            ->create([
                'extras' => [],
            ]);
        $event->attendance_mode = 'mixed';
        $this->assertEquals('mixed', $event->attendance_mode);
    }

    public function testSettingOnlineAttendance()
    {
        $event = Event::factory()
            ->upcomingToday()
            ->published()
            ->create();
        $event->attendance_mode = 'online';
        $this->assertEquals('online', $event->attendance_mode);
    }

    public function testSettingOfflineAttendance()
    {
        $event = Event::factory()
            ->upcomingToday()
            ->published()
            ->create();
        $event->attendance_mode = 'offline';
        $this->assertEquals('offline', $event->attendance_mode);
    }

    public function testSettingUnknownAttendanceFails()
    {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Attendance mode unknown. Only offline, online and mixed is allowed.');
        $event = Event::factory()
            ->upcomingToday()
            ->published()
            ->create();
        $event->attendance_mode = 'something-else';
    }

    public function testCollectionScopes()
    {
        $eventsWithCollection = Event::factory()
            ->times(3)
            ->published()
            ->create([
                'extras' => [
                    'collection' => 'collection-1',
                ],
            ]);

        $eventsWithoutCollection = Event::factory()
            ->times(4)
            ->published()
            ->create([
                'extras' => [],
            ]);

        $this->assertEquals(7, Event::query()->count());
        $this->assertEquals(4, Event::query()->noCollection()->count());
        $this->assertEquals(3, Event::query()->collection('collection-1')->count());
        $this->assertEquals(0, Event::query()->collection('collection-2')->count());

    }

    public function testFilterScope()
    {
        $eventsWithCollection = Event::factory()
            ->times(3)
            ->published()
            ->create([
                'extras' => [
                    'collection' => 'collection-1',
                ],
            ]);

        $eventsWithCollection2 = Event::factory()
            ->times(2)
            ->published()
            ->create([
                'extras' => [
                    'collection' => 'collection-2',
                ],
            ]);

        $eventsWithoutCollection = Event::factory()
            ->times(4)
            ->published()
            ->create([
                'extras' => [],
            ]);

        $countCollection2 = Event::query()
            ->filter(['collection' => 'collection-2'])
            ->count();

        $countCollection1 = Event::query()
            ->filter(['collection' => 'collection-1'])
            ->count();

        $this->assertEquals(2, $countCollection2);
        $this->assertEquals(3, $countCollection1);

    }

    public function testDurationNoStartDate()
    {
        $event = Event::factory()->create([
            'start_date' => null,
            'end_date' => null,
        ]);
        $this->assertNull($event->duration);
    }

    public function testDurationOnlyStartDate()
    {
        $event = Event::factory()->create([
            'start_date' => Carbon::now()->addDay(),
            'end_date' => null,
        ]);
        $this->assertEquals(30, $event->duration);
    }

    public function testDuration55MinutesWithStartAndEndDate()
    {
        $event = Event::factory()->create([
            'start_date' => Carbon::now()->addMinutes(45),
            'end_date' => Carbon::now()->addMinutes(100),
        ]);
        $this->assertEquals(55, $event->duration);
    }

    public function testDateCasts() {

        $event = Event::factory()->create([
            'start_date' => Carbon::now()->addMinutes(45),
        ]);

        $this->assertEquals($event->fresh()->start_date::class, "Illuminate\Support\Carbon");

    }
}
