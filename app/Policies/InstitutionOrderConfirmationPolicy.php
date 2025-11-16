<?php

declare(strict_types=1);

namespace App\Policies;

use Illuminate\Foundation\Auth\User as AuthUser;
use App\Models\InstitutionOrderConfirmation;
use Illuminate\Auth\Access\HandlesAuthorization;

class InstitutionOrderConfirmationPolicy
{
    use HandlesAuthorization;

    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:InstitutionOrderConfirmation');
    }

    public function view(AuthUser $authUser, InstitutionOrderConfirmation $institutionOrderConfirmation): bool
    {
        return $authUser->can('View:InstitutionOrderConfirmation');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:InstitutionOrderConfirmation');
    }

    public function update(AuthUser $authUser, InstitutionOrderConfirmation $institutionOrderConfirmation): bool
    {

        return $authUser->can('Update:InstitutionOrderConfirmation');
    }

    public function delete(AuthUser $authUser, InstitutionOrderConfirmation $institutionOrderConfirmation): bool
    {
        return $authUser->can('Delete:InstitutionOrderConfirmation');
    }

    public function restore(AuthUser $authUser, InstitutionOrderConfirmation $institutionOrderConfirmation): bool
    {
        return $authUser->can('Restore:InstitutionOrderConfirmation');
    }

    public function forceDelete(AuthUser $authUser, InstitutionOrderConfirmation $institutionOrderConfirmation): bool
    {
        return $authUser->can('ForceDelete:InstitutionOrderConfirmation');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:InstitutionOrderConfirmation');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:InstitutionOrderConfirmation');
    }

    public function replicate(AuthUser $authUser, InstitutionOrderConfirmation $institutionOrderConfirmation): bool
    {
        return $authUser->can('Replicate:InstitutionOrderConfirmation');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:InstitutionOrderConfirmation');
    }

}
