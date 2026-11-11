<?php

namespace App\Http\Requests;

use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Foundation\Http\FormRequest;

class UpdateEventRequest extends FormRequest
{
    /** Somente quem cadastrou o evento pode alterá-lo. */
    public function authorize(): bool
    {
        return $this->route('event')->user_id === $this->user()->id;
    }

    protected function failedAuthorization(): void
    {
        throw new AuthorizationException('Somente o organizador pode alterar este evento.');
    }

    public function rules(): array
    {
        // A lotação não pode ficar abaixo do número de inscritos atuais
        $registered = $this->route('event')->registrations()->count();

        return [
            // "sometimes" permite atualização parcial (PATCH), mas o nome nunca pode ficar vazio
            'name' => ['sometimes', 'required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'location' => ['nullable', 'string', 'max:255'],
            'event_date' => ['nullable', 'date_format:Y-m-d'],
            'event_time' => ['nullable', 'date_format:H:i'],
            'max_participants' => ['nullable', 'integer', 'min:'.max(1, $registered)],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'O nome do evento é obrigatório.',
            'event_date.date_format' => 'A data do evento deve estar no formato AAAA-MM-DD.',
            'event_time.date_format' => 'O horário deve estar no formato HH:MM.',
            'max_participants.integer' => 'A quantidade máxima de participantes deve ser um número inteiro.',
            'max_participants.min' => 'A quantidade máxima de participantes deve ser no mínimo :min.',
        ];
    }
}
