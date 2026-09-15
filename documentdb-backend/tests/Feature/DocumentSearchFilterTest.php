<?php

use App\Models\Category;
use App\Models\Document;
use App\Models\User;
use Database\Seeders\RoleSeeder;
use Spatie\Permission\PermissionRegistrar;

beforeEach(function () {
    $this->seed(RoleSeeder::class);
    app(PermissionRegistrar::class)->forgetCachedPermissions();
});

function searchUserWithRole(string $role): User
{
    return User::factory()->create()->assignRole($role);
}

it('filters documents by search term on title', function () {
    Document::factory()->create(['title' => 'Quarterly Revenue Report']);
    Document::factory()->create(['title' => 'Employee Handbook']);

    $this->actingAs(searchUserWithRole('staff'), 'sanctum')
        ->getJson(route('documents.index', ['search' => 'Revenue']))
        ->assertOk()
        ->assertJsonCount(1, 'data')
        ->assertJsonPath('data.0.title', 'Quarterly Revenue Report');
});

it('filters documents by category_id', function () {
    $policies = Category::factory()->create(['name' => 'Policies']);
    $reports = Category::factory()->create(['name' => 'Reports']);

    Document::factory()->create([
        'title' => 'Leave Policy Document',
        'category_id' => $policies->id,
    ]);
    Document::factory()->create([
        'title' => 'Monthly Sales Report',
        'category_id' => $reports->id,
    ]);

    $this->actingAs(searchUserWithRole('staff'), 'sanctum')
        ->getJson(route('documents.index', ['category_id' => $policies->id]))
        ->assertOk()
        ->assertJsonCount(1, 'data')
        ->assertJsonPath('data.0.title', 'Leave Policy Document');
});

it('combines search and category_id filters', function () {
    $reports = Category::factory()->create(['name' => 'Reports']);
    $policies = Category::factory()->create(['name' => 'Policies']);

    Document::factory()->create([
        'title' => 'Laptop Inventory Report',
        'category_id' => $reports->id,
    ]);
    Document::factory()->create([
        'title' => 'Laptop Purchase Policy',
        'category_id' => $policies->id,
    ]);
    Document::factory()->create([
        'title' => 'Desktop Inventory Report',
        'category_id' => $reports->id,
    ]);

    $this->actingAs(searchUserWithRole('staff'), 'sanctum')
        ->getJson(route('documents.index', [
            'search' => 'Laptop',
            'category_id' => $reports->id,
        ]))
        ->assertOk()
        ->assertJsonCount(1, 'data')
        ->assertJsonPath('data.0.title', 'Laptop Inventory Report');
});

it('lists categories for authenticated users', function () {
    Category::factory()->create(['name' => 'Training']);
    Category::factory()->create(['name' => 'Contracts']);

    $this->actingAs(searchUserWithRole('staff'), 'sanctum')
        ->getJson(route('categories.index'))
        ->assertOk()
        ->assertJsonCount(2, 'data')
        ->assertJsonFragment(['name' => 'Contracts'])
        ->assertJsonFragment(['name' => 'Training']);
});

it('rejects unauthenticated access to categories', function () {
    $this->getJson(route('categories.index'))->assertUnauthorized();
});
