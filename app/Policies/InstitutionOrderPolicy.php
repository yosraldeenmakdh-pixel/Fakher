<?php

declare(strict_types=1);

namespace App\Policies;

use Illuminate\Foundation\Auth\User as AuthUser;
use App\Models\InstitutionOrder;
use Illuminate\Auth\Access\HandlesAuthorization;

class InstitutionOrderPolicy
{
    use HandlesAuthorization;
    
    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:InstitutionOrder');
    }

    public function view(AuthUser $authUser, InstitutionOrder $institutionOrder): bool
    {
        return $authUser->can('View:InstitutionOrder');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:InstitutionOrder');
    }

    public function update(AuthUser $authUser, InstitutionOrder $institutionOrder): bool
    {
        return $authUser->can('Update:InstitutionOrder');
    }

    public function delete(AuthUser $authUser, InstitutionOrder $institutionOrder): bool
    {
        return $authUser->can('Delete:InstitutionOrder');
    }

    public function restore(AuthUser $authUser, InstitutionOrder $institutionOrder): bool
    {
        return $authUser->can('Restore:InstitutionOrder');
    }

    public function forceDelete(AuthUser $authUser, InstitutionOrder $institutionOrder): bool
    {
        return $authUser->can('ForceDelete:InstitutionOrder');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:InstitutionOrder');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:InstitutionOrder');
    }

    public function replicate(AuthUser $authUser, InstitutionOrder $institutionOrder): bool
    {
        return $authUser->can('Replicate:InstitutionOrder');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:InstitutionOrder');
    }

}