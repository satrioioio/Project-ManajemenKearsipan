<?php

namespace App\Policies;

use App\Models\Document;
use App\Models\User;

class DocumentPolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, Document $document): bool
    {
        return $document->isAccessibleBy($user);
    }

    public function create(User $user): bool
    {
        return $user->canManageDocuments();
    }

    public function update(User $user, Document $document): bool
    {
        if ($document->is_locked) {
            return false;
        }

        if ($user->isAdmin() || $user->isLegal()) {
            return true;
        }

        return $user->isPm() && $document->created_by === $user->id;
    }

    public function delete(User $user, Document $document): bool
    {
        if ($document->is_locked) {
            return false;
        }

        return $user->isAdmin() || $user->isLegal();
    }

    public function download(User $user, Document $document): bool
    {
        return $document->isDownloadableBy($user);
    }

    public function lock(User $user, Document $document): bool
    {
        return $user->isAdmin() || $user->isLegal();
    }
}
