<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

uses(RefreshDatabase::class);

test('guests visiting home are redirected to login', function (): void {
    $this->get('/')
        ->assertRedirect(route('login'));
});

test('login screen can be rendered without public registration links', function (): void {
    $response = $this->get(route('login'));

    $response->assertOk()
        ->assertSee('FinanceKu')
        ->assertDontSee('/register');
});

test('public registration routes are disabled', function (): void {
    expect(Route::has('register'))->toBeFalse();

    $this->get('/register')->assertNotFound();
    $this->post('/register', [
        'name' => 'Intruder',
        'email' => 'intruder@example.com',
        'password' => 'password',
        'password_confirmation' => 'password',
    ])->assertNotFound();
});

test('user can authenticate and remember me is enabled by default', function (): void {
    $user = User::factory()->create([
        'email' => 'admin@financeku.test',
        'password' => 'password',
    ]);

    $response = $this->post(route('login.store'), [
        'email' => 'admin@financeku.test',
        'password' => 'password',
    ]);

    $this->assertAuthenticatedAs($user);
    $response->assertRedirect(route('home'));
    $response->assertCookie(Auth::guard('web')->getRecallerName());
});

test('user can explicitly opt out of remember me', function (): void {
    $user = User::factory()->create([
        'email' => 'admin@financeku.test',
        'password' => 'password',
    ]);

    $response = $this->post(route('login.store'), [
        'email' => 'admin@financeku.test',
        'password' => 'password',
        'remember' => '0',
    ]);

    $this->assertAuthenticatedAs($user);
    $response->assertRedirect(route('home'));
    $response->assertCookieMissing(Auth::guard('web')->getRecallerName());
});

test('user cannot authenticate with invalid password', function (): void {
    User::factory()->create([
        'email' => 'admin@financeku.test',
        'password' => 'password',
    ]);

    $response = $this->from(route('login'))->post(route('login.store'), [
        'email' => 'admin@financeku.test',
        'password' => 'wrong-password',
    ]);

    $this->assertGuest();
    $response->assertRedirect(route('login'));
    $response->assertSessionHasErrors('email');
});

test('login attempts are rate limited after five failures', function (): void {
    User::factory()->create([
        'email' => 'admin@financeku.test',
        'password' => 'password',
    ]);

    for ($attempt = 0; $attempt < 5; $attempt++) {
        $this->post(route('login.store'), [
            'email' => 'admin@financeku.test',
            'password' => 'wrong-password',
        ])->assertSessionHasErrors('email');
    }

    $this->post(route('login.store'), [
        'email' => 'admin@financeku.test',
        'password' => 'wrong-password',
    ])->assertTooManyRequests();
});

test('authenticated user visiting login is redirected to home', function (): void {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->get(route('login'))
        ->assertRedirect(route('home'));
});

test('authenticated user can logout', function (): void {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->post(route('logout'));

    $this->assertGuest();
    $response->assertRedirect(route('login'));
});

test('session lifetime is configured for extended mobile sessions', function (): void {
    expect(config('session.lifetime'))->toBeGreaterThanOrEqual(43200);
});
