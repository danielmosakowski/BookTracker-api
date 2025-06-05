<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\URL;
use Tests\TestCase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;

class UserAuthTest extends TestCase
{
    use RefreshDatabase;

    protected User $regularUser;
    protected User $adminUser;

    protected function setUp(): void
    {
        parent::setUp();
        Mail::fake();

        // Wyłącz prawdziwe zapytania HTTP
        Http::preventStrayRequests();

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

    public function test_user_can_register_with_valid_captcha()
    {
        $response = $this->postJson('/api/auth/register', [
            'name' => 'New User',
            'email' => 'new@example.com',
            'password' => 'Password123!',
            'password_confirmation' => 'Password123!',
            'captcha_token' => 'valid_captcha_token' // Używamy specjalnego tokenu
        ]);

        $response->assertStatus(201);
    }

    public function test_registration_fails_with_invalid_captcha()
    {
        $response = $this->postJson('/api/auth/register', [
            'name' => 'Test User',
            'email' => 'test_'.time().'@example.com',
            'password' => 'Password123!',
            'password_confirmation' => 'Password123!',
            'captcha_token' => 'invalid_captcha_token' // Inny token
        ]);

        $response->assertStatus(422)
            ->assertJson(['message' => 'Invalid CAPTCHA']);
    }


    public function test_login_requires_captcha()
    {
        $response = $this->postJson('/api/auth/login', [
            'email' => 'user@example.com',
            'password' => 'Password123!'
            // Brak captcha_token
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['captcha_token']);
    }



    public function test_login_fails_with_invalid_credentials()
    {
        $response = $this->postJson('/api/auth/login', [
            'email' => 'user@example.com',
            'password' => 'wrongpassword',
            'captcha_token' => 'valid_captcha_token' // Używamy ważnego tokenu CAPTCHA
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

    public function test_admin_can_update_any_user()
    {
        $admin = User::factory()->create([
            'is_admin' => true, // Kluczowe!
            'email_verified_at' => null // Admin nie wymaga weryfikacji email
        ]);

        $token = $admin->createToken('admin-token')->plainTextToken;
        $targetUser = User::factory()->create();

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->putJson("/api/auth/users/{$targetUser->id}", [
                'name' => 'Admin Updated Name'
            ]);

        $response->assertStatus(200);
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
    public function test_email_verification_required_after_registration()
    {
        $response = $this->postJson('/api/auth/register', [
            'name' => 'New User',
            'email' => 'new@example.com',
            'password' => 'Password123!',
            'password_confirmation' => 'Password123!',
            'captcha_token' => 'valid_captcha_token'
        ]);

        $response->assertStatus(201)
            ->assertJson(['message' => 'Registration successful. Please check your email to verify your account.']);

        $user = User::where('email', 'new@example.com')->first();
        $this->assertFalse($user->hasVerifiedEmail());
    }

    public function test_can_verify_email()
    {
        // Użyj poprawnej metody
        $user = User::factory()->create([
            'email_verified_at' => null // Ręcznie ustaw jako niezweryfikowany
        ]);

        $verificationUrl = URL::temporarySignedRoute(
            'verification.verify',
            now()->addMinutes(60),
            ['id' => $user->id, 'hash' => sha1($user->email)]
        );

        $response = $this->getJson($verificationUrl);
        $response->assertStatus(200)
            ->assertJson(['message' => 'Email verified successfully']);

        $this->assertNotNull($user->fresh()->email_verified_at);
    }

    public function test_cannot_login_with_unverified_email()
    {
        $user = User::factory()->create([
            'email' => 'unverified@example.com',
            'password' => Hash::make('Password123!'),
            'email_verified_at' => null,
            'is_admin' => false // Upewnij się, że to nie jest admin
        ]);

        $response = $this->postJson('/api/auth/login', [
            'email' => 'unverified@example.com',
            'password' => 'Password123!',
            'captcha_token' => 'valid_captcha_token'
        ]);

        $response->assertStatus(403)
            ->assertJson(['message' => 'Email not verified']);

        $this->assertCount(0, $user->tokens);
    }


}
