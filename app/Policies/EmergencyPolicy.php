<?php

declare(strict_types=1);

namespace App\Policies;

use Illuminate\Foundation\Auth\User as AuthUser;
use App\Models\Emergency;
use Illuminate\Auth\Access\HandlesAuthorization;

class EmergencyPolicy
{
    use HandlesAuthorization;

    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:Emergency');
    }

    public function view(AuthUser $authUser, Emergency $emergency): bool
    {
        return $authUser->can('View:Emergency');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:Emergency');
    }

    public function update(AuthUser $authUser, Emergency $emergency): bool
    {

        if ($authUser->hasRole('institution')) {
            if($emergency->status === 'pending'){
                return $authUser->can('Update:Emergency');
            }
            else
                return $authUser->can('');

        }

        return $authUser->can('Update:Emergency');
    }

    public function delete(AuthUser $authUser, Emergency $emergency): bool
    {

        if ($authUser->hasRole('institution')) {
            if($emergency->status === 'pending'){
                return $authUser->can('Delete:Emergency');
            }
            else
                return $authUser->can('');

        }

        return $authUser->can('Delete:Emergency');
    }

    public function restore(AuthUser $authUser, Emergency $emergency): bool
    {
        return $authUser->can('Restore:Emergency');
    }

    public function forceDelete(AuthUser $authUser, Emergency $emergency): bool
    {
        return $authUser->can('ForceDelete:Emergency');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:Emergency');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:Emergency');
    }

    public function replicate(AuthUser $authUser, Emergency $emergency): bool
    {
        return $authUser->can('Replicate:Emergency');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:Emergency');
    }

}
