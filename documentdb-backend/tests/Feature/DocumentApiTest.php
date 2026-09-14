<?php

use App\Models\Document;
use App\Models\User;
use Database\Seeders\RoleSeeder;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Spatie\Permission\PermissionRegistrar;

beforeEach(function () {
    $this->seed(RoleSeeder::class);
    app(PermissionRegistrar::class)->forgetCachedPermissions();
});

function userWithRole(string $role): User
{
    return User::factory()->create()->assignRole($role);
}

it('rejects unauthenticated access to documents', function () {
    $this->getJson(route('documents.index'))->assertUnauthorized();
});

it('lets an authenticated user list documents', function () {
    Document::factory()->count(2)->create();

    $this->actingAs(userWithRole('staff'), 'sanctum')
        ->getJson(route('documents.index'))
        ->assertOk()
        ->assertJsonCount(2, 'data');
});

it('creates a document for admin and manager', function (string $role) {
    Storage::fake('r2');

    $user = userWithRole($role);
    $file = UploadedFile::fake()->create('report.pdf', 100, 'application/pdf');

    $this->actingAs($user, 'sanctum')
        ->post(route('documents.store'), [
            'title' => 'Quarterly Revenue Report',
            'description' => 'Revenue breakdown for Q3.',
            'document' => $file,
        ], ['Accept' => 'application/json'])
        ->assertCreated()
        ->assertJsonPath('data.user.id', $user->id);

    expect(Document::sole()->user_id)->toBe($user->id);
})->with(['admin', 'manager']);

it('forbids staff from creating a document', function () {
    Storage::fake('r2');

    $file = UploadedFile::fake()->create('report.pdf', 100, 'application/pdf');

    $this->actingAs(userWithRole('staff'), 'sanctum')
        ->post(route('documents.store'), [
            'title' => 'Quarterly Revenue Report',
            'description' => 'Revenue breakdown for Q3.',
            'document' => $file,
        ], ['Accept' => 'application/json'])
        ->assertForbidden();

    expect(Document::count())->toBe(0);
});

it('rejects a document with a title shorter than six characters', function () {
    $this->actingAs(userWithRole('manager'), 'sanctum')
        ->postJson(route('documents.store'), [
            'title' => 'Short',
            'description' => 'A valid description.',
        ])
        ->assertJsonValidationErrors('title');
});

it('includes a file url when the document has an uploaded file', function () {
    config(['filesystems.disks.r2.url' => 'https://files.example.com']);

    $document = Document::factory()->create([
        'document_key' => 'documents/report.pdf',
    ]);

    $this->actingAs(userWithRole('staff'), 'sanctum')
        ->getJson(route('documents.show', $document))
        ->assertOk()
        ->assertJsonPath('data.file_url', 'https://files.example.com/documents/report.pdf');
});

it('returns a null file url when no file was uploaded', function () {
    $document = Document::factory()->create([
        'document_key' => null,
    ]);

    $this->actingAs(userWithRole('staff'), 'sanctum')
        ->getJson(route('documents.show', $document))
        ->assertOk()
        ->assertJsonPath('data.file_url', null);
});

it('lets admin delete a document', function () {
    $document = Document::factory()->create();

    $this->actingAs(userWithRole('admin'), 'sanctum')
        ->deleteJson(route('documents.destroy', $document))
        ->assertNoContent();

    expect(Document::count())->toBe(0);
});

it('forbids manager and staff from deleting a document', function (string $role) {
    $document = Document::factory()->create();

    $this->actingAs(userWithRole($role), 'sanctum')
        ->deleteJson(route('documents.destroy', $document))
        ->assertForbidden();

    expect(Document::count())->toBe(1);
})->with(['manager', 'staff']);

it('lets admin update a document', function () {
    Storage::fake('r2');
    $document = Document::factory()->create();

    $this->actingAs(userWithRole('admin'), 'sanctum')
        ->putJson(route('documents.update', $document), [
            'title' => 'Updated Document Title',
            'description' => 'Updated description text.',
        ])
        ->assertOk()
        ->assertJsonPath('data.title', 'Updated Document Title');
});

it('forbids manager and staff from updating a document', function (string $role) {
    $document = Document::factory()->create([
        'title' => 'Original Document Title',
    ]);

    $this->actingAs(userWithRole($role), 'sanctum')
        ->putJson(route('documents.update', $document), [
            'title' => 'Updated Document Title',
            'description' => 'Updated description text.',
        ])
        ->assertForbidden();

    expect($document->fresh()->title)->toBe('Original Document Title');
})->with(['manager', 'staff']);
