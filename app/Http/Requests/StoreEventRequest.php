<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreEventRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'location' => ['nullable', 'string', 'max:255'],
            'event_date' => ['nullable', 'date_format:Y-m-d'],
            'event_time' => ['nullable', 'date_format:H:i'],
            'max_participants' => ['nullable', 'integer', 'min:1'],
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
