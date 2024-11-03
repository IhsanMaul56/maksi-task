<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;

class LoginControllerTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_can_login_with_valid_credentials()
    {
        // Buat pengguna
        $user = \App\Models\User::factory()->create([
            'email' => 'test@example.com',
            'password' => bcrypt('password123'),
        ]);

        // Kirim permintaan login
        $response = $this->postJson('/api/login', [
            'email' => 'test@example.com',
            'password' => 'password123',
        ]);

        // Periksa respons
        $response->assertStatus(200)
                 ->assertJson([
                     'success' => true,
                     'user' => [
                         'email' => 'test@example.com',
                     ],
                 ]);
    }

    /** @test */
    public function it_fails_login_with_invalid_credentials()
    {
        // Kirim permintaan login dengan kredensial yang salah
        $response = $this->postJson('/api/login', [
            'email' => 'wrong@example.com',
            'password' => 'wrongpassword',
        ]);

        // Periksa respons
        $response->assertStatus(401)
                 ->assertJson([
                     'success' => false,
                     'message' => 'Email atau Password Anda salah',
                 ]);
    }

    /** @test */
    public function it_fails_login_with_missing_fields()
    {
        // Kirim permintaan login tanpa email dan password
        $response = $this->postJson('/api/login', []);

        // Periksa respons
        $response->assertStatus(422)
                 ->assertJsonStructure(['email', 'password']);
    }
}
