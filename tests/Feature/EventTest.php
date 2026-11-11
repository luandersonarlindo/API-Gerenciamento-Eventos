<?php

namespace Tests\Feature;

use App\Models\Event;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class EventTest extends TestCase
{
    use RefreshDatabase;

    public function test_crud_of_events(): void
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $id = $this->postJson('/api/events', [
            'name' => 'Semana de TI',
            'description' => 'Palestras e minicursos',
            'location' => 'Auditório',
            'event_date' => '2026-12-01',
            'event_time' => '19:00',
            'max_participants' => 50,
        ])
            ->assertCreated()
            ->assertJsonPath('data.name', 'Semana de TI')
            ->assertJsonPath('data.event_date', '2026-12-01')
            ->assertJsonPath('data.event_time', '19:00')
            ->assertJsonPath('data.available_spots', 50)
            ->assertJsonPath('data.organizer.id', $user->id)
            ->json('data.id');

        $this->getJson('/api/events')->assertOk()->assertJsonCount(1, 'data');
        $this->getJson("/api/events/$id")->assertOk()->assertJsonPath('data.id', $id);

        $this->putJson("/api/events/$id", ['name' => 'Semana de TI 2026', 'max_participants' => 80])
            ->assertOk()
            ->assertJsonPath('data.name', 'Semana de TI 2026')
            ->assertJsonPath('data.max_participants', 80)
            ->assertJsonPath('data.location', 'Auditório');

        $this->deleteJson("/api/events/$id")->assertOk();
        $this->assertDatabaseCount('events', 0);
        $this->getJson("/api/events/$id")->assertNotFound()->assertJsonStructure(['message']);
    }

    public function test_event_name_is_required(): void
    {
        Sanctum::actingAs(User::factory()->create());

        $this->postJson('/api/events', ['location' => 'Sala 1'])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['name']);

        $this->postJson('/api/events', [
            'name' => 'X', 'event_date' => '01/12/2026', 'event_time' => '7pm', 'max_participants' => 0,
        ])->assertJsonValidationErrors(['event_date', 'event_time', 'max_participants']);

        $event = Event::factory()->create(['user_id' => auth()->id()]);
        $this->putJson("/api/events/{$event->id}", ['name' => ''])
            ->assertJsonValidationErrors(['name']);
    }

    public function test_only_organizer_can_update_or_delete(): void
    {
        $event = Event::factory()->create();
        Sanctum::actingAs(User::factory()->create());

        $this->putJson("/api/events/{$event->id}", ['name' => 'Hack'])->assertForbidden();
        $this->deleteJson("/api/events/{$event->id}")->assertForbidden();
        $this->assertDatabaseHas('events', ['id' => $event->id, 'name' => $event->name]);
    }
}
