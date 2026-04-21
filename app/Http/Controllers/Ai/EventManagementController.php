<?php

declare(strict_types=1);

namespace App\Http\Controllers\Ai;

use App\Actions\Ai\CreateAiEventDraft;
use App\Actions\Ai\PlanAiEvent;
use App\Actions\Ai\SetAiEventPublicationState;
use App\Actions\Ai\UpdateAiEvent;
use App\Http\Requests\Ai\ConfirmPublicationRequest;
use App\Http\Requests\Ai\PlanEventRequest;
use App\Http\Requests\Ai\StoreAiEventRequest;
use App\Http\Requests\Ai\UpdateAiEventRequest;
use App\Models\Event;
use App\Services\Ai\AiDataFormatter;
use Illuminate\Http\JsonResponse;

final class EventManagementController
{
    public function index(AiDataFormatter $formatter): JsonResponse
    {
        $events = Event::query()->withCount('activeRegistrations')->orderBy('event_date')->get();

        return response()->json([
            'data' => $formatter->events($events),
        ]);
    }

    public function show(Event $event, AiDataFormatter $formatter): JsonResponse
    {
        $event->load('media')->loadCount('activeRegistrations');

        return response()->json([
            'data' => $formatter->event($event, includeRegistrations: true),
        ]);
    }

    public function plan(PlanEventRequest $request, PlanAiEvent $action): JsonResponse
    {
        return response()->json([
            'data' => $action->execute($request->string('prompt')->toString()),
        ]);
    }

    public function store(StoreAiEventRequest $request, CreateAiEventDraft $action, AiDataFormatter $formatter): JsonResponse
    {
        $event = $action->execute($request->validated());
        $event->loadCount('activeRegistrations');

        return response()->json([
            'data' => $formatter->event($event),
        ], 201);
    }

    public function update(UpdateAiEventRequest $request, Event $event, UpdateAiEvent $action, AiDataFormatter $formatter): JsonResponse
    {
        $event = $action->execute($event, $request->validated());
        $event->loadCount('activeRegistrations');

        return response()->json([
            'data' => $formatter->event($event),
        ]);
    }

    public function publish(ConfirmPublicationRequest $request, Event $event, SetAiEventPublicationState $action, AiDataFormatter $formatter): JsonResponse
    {
        $event = $action->execute($event, (bool) $request->validated()['is_published']);
        $event->loadCount('activeRegistrations');

        return response()->json([
            'data' => $formatter->event($event),
        ]);
    }
}
