<?php

use App\Models\User;
use Database\Seeders\RoleSeeder;
use Laravel\Sanctum\PersonalAccessToken;
use Spatie\Permission\PermissionRegistrar;

beforeEach(function () {
    $this->seed(RoleSeeder::class);
    app(PermissionRegistrar::class)->forgetCachedPermissions();
});

it('registers a user as staff and returns a token', function () {
    $response = $this->postJson(route('register'), [
        'name' => 'Ada Lovelace',
        'email' => 'ada@example.com',
        'password' => 'password-1234',
        'password_confirmation' => 'password-1234',
    ]);

    $response->assertCreated()
        ->assertJsonPath('user.email', 'ada@example.com')
        ->assertJsonPath('user.roles.0', 'staff');

    expect($response->json('token'))->toBeString()->not->toBeEmpty();
});

it('rejects registration with a duplicate email', function () {
    User::factory()->create(['email' => 'ada@example.com']);

    $this->postJson(route('register'), [
        'name' => 'Ada Lovelace',
        'email' => 'ada@example.com',
        'password' => 'password-1234',
        'password_confirmation' => 'password-1234',
    ])->assertJsonValidationErrors('email');
});

it('issues a token for valid credentials', function () {
    $user = User::factory()->create(['password' => 'password-1234']);

    $response = $this->postJson(route('login'), [
        'email' => $user->email,
        'password' => 'password-1234',
    ]);

    $response->assertOk()->assertJsonPath('user.id', $user->id);
    expect($response->json('token'))->toBeString()->not->toBeEmpty();
});

it('rejects invalid credentials', function () {
    $user = User::factory()->create(['password' => 'password-1234']);

    $this->postJson(route('login'), [
        'email' => $user->email,
        'password' => 'wrong-password',
    ])->assertJsonValidationErrors('email');
});

it('authenticates a request with the bearer token', function () {
    $user = User::factory()->create(['password' => 'password-1234']);

    $token = $this->postJson(route('login'), [
        'email' => $user->email,
        'password' => 'password-1234',
    ])->json('token');

    $this->withToken($token)
        ->getJson(route('me'))
        ->assertOk()
        ->assertJsonPath('data.email', $user->email);
});

it('revokes the current token on logout', function () {
    $user = User::factory()->create(['password' => 'password-1234']);

    $token = $this->postJson(route('login'), [
        'email' => $user->email,
        'password' => 'password-1234',
    ])->json('token');

    $this->withToken($token)->postJson(route('logout'))->assertNoContent();

    expect(PersonalAccessToken::count())->toBe(0);

    // The sanctum guard caches the resolved user for the lifetime of the test
    // application, so it has to be flushed before re-checking the token.
    $this->app['auth']->forgetGuards();

    $this->withToken($token)->getJson(route('me'))->assertUnauthorized();
});

it('rejects an unauthenticated request', function () {
    $this->getJson(route('me'))->assertUnauthorized();
});
