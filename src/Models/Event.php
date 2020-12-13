<?php

namespace LambdaDigamma\MMEvents\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use LambdaDigamma\MMEvents\Database\Factories\EventFactory;
use LambdaDigamma\MMEvents\Exceptions\InvalidLink;
use LambdaDigamma\MMEvents\Link;
use LaravelArchivable\Archivable;
use Spatie\Translatable\HasTranslations;

class Event extends Model
{
    use SoftDeletes;
    use Archivable;
    use HasFactory;
    use HasTranslations;

    protected $table = "mm_events";

    protected $fillable = [
        'name', 'start_date', 'end_date',
        'description', 'url', 'image_path',
        'category', 'organisation_id', 'place_id',
        'extras', 'published_at', 'scheduled_at',
    ];

    protected $casts = [
        'extras' => 'array',
    ];

    public $translatable = ['name', 'description', 'category'];

    public $dates = ['start_date', 'end_date', 'scheduled_at', 'published_at'];

    public function toArray()
    {
        $attributes = parent::toArray();

        foreach ($this->getTranslatableAttributes() as $name) {
            $attributes[$name] = $this->getTranslation($name, app()->getLocale());
        }

        return $attributes;
    }

    protected static function newFactory()
    {
        return EventFactory::new();
    }

    /**
     * Marks the event as published by setting
     * `published_at` to current timestamp.
     */
    public function publish()
    {
        $this->published_at = Carbon::now();

        return $this->save();
    }

    /**
     * Marks the event as not publsihed by resetting
     * `published_at` to null.
     */
    public function unpublish()
    {
        $this->published_at = null;

        return $this->save();
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
            $end_date = Carbon::now()->addMinutes(config('mm-events.event_default_duration'));
        }

        $link = Link::create($this->name, $start_date, $end_date)
            ->description($this->description);

        return $link->ics();
    }

    public function scopePublished($query)
    {
        return $query
            ->where('scheduled_at', '<=', Carbon::now()->toDateTimeString())
            ->orWhere('scheduled_at', '=', null);
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

    public function scopeToday(Builder $query)
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

    public function scopeFilter($query, array $filters)
    {
        $query->when($filters['search'] ?? null, function ($query, $search) {
            $query->where('name', 'like', '%'.$search.'%');
        })->when($filters['trashed'] ?? null, function ($query, $trashed) {
            if ($trashed === 'with') {
                $query->withTrashed();
            } elseif ($trashed === 'only') {
                $query->onlyTrashed();
            }
        });
    }
}
