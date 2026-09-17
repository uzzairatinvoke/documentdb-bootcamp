<?php

use App\Models\Document;
use App\Models\User;
use Database\Seeders\RoleSeeder;
use Illuminate\Support\Facades\Gate;
use Spatie\Permission\PermissionRegistrar;

beforeEach(function () {
    $this->seed(RoleSeeder::class);
    app(PermissionRegistrar::class)->forgetCachedPermissions();
});

function authUser(string $role): User
{
    return User::factory()->create()->assignRole($role);
}

it('allows create-document gate for admin and manager only', function (string $role, bool $allowed) {
    expect(Gate::forUser(authUser($role))->allows('create-document'))->toBe($allowed);
})->with([
    ['admin', true],
    ['manager', true],
    ['staff', false],
]);

it('allows update-document and delete-document gates for admin only', function (string $role, bool $allowed) {
    $user = authUser($role);

    expect(Gate::forUser($user)->allows('update-document'))->toBe($allowed)
        ->and(Gate::forUser($user)->allows('delete-document'))->toBe($allowed);
})->with([
    ['admin', true],
    ['manager', false],
    ['staff', false],
]);

it('matches DocumentPolicy matrix for each role', function (string $role, bool $canCreate, bool $canUpdate) {
    $user = authUser($role);
    $document = Document::factory()->create();

    expect($user->can('viewAny', Document::class))->toBeTrue()
        ->and($user->can('view', $document))->toBeTrue()
        ->and($user->can('create', Document::class))->toBe($canCreate)
        ->and($user->can('update', $document))->toBe($canUpdate)
        ->and($user->can('delete', $document))->toBe($canUpdate);
})->with([
    ['admin', true, true],
    ['manager', true, false],
    ['staff', false, false],
]);
