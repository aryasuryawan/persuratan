<?php

namespace App\Policies;

use App\Models\NotulensiRapim;
use App\Models\User;

class NotulensiRapimPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasPermission('notulensi-rapim.index');
    }

    public function view(User $user, NotulensiRapim $rapim): bool
    {
        if (! $rapim->is_rahasia) {
            return $user->hasPermission('notulensi-rapim.show');
        }

        if ($user->hasRole('admin') || $user->hasRole('sekretaris')) {
            return true;
        }

        return $rapim->punyaAksesManual($user);
    }

    public function create(User $user): bool
    {
        return $user->hasPermission('notulensi-rapim.create');
    }

    public function update(User $user, NotulensiRapim $rapim): bool
    {
        return $user->hasPermission('notulensi-rapim.edit');
    }

    public function delete(User $user, NotulensiRapim $rapim): bool
    {
        return $user->isCreatorOrAdmin($rapim->dicatat_oleh_id);
    }

    public function assignAkses(User $user, NotulensiRapim $rapim): bool
    {
        return $user->hasPermission('notulensi-rapim.assign-akses');
    }
}