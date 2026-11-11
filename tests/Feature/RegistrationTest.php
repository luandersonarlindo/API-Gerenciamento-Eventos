<?php

namespace Tests\Feature;

use App\Models\Event;
use App\Models\Registration;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class RegistrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_register_list_and_cancel(): void
    {
        $event = Event::factory()->create(['max_participants' => 2]);
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $id = $this->postJson("/api/events/{$event->id}/registrations")
            ->assertCreated()
            ->assertJsonPath('data.event.id', $event->id)
            ->assertJsonPath('data.event.available_spots', 1)
            ->json('data.id');

        $this->getJson('/api/registrations')
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.event.name', $event->name);

        $this->deleteJson("/api/registrations/$id")->assertOk();
        $this->assertDatabaseCount('registrations', 0);
    }

    public function test_duplicate_registration_is_rejected(): void
    {
        $event = Event::factory()->create();
        Sanctum::actingAs(User::factory()->create());

        $this->postJson("/api/events/{$event->id}/registrations")->assertCreated();
        $this->postJson("/api/events/{$event->id}/registrations")->assertStatus(409);
        $this->assertDatabaseCount('registrations', 1);
    }

    public function test_full_event_rejects_registration_until_someone_cancels(): void
    {
        $event = Event::factory()->create(['max_participants' => 1]);
        $first = Registration::create([
            'user_id' => User::factory()->create()->id,
            'event_id' => $event->id,
        ]);

        Sanctum::actingAs(User::factory()->create());

        $this->postJson("/api/events/{$event->id}/registrations")
            ->assertUnprocessable()
            ->assertJsonPath('message', 'Este evento já atingiu a lotação máxima.');

        $first->delete();

        $this->postJson("/api/events/{$event->id}/registrations")->assertCreated();
    }

    public function test_event_without_limit_accepts_registrations(): void
    {
        $event = Event::factory()->create(['max_participants' => null]);
        Sanctum::actingAs(User::factory()->create());

        $this->postJson("/api/events/{$event->id}/registrations")
            ->assertCreated()
            ->assertJsonPath('data.event.available_spots', null);
    }

    public function test_user_sees_only_own_registrations_and_cannot_cancel_others(): void
    {
        $event = Event::factory()->create();
        $other = Registration::create([
            'user_id' => User::factory()->create()->id,
            'event_id' => $event->id,
        ]);

        Sanctum::actingAs(User::factory()->create());

        $this->getJson('/api/registrations')->assertOk()->assertJsonCount(0, 'data');
        $this->deleteJson("/api/registrations/{$other->id}")->assertForbidden();
        $this->assertDatabaseHas('registrations', ['id' => $other->id]);
    }

    public function test_max_participants_cannot_go_below_current_registrations(): void
    {
        $user = User::factory()->create();
        $event = Event::factory()->create(['user_id' => $user->id, 'max_participants' => 5]);
        foreach (User::factory(3)->create() as $participant) {
            Registration::create(['user_id' => $participant->id, 'event_id' => $event->id]);
        }

        Sanctum::actingAs($user);

        $this->putJson("/api/events/{$event->id}", ['max_participants' => 2])
            ->assertJsonValidationErrors(['max_participants']);
        $this->putJson("/api/events/{$event->id}", ['max_participants' => 3])->assertOk();
    }
}
