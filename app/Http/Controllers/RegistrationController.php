<?php

namespace App\Http\Controllers;

use App\Http\Resources\RegistrationResource;
use App\Models\Event;
use App\Models\Registration;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\DB;

class RegistrationController extends Controller
{
    /** GET /api/registrations — inscrições do usuário autenticado */
    public function index(Request $request): AnonymousResourceCollection
    {
        $registrations = $request->user()
            ->registrations()
            ->with(['event' => fn ($query) => $query->withCount('registrations')])
            ->latest()
            ->get();

        return RegistrationResource::collection($registrations);
    }

    /** POST /api/events/{event}/registrations — inscreve o usuário autenticado no evento */
    public function store(Request $request, Event $event): JsonResponse
    {
        $user = $request->user();

        // A transação com bloqueio evita que duas inscrições simultâneas ultrapassem a lotação
        return DB::transaction(function () use ($user, $event) {
            $event = Event::lockForUpdate()->findOrFail($event->id);

            if ($event->registrations()->where('user_id', $user->id)->exists()) {
                return response()->json([
                    'message' => 'Você já está inscrito neste evento.',
                ], 409);
            }

            if ($event->isFull()) {
                return response()->json([
                    'message' => 'Este evento já atingiu a lotação máxima.',
                ], 422);
            }

            $registration = $event->registrations()->create(['user_id' => $user->id]);
            $registration->load(['event' => fn ($query) => $query->withCount('registrations')]);

            return (new RegistrationResource($registration))
                ->additional(['message' => 'Inscrição realizada com sucesso.'])
                ->response()
                ->setStatusCode(201);
        });
    }

    /** DELETE /api/registrations/{registration} — cancela uma inscrição do próprio usuário */
    public function destroy(Request $request, Registration $registration): JsonResponse
    {
        if ($registration->user_id !== $request->user()->id) {
            return response()->json([
                'message' => 'Você não pode cancelar a inscrição de outro usuário.',
            ], 403);
        }

        $registration->delete();

        return response()->json(['message' => 'Inscrição cancelada com sucesso.']);
    }
}
