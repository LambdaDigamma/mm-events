<?php

namespace LambdaDigamma\MMEvents\Http\Controllers;

use Illuminate\Http\JsonResponse;
use LambdaDigamma\MMEvents\Http\Requests\UpdateGeneralEvent;
use LambdaDigamma\MMEvents\Models\Event;

class EventController extends Controller
{
    public function store()
    {
        //
    }

    public function update(UpdateGeneralEvent $request, Event $event)
    {
        $event->update($request->validated());

        return $request->wantsJson()
                ? new JsonResponse('', 200)
                : back()->with('success', 'Die Daten wurden gespeichert.');
    }
}
