<?php

namespace Tests\Feature;

use App\Models\Notification;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class NotificationTest extends TestCase
{
    use RefreshDatabase;

    public function test_unauthenticated_user_cannot_access_notifications(): void
    {
        $response = $this->getJson('/notifications');
        $response->assertStatus(401);
    }

    public function test_user_can_fetch_their_notifications(): void
    {
        $user = User::factory()->create();
        $other = User::factory()->create();

        Notification::create([
            'user_id' => $user->id,
            'title' => 'Notifikasi 1',
            'message' => 'Pesan 1',
            'type' => 'info',
        ]);

        Notification::create([
            'user_id' => $other->id,
            'title' => 'Notifikasi Orang Lain',
            'message' => 'Pesan Orang Lain',
            'type' => 'warning',
        ]);

        $response = $this->actingAs($user)->getJson('/notifications');
        $response->assertStatus(200);
        $response->assertJsonCount(1, 'data');
        $response->assertJsonFragment(['title' => 'Notifikasi 1']);
        $response->assertJsonMissing(['title' => 'Notifikasi Orang Lain']);
    }

    public function test_user_can_mark_notification_as_read(): void
    {
        $user = User::factory()->create();
        $notif = Notification::create([
            'user_id' => $user->id,
            'title' => 'Ujian Terjadwal',
            'message' => 'Jadwal sempro telah ditentukan.',
            'type' => 'success',
        ]);

        $this->assertNull($notif->read_at);

        $response = $this->actingAs($user)->patchJson("/notifications/{$notif->id}/read");
        $response->assertStatus(200);
        $response->assertJson(['success' => true]);

        $this->assertNotNull($notif->fresh()->read_at);
    }

    public function test_user_cannot_mark_another_users_notification(): void
    {
        $user = User::factory()->create();
        $other = User::factory()->create();

        $notif = Notification::create([
            'user_id' => $other->id,
            'title' => 'Private',
            'message' => 'Pesan orang lain',
            'type' => 'info',
        ]);

        $response = $this->actingAs($user)->patchJson("/notifications/{$notif->id}/read");
        $response->assertStatus(404);
        $this->assertNull($notif->fresh()->read_at);
    }

    public function test_user_can_mark_all_notifications_as_read(): void
    {
        $user = User::factory()->create();

        $n1 = Notification::create([
            'user_id' => $user->id,
            'title' => 'N1',
            'message' => 'M1',
            'type' => 'info',
        ]);
        $n2 = Notification::create([
            'user_id' => $user->id,
            'title' => 'N2',
            'message' => 'M2',
            'type' => 'info',
        ]);

        $response = $this->actingAs($user)->postJson('/notifications/read-all');
        $response->assertStatus(200);
        $response->assertJson(['success' => true]);

        $this->assertNotNull($n1->fresh()->read_at);
        $this->assertNotNull($n2->fresh()->read_at);
    }
}
