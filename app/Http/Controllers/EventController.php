<?php

namespace App\Http\Controllers;

use App\Models\Event;
use App\Models\EventRegistration;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\View\View;

class EventController extends Controller
{
    public function show(): View
    {
        $event = Event::where('is_published', true)
            ->where('event_date', '>=', now())
            ->orderBy('event_date')
            ->firstOrFail();

        return view('event', compact('event'));
    }

    public function register(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'event_id' => 'required|exists:events,id',
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'privacy' => 'required|accepted',
        ], [
            'first_name.required' => 'Bitte gib deinen Vornamen ein.',
            'last_name.required' => 'Bitte gib deinen Nachnamen ein.',
            'email.required' => 'Bitte gib eine gültige E-Mail-Adresse ein.',
            'email.email' => 'Bitte gib eine gültige E-Mail-Adresse ein.',
            'privacy.required' => 'Bitte bestätige die Datenschutzerklärung.',
            'privacy.accepted' => 'Bitte bestätige die Datenschutzerklärung.',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()->first(),
            ], 422);
        }

        $event = Event::findOrFail($request->event_id);

        if (!$event->is_published) {
            return response()->json([
                'success' => false,
                'message' => 'Diese Veranstaltung ist nicht verfügbar.',
            ], 404);
        }

        if ($event->isFull()) {
            return response()->json([
                'success' => false,
                'message' => 'Diese Veranstaltung ist leider bereits ausgebucht.',
            ], 409);
        }

        $existingRegistration = EventRegistration::where('event_id', $event->id)
            ->where('email', $request->email)
            ->first();

        if ($existingRegistration) {
            return response()->json([
                'success' => false,
                'message' => 'Du bist bereits für diese Veranstaltung angemeldet.',
            ], 409);
        }

        $registration = EventRegistration::create([
            'event_id' => $event->id,
            'first_name' => $request->first_name,
            'last_name' => $request->last_name,
            'email' => $request->email,
            'privacy_accepted' => true,
            'status' => 'confirmed',
            'confirmed_at' => now(),
        ]);

        // TODO: Send confirmation email

        return response()->json([
            'success' => true,
            'message' => "Vielen Dank, {$request->first_name}! Deine Anmeldung war erfolgreich. Du erhältst in Kürze eine Bestätigung per E-Mail.",
        ]);
    }
}
