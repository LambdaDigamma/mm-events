<?php

namespace LambdaDigamma\MMEvents\Models;

use Carbon\Carbon;
use Exception;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\AsCollection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use LambdaDigamma\MMEvents\Database\Factories\EventFactory;
use LambdaDigamma\MMEvents\Exceptions\InvalidLink;
use LambdaDigamma\MMEvents\Link;
use LaravelArchivable\Archivable;
use LaravelPublishable\Publishable;
use Spatie\Translatable\HasTranslations;

class Event extends Model
{
    use SoftDeletes;
    use Archivable;
    use Publishable;
    use HasFactory;
    use HasTranslations;

    protected $table = "mm_events";

    protected $fillable = [
        'name', 'start_date', 'end_date',
        'description', 'url', 'image_path',
        'category', 'organisation_id', 'place_id',
        'extras', 'published_at', 'scheduled_at',
    ];

    protected $casts = ['extras' => AsCollection::class,];
    protected $appends = ['attendance_mode'];
    public $translatable = ['name', 'description', 'category'];
    public $dates = ['start_date', 'end_date', 'scheduled_at', 'cancelled_at'];

    public const ATTENDANCE_MIXED = "mixed";
    public const ATTENDANCE_OFFLINE = "offline";
    public const ATTENDANCE_ONLINE = "online";

    public function toArray()
    {
        $attributes = parent::toArray();

        foreach ($this->getTranslatableAttributes() as $name) {
            $attributes[$name] = $this->getTranslation($name, app()->getLocale());
        }

        return $attributes;
    }

    protected static function newFactory(): EventFactory
    {
        return EventFactory::new();
    }

    /**
     * Returns a data string ics format of the event.
     * This can be used to download an ics file.
     *
     * @return string
     */
    public function ics()
    {
        $start_date = $this->start_date;
        $end_date = $this->end_date;

        if ($start_date == null) {
            throw InvalidLink::noStartDateProvided();
        } elseif ($end_date == null) {
            $end_date = $start_date->addMinutes(config('mm-events.event_default_duration'));
        }

        $link = Link::create($this->name, $start_date, $end_date)
            ->description($this->description);

        return $link->ics();
    }

    public function jsonLd()
    {
        $attendanceModeSchema = "https://schema.org/OfflineEventAttendanceMode";

        if ($this->attendance_mode == self::ATTENDANCE_ONLINE) {
            $attendanceModeSchema = "https://schema.org/OnlineEventAttendanceMode";
        } elseif ($this->attendance_mode == self::ATTENDANCE_MIXED) {
            $attendanceModeSchema = "https://schema.org/MixedEventAttendanceMode";
        }

        return [
            '@type' => 'Event',
            'name' => $this->name,
            'startDate' => $this->start_date ? $this->start_date->tz('UTC')->toAtomString() : null,
            'endDate' => $this->end_date ? $this->end_date->tz('UTC')->toAtomString() : null,
            'eventStatus' => $this->cancelled_at == null ? 'https://schema.org/EventScheduled' : 'https://schema.org/EventCancelled',
            'eventAttendanceMode' => $attendanceModeSchema,
            'description' => $this->description
        ];
    }

    public function getAttendanceModeAttribute()
    {
        return $this->extras ? $this->extras->get('attendance_mode', null) : null;
    }

    public function setAttendanceModeAttribute($value): void
    {
        if (! collect([self::ATTENDANCE_MIXED, self::ATTENDANCE_OFFLINE, self::ATTENDANCE_ONLINE])->contains($value)) {
            throw new Exception('Attendance mode unknown. Only offline, online and mixed is allowed.');
        }
        
        if ($this->extras) {
            $this->extras->put('attendance_mode', $value);
        } else {
            $this->extras = collect(['attendance_mode' => $value]);
        }
    }

    public function scopeActive($query)
    {
        $now = Carbon::now()->toDateTimeString();
        $deadline = Carbon::now()->addMinutes(config('mm-events.event_active_duration') * -1)->toDateTimeString();

        return $query
            ->where(function ($query) use ($now, $deadline) {
                $query
                    ->where('end_date', '=', null)
                    ->where('start_date', '!=', null)
                    ->where('start_date', '<=', $now)
                    ->where('start_date', '>=', $deadline);
            })
            ->orWhere(function ($query) use ($now) {
                $query
                    ->where('end_date', '>=', $now)
                    ->where('start_date', '<=', $now);
            });
    }

    public function scopeToday(Builder $query): Builder
    {
        $now = Carbon::now()->toDateTimeString();
        $today = Carbon::today()->toDateString();

        return $query
            ->whereDate('start_date', '=', $today)
            ->orWhereDate('end_date', '=', $today)
            ->orWhere(function (Builder $query) use ($now) {
                $query
                    ->where('end_date', '>=', $now)
                    ->where('start_date', '<=', $now);
            });
    }

    /**
     * Returns all upcoming events that have a start date
     * which is greater than the current date.
     *
     * @return Builder
     */
    public function scopeUpcomingToday(Builder $query)
    {
        return $query
            ->whereDate('start_date', '=', Carbon::today()->toDateString())
            ->where('start_date', '>', Carbon::now());
    }

    /**
     * Returns all events that take place in the future
     * after tomorrow or have no start date set.
     *
     * @return Builder
     */
    public function scopeNextDays(Builder $query)
    {
        $today = Carbon::today()->toDateString();

        return $query
            ->whereDate('start_date', '>', $today)
            ->orWhere('start_date', '=', null);
    }

    /**
     * Orders the query with a chronological start date.
     * Events without a start date go last.
     *
     * @return Builder
     */
    public function scopeChronological(Builder $query)
    {
        return $query
            ->orderByRaw('-start_date DESC');
    }

    /**
     * Returns all events that have a start date greater than today.
     *
     * @return Builder
     */
    public function scopeFuture(Builder $query)
    {
        return $query
            ->whereDate('start_date', '>=', now()->toDateString())
            ->orWhere('start_date', '=', null);
    }

    public function scopePast(Builder $query): Builder
    {
        return $query
            ->where('start_date', '<=', now()->toDateString());
    }

    public function scopeDrafts(Builder $query): Builder
    {
        return $query->where('published_at', '=', null);
    }

    public function scopeFilter($query, array $filters): void
    {
        $locale = app()->getLocale();
        $fallback = config('app.fallback_locale', 'en');
        $query->when($filters['search'] ?? null, function ($query, $search) use ($locale, $fallback) {
            $query
                ->where("name->${locale}", 'like', '%'.$search.'%')
                ->orWhere("name->${fallback}", 'like', '%'.$search.'%');
        })->when($filters['type'] ?? null, function ($query, $type) {
            if ($type === 'upcoming') {
                $query->future();
            } elseif ($type === 'past') {
                $query->past();
            } elseif ($type === 'drafts') {
                $query->drafts();
            } elseif ($type === 'archived') {
                $query->onlyArchived();
            } elseif ($type === 'deleted') {
                $query->onlyTrashed();
            }
        })->when($filters['trashed'] ?? null, function ($query, $trashed) {
            if ($trashed === 'with') {
                $query->withTrashed();
            } elseif ($trashed === 'only') {
                $query->onlyTrashed();
            }
        });
    }
}
