<?php 

namespace LambdaDigamma\MMEvents\Http\Controllers;

use Illuminate\Http\JsonResponse;
use LambdaDigamma\MMEvents\Http\Controllers\Controller;
use LambdaDigamma\MMEvents\Models\Event;
use LambdaDigamma\MMEvents\Http\Requests\UpdatePlaceEvent;

class EventPlaceController extends Controller
{

    public function update(UpdatePlaceEvent $request, Event $event)
    {
        $event->place_id = $request->place_id;
        $event->save();

        return $request->wantsJson()
                ? new JsonResponse('', 200)
                : redirect()->back()->with('success', 'Der neue Spielort wurde gespeichert.');
    }

}