<?php

namespace LambdaDigamma\MMEvents\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use LambdaDigamma\MMEvents\Http\Requests\PublishEvent;
use LambdaDigamma\MMEvents\Models\Event;

class EventActionController extends Controller
{
    public function archive(Request $request, Event $event)
    {
        $event->archive();

        return $request->wantsJson()
                ? new JsonResponse('', 200)
                : redirect()->back()->with('success', 'Die Veranstaltung wurde archiviert.');
    }

    public function unarchive(Request $request, Event $event)
    {
        $event->unArchive();

        return $request->wantsJson()
                ? new JsonResponse('', 200)
                : redirect()->back()->with('success', 'Das Archivieren wurde rückgängig gemacht.');
    }

    public function publish(PublishEvent $request, Event $event)
    {
        $published_at = request()->published_at;
        $event->scheduleFor($published_at ? Carbon::parse($published_at) : now());

        return $request->wantsJson()
            ? new JsonResponse('', 200)
            : redirect()->back()->with('info', 'Der Veröffentlichungszeitpunkt wurde festgelegt.');
    }

    public function unpublish(Request $request, Event $event)
    {
        $event->unpublish();

        return $request->wantsJson()
                ? new JsonResponse('', 200)
                : redirect()->back()->with('info', 'Die Veranstaltung wurde ins Entwurfsstadium zurückversetzt.');
    }
}
