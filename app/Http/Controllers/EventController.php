<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreEventRequest;
use App\Http\Requests\UpdateEventRequest;
use App\Http\Resources\EventResource;
use App\Models\Event;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class EventController extends Controller
{
    /** GET /api/events */
    public function index(): AnonymousResourceCollection
    {
        $events = Event::with('user')
            ->withCount('registrations')
            ->orderBy('event_date')
            ->orderBy('event_time')
            ->paginate(15);

        return EventResource::collection($events);
    }

    /** POST /api/events */
    public function store(StoreEventRequest $request): JsonResponse
    {
        $event = $request->user()->events()->create($request->validated());

        return (new EventResource($event->load('user')))
            ->response()
            ->setStatusCode(201);
    }

    /** GET /api/events/{event} */
    public function show(Event $event): EventResource
    {
        return new EventResource($event->load('user')->loadCount('registrations'));
    }

    /** PUT/PATCH /api/events/{event} */
    public function update(UpdateEventRequest $request, Event $event): EventResource
    {
        $event->update($request->validated());

        return new EventResource($event->load('user')->loadCount('registrations'));
    }

    /** DELETE /api/events/{event} */
    public function destroy(Request $request, Event $event): JsonResponse
    {
        if ($event->user_id !== $request->user()->id) {
            return response()->json([
                'message' => 'Somente o organizador pode excluir este evento.',
            ], 403);
        }

        $event->delete();

        return response()->json(['message' => 'Evento excluído com sucesso.']);
    }
}
