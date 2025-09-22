<?php

declare(strict_types=1);

namespace App\Policies;

use Illuminate\Foundation\Auth\User as AuthUser;
use App\Models\Abono;
use Illuminate\Auth\Access\HandlesAuthorization;

class AbonoPolicy
{
    use HandlesAuthorization;
    
    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:Abono');
    }

    public function view(AuthUser $authUser, Abono $abono): bool
    {
        return $authUser->can('View:Abono');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:Abono');
    }

    public function update(AuthUser $authUser, Abono $abono): bool
    {
        return $authUser->can('Update:Abono');
    }

    public function delete(AuthUser $authUser, Abono $abono): bool
    {
        return $authUser->can('Delete:Abono');
    }

    public function restore(AuthUser $authUser, Abono $abono): bool
    {
        return $authUser->can('Restore:Abono');
    }

    public function forceDelete(AuthUser $authUser, Abono $abono): bool
    {
        return $authUser->can('ForceDelete:Abono');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:Abono');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:Abono');
    }

    public function replicate(AuthUser $authUser, Abono $abono): bool
    {
        return $authUser->can('Replicate:Abono');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:Abono');
    }

}