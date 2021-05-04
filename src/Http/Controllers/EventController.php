<?php

namespace LambdaDigamma\MMEvents\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Support\Carbon;
use LambdaDigamma\MMEvents\Http\Requests\StoreEventRequest;
use LambdaDigamma\MMEvents\Http\Requests\UpdateGeneralEvent;
use LambdaDigamma\MMEvents\Models\Event;

class EventController extends Controller
{
    /**
     * @return JsonResponse|\Illuminate\Http\RedirectResponse
     */
    public function store(StoreEventRequest $request)
    {
        $event = Event::create($request->validated());

        return $request->wantsJson()
                ? new JsonResponse($event, 302)
                : back()->with('success', 'Die Veranstaltung wurde erstellt.')->with('data', ['id' => $event->id]);
    }

    /**
     * @return JsonResponse|\Illuminate\Http\RedirectResponse
     */
    public function update(UpdateGeneralEvent $request, Event $event)
    {
        $event->fill($request->except(['start_date', 'end_date']));
        
        $event->start_date = Carbon::parse($request->start_date)
            ->timezone(config('app.timezone', 'UTC'))
            ->toDateTimeLocalString();

        $event->end_date = Carbon::parse($request->end_date)
            ->timezone(config('app.timezone', 'UTC'))
            ->toDateTimeLocalString();

        $event->save();

        return $request->wantsJson()
                ? new JsonResponse('', 200)
                : back()->with('success', 'Die Daten wurden gespeichert.');
    }
}
