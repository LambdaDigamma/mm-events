<?php

namespace LambdaDigamma\MMEvents\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
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

    public function publish(Request $request, Event $event)
    {
        $event->publish();

        return $request->wantsJson()
                ? new JsonResponse('', 200)
                : redirect()->back()->with('success', 'Die Veranstaltung wurde veröffentlicht.');
    }

    public function unpublish(Request $request, Event $event)
    {
        $event->unpublish();

        return $request->wantsJson()
                ? new JsonResponse('', 200)
                : redirect()->back()->with('info', 'Die Veranstaltung wurde ins Entwurfsstadium zurückversetzt.');
    }
}
