<?php

declare(strict_types=1);

namespace App\Policies;

use Illuminate\Foundation\Auth\User as AuthUser;
use App\Models\QuoteModel;
use Illuminate\Auth\Access\HandlesAuthorization;

class QuoteModelPolicy
{
    use HandlesAuthorization;
    
    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:QuoteModel');
    }

    public function view(AuthUser $authUser, QuoteModel $quoteModel): bool
    {
        return $authUser->can('View:QuoteModel');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:QuoteModel');
    }

    public function update(AuthUser $authUser, QuoteModel $quoteModel): bool
    {
        return $authUser->can('Update:QuoteModel');
    }

    public function delete(AuthUser $authUser, QuoteModel $quoteModel): bool
    {
        return $authUser->can('Delete:QuoteModel');
    }

    public function restore(AuthUser $authUser, QuoteModel $quoteModel): bool
    {
        return $authUser->can('Restore:QuoteModel');
    }

    public function forceDelete(AuthUser $authUser, QuoteModel $quoteModel): bool
    {
        return $authUser->can('ForceDelete:QuoteModel');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:QuoteModel');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:QuoteModel');
    }

    public function replicate(AuthUser $authUser, QuoteModel $quoteModel): bool
    {
        return $authUser->can('Replicate:QuoteModel');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:QuoteModel');
    }

}