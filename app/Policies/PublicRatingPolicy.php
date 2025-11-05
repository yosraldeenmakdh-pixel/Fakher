<?php

declare(strict_types=1);

namespace App\Policies;

use Illuminate\Foundation\Auth\User as AuthUser;
use App\Models\PublicRating;
use Illuminate\Auth\Access\HandlesAuthorization;

class PublicRatingPolicy
{
    use HandlesAuthorization;
    
    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:PublicRating');
    }

    public function view(AuthUser $authUser, PublicRating $publicRating): bool
    {
        return $authUser->can('View:PublicRating');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:PublicRating');
    }

    public function update(AuthUser $authUser, PublicRating $publicRating): bool
    {
        return $authUser->can('Update:PublicRating');
    }

    public function delete(AuthUser $authUser, PublicRating $publicRating): bool
    {
        return $authUser->can('Delete:PublicRating');
    }

    public function restore(AuthUser $authUser, PublicRating $publicRating): bool
    {
        return $authUser->can('Restore:PublicRating');
    }

    public function forceDelete(AuthUser $authUser, PublicRating $publicRating): bool
    {
        return $authUser->can('ForceDelete:PublicRating');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:PublicRating');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:PublicRating');
    }

    public function replicate(AuthUser $authUser, PublicRating $publicRating): bool
    {
        return $authUser->can('Replicate:PublicRating');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:PublicRating');
    }

}