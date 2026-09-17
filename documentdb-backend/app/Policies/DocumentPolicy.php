<?php

namespace App\Policies;

use App\Models\Document;
use App\Models\User;

/**
 * Policy = peraturan authorization yang terikat pada model Document.
 * Matriks: admin = penuh | manager = cipta + lihat | staff = lihat sahaja.
 */
class DocumentPolicy
{
    /**
     * Senarai dokumen — semua peranan yang log masuk dibenarkan.
     */
    public function viewAny(User $user): bool
    {
        return $user->hasAnyRole(['admin', 'manager', 'staff']);
    }

    /**
     * Lihat satu dokumen — semua peranan dibenarkan.
     */
    public function view(User $user, Document $document): bool
    {
        // return $user->id == $document->user_id;
        return $user->hasAnyRole(['admin', 'manager', 'staff']);
    }

    /**
     * Cipta dokumen — admin & manager sahaja.
     */
    public function create(User $user): bool
    {
        return $user->hasAnyRole(['admin', 'manager']);
    }

    /**
     * Kemaskini dokumen — admin sahaja.
     */
    public function update(User $user, Document $document): bool
    {
        return $user->hasRole('admin');
    }

    /**
     * Padam dokumen — admin sahaja.
     */
    public function delete(User $user, Document $document): bool
    {
        return $user->hasRole('admin');
    }
}
