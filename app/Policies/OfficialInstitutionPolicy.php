<?php

declare(strict_types=1);

namespace App\Policies;

use Illuminate\Foundation\Auth\User as AuthUser;
use App\Models\OfficialInstitution;
use Illuminate\Auth\Access\HandlesAuthorization;

class OfficialInstitutionPolicy
{
    use HandlesAuthorization;
    
    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:OfficialInstitution');
    }

    public function view(AuthUser $authUser, OfficialInstitution $officialInstitution): bool
    {
        return $authUser->can('View:OfficialInstitution');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:OfficialInstitution');
    }

    public function update(AuthUser $authUser, OfficialInstitution $officialInstitution): bool
    {
        return $authUser->can('Update:OfficialInstitution');
    }

    public function delete(AuthUser $authUser, OfficialInstitution $officialInstitution): bool
    {
        return $authUser->can('Delete:OfficialInstitution');
    }

    public function restore(AuthUser $authUser, OfficialInstitution $officialInstitution): bool
    {
        return $authUser->can('Restore:OfficialInstitution');
    }

    public function forceDelete(AuthUser $authUser, OfficialInstitution $officialInstitution): bool
    {
        return $authUser->can('ForceDelete:OfficialInstitution');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:OfficialInstitution');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:OfficialInstitution');
    }

    public function replicate(AuthUser $authUser, OfficialInstitution $officialInstitution): bool
    {
        return $authUser->can('Replicate:OfficialInstitution');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:OfficialInstitution');
    }

}