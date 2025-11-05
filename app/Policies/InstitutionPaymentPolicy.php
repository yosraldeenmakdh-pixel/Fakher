<?php

declare(strict_types=1);

namespace App\Policies;

use Illuminate\Foundation\Auth\User as AuthUser;
use App\Models\InstitutionPayment;
use Illuminate\Auth\Access\HandlesAuthorization;

class InstitutionPaymentPolicy
{
    use HandlesAuthorization;
    
    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:InstitutionPayment');
    }

    public function view(AuthUser $authUser, InstitutionPayment $institutionPayment): bool
    {
        return $authUser->can('View:InstitutionPayment');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:InstitutionPayment');
    }

    public function update(AuthUser $authUser, InstitutionPayment $institutionPayment): bool
    {
        return $authUser->can('Update:InstitutionPayment');
    }

    public function delete(AuthUser $authUser, InstitutionPayment $institutionPayment): bool
    {
        return $authUser->can('Delete:InstitutionPayment');
    }

    public function restore(AuthUser $authUser, InstitutionPayment $institutionPayment): bool
    {
        return $authUser->can('Restore:InstitutionPayment');
    }

    public function forceDelete(AuthUser $authUser, InstitutionPayment $institutionPayment): bool
    {
        return $authUser->can('ForceDelete:InstitutionPayment');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:InstitutionPayment');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:InstitutionPayment');
    }

    public function replicate(AuthUser $authUser, InstitutionPayment $institutionPayment): bool
    {
        return $authUser->can('Replicate:InstitutionPayment');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:InstitutionPayment');
    }

}