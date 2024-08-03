<?php

use App\Models\User;
use Symfony\Component\HttpFoundation\Response;

use function Pest\Laravel\postJson;

describe('Login', function () {

    beforeEach(function () {
        $this->user = User::factory()->admin()->create(
            [
                'phone' => '+6281234567890',
                'password' => 'password',
            ]
        );
    });
    it('should authenticate with valid credentials', function () {
        $response = postJson('/api/v1/auth/login', [
            'user_name' => $this->user->phone,
            'password' => 'password',
        ]);
        $response->assertStatus(Response::HTTP_OK);
    });
    it('should not authenticate with incorrect credentials', function () {
        $response = postJson('/api/v1/auth/login', [
            'user_name' => $this->user->phone,
            'password' => 'admin12',
        ]);
        $response->assertStatus(Response::HTTP_UNAUTHORIZED);
    });
});
