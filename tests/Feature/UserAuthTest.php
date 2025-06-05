<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Illuminate\Support\Facades\Hash;

class UserAuthTest extends TestCase
{
    use RefreshDatabase;

    protected User $regularUser;
    protected User $adminUser;

    protected function setUp(): void
    {
        parent::setUp();

        $this->regularUser = User::factory()->create([
            'email' => 'user@example.com',
            'password' => Hash::make('Password123!'),
            'is_admin' => false,
        ]);

        $this->adminUser = User::factory()->create([
            'email' => 'admin@example.com',
            'password' => Hash::make('AdminPassword123!'),
            'is_admin' => true,
        ]);
    }

    public function test_user_can_register(): void
    {
        $response = $this->postJson('/api/auth/register', [
            'name' => 'New User',
            'email' => 'new@example.com',
            'password' => 'Password123!',
            'password_confirmation' => 'Password123!',
        ]);

        $response->assertStatus(201)
            ->assertJsonStructure(['message', 'user']);
    }

    public function test_user_can_login(): void
    {
        $response = $this->postJson('/api/auth/login', [
            'email' => 'user@example.com',
            'password' => 'Password123!',
        ]);

        $response->assertStatus(200)
            ->assertJsonStructure(['message', 'token', 'user']);
    }

    public function test_login_fails_with_invalid_credentials(): void
    {
        $response = $this->postJson('/api/auth/login', [
            'email' => 'user@example.com',
            'password' => 'wrongpassword',
        ]);

        $response->assertStatus(401)
            ->assertJson(['message' => 'Invalid credentials']);
    }

    public function test_user_can_logout(): void
    {
        $token = $this->regularUser->createToken('test-token')->plainTextToken;

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->postJson('/api/auth/logout');

        $response->assertStatus(200)
            ->assertJson(['message' => 'Logged out']);
    }

    public function test_user_can_get_their_profile(): void
    {
        $token = $this->regularUser->createToken('test-token')->plainTextToken;

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->getJson('/api/auth/me');

        $response->assertStatus(200)
            ->assertJsonPath('email', 'user@example.com');
    }

    public function test_user_can_update_own_profile(): void
    {
        $token = $this->regularUser->createToken('test-token')->plainTextToken;

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->putJson('/api/auth/user', [
                'name' => 'Updated Name',
                'current_password' => 'Password123!',
                'password' => 'NewPassword123!',
                'password_confirmation' => 'NewPassword123!'
            ]);

        $response->assertStatus(200)
            ->assertJson([
                'message' => 'Profile updated successfully',
                'user' => [
                    'name' => 'Updated Name'
                ]
            ]);
    }

    public function test_password_change_requires_current_password(): void
    {
        $token = $this->regularUser->createToken('test-token')->plainTextToken;

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->putJson('/api/auth/user', [
                'password' => 'NewPassword123!',
                'password_confirmation' => 'NewPassword123!',
                // Brak current_password
            ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['current_password']);
    }

    public function test_admin_can_update_any_user(): void
    {
        $token = $this->adminUser->createToken('admin-token')->plainTextToken;
        $targetUser = User::factory()->create();

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->putJson("/api/auth/users/{$targetUser->id}", [
                'name' => 'Admin Updated Name',
                'is_admin' => true
            ]);

        $response->assertStatus(200)
            ->assertJsonPath('user.name', 'Admin Updated Name');
    }

    public function test_regular_user_cannot_update_other_users(): void
    {
        $token = $this->regularUser->createToken('user-token')->plainTextToken;
        $targetUser = User::factory()->create();

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->putJson("/api/auth/users/{$targetUser->id}", [
                'name' => 'Try to Update'
            ]);

        $response->assertStatus(403);
    }

    public function test_admin_can_delete_any_user(): void
    {
        $token = $this->adminUser->createToken('admin-token')->plainTextToken;
        $targetUser = User::factory()->create();

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->deleteJson("/api/auth/users/{$targetUser->id}");

        $response->assertStatus(200)
            ->assertJson(['message' => 'User deleted successfully']);

        $this->assertDatabaseMissing('users', ['id' => $targetUser->id]);
    }

    public function test_regular_user_cannot_delete_other_users(): void
    {
        $token = $this->regularUser->createToken('user-token')->plainTextToken;
        $targetUser = User::factory()->create();

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->deleteJson("/api/auth/users/{$targetUser->id}");

        $response->assertStatus(403);
    }
}
