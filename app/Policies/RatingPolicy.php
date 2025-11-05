<?php

declare(strict_types=1);

namespace App\Policies;

use Illuminate\Foundation\Auth\User as AuthUser;
use App\Models\Rating;
use Illuminate\Auth\Access\HandlesAuthorization;

class RatingPolicy
{
    use HandlesAuthorization;
    
    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:Rating');
    }

    public function view(AuthUser $authUser, Rating $rating): bool
    {
        return $authUser->can('View:Rating');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:Rating');
    }

    public function update(AuthUser $authUser, Rating $rating): bool
    {
        return $authUser->can('Update:Rating');
    }

    public function delete(AuthUser $authUser, Rating $rating): bool
    {
        return $authUser->can('Delete:Rating');
    }

    public function restore(AuthUser $authUser, Rating $rating): bool
    {
        return $authUser->can('Restore:Rating');
    }

    public function forceDelete(AuthUser $authUser, Rating $rating): bool
    {
        return $authUser->can('ForceDelete:Rating');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:Rating');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:Rating');
    }

    public function replicate(AuthUser $authUser, Rating $rating): bool
    {
        return $authUser->can('Replicate:Rating');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:Rating');
    }

}