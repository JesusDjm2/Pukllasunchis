<?php

namespace Tests\Feature\Api;

use App\Models\DeviceToken;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DeviceTokenTest extends TestCase
{
    use RefreshDatabase;

    public function test_alumno_can_register_device_token(): void
    {
        $user = User::factory()->create();
        $token = $user->createToken('test')->plainTextToken;

        $response = $this->withHeader('Authorization', "Bearer {$token}")
            ->postJson('/api/v1/device-tokens', ['token' => 'fcm-token-abc', 'platform' => 'android']);

        $response->assertStatus(201);
        $this->assertDatabaseHas('device_tokens', [
            'user_id' => $user->id,
            'token' => 'fcm-token-abc',
            'platform' => 'android',
        ]);
    }

    public function test_registering_same_token_reassigns_it_to_new_user(): void
    {
        $userA = User::factory()->create();
        $userB = User::factory()->create();
        DeviceToken::factory()->create(['user_id' => $userA->id, 'token' => 'shared-device']);

        $tokenB = $userB->createToken('test')->plainTextToken;

        $this->withHeader('Authorization', "Bearer {$tokenB}")
            ->postJson('/api/v1/device-tokens', ['token' => 'shared-device', 'platform' => 'android'])
            ->assertStatus(201);

        $this->assertDatabaseHas('device_tokens', ['token' => 'shared-device', 'user_id' => $userB->id]);
        $this->assertDatabaseCount('device_tokens', 1);
    }

    public function test_alumno_can_unregister_own_device_token(): void
    {
        $user = User::factory()->create();
        DeviceToken::factory()->create(['user_id' => $user->id, 'token' => 'to-delete']);
        $token = $user->createToken('test')->plainTextToken;

        $this->withHeader('Authorization', "Bearer {$token}")
            ->deleteJson('/api/v1/device-tokens', ['token' => 'to-delete'])
            ->assertOk();

        $this->assertDatabaseMissing('device_tokens', ['token' => 'to-delete']);
    }

    public function test_cannot_unregister_another_users_token(): void
    {
        $userA = User::factory()->create();
        $userB = User::factory()->create();
        DeviceToken::factory()->create(['user_id' => $userA->id, 'token' => 'owned-by-a']);
        $tokenB = $userB->createToken('test')->plainTextToken;

        $this->withHeader('Authorization', "Bearer {$tokenB}")
            ->deleteJson('/api/v1/device-tokens', ['token' => 'owned-by-a'])
            ->assertOk();

        $this->assertDatabaseHas('device_tokens', ['token' => 'owned-by-a']);
    }

    public function test_unauthenticated_cannot_register_token(): void
    {
        $this->postJson('/api/v1/device-tokens', ['token' => 'x'])->assertStatus(401);
    }
}
