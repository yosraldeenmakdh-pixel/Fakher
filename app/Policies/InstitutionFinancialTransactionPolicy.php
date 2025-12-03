<?php

declare(strict_types=1);

namespace App\Policies;

use Illuminate\Foundation\Auth\User as AuthUser;
use App\Models\InstitutionFinancialTransaction;
use Illuminate\Auth\Access\HandlesAuthorization;

class InstitutionFinancialTransactionPolicy
{
    use HandlesAuthorization;
    
    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:InstitutionFinancialTransaction');
    }

    public function view(AuthUser $authUser, InstitutionFinancialTransaction $institutionFinancialTransaction): bool
    {
        return $authUser->can('View:InstitutionFinancialTransaction');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:InstitutionFinancialTransaction');
    }

    public function update(AuthUser $authUser, InstitutionFinancialTransaction $institutionFinancialTransaction): bool
    {
        return $authUser->can('Update:InstitutionFinancialTransaction');
    }

    public function delete(AuthUser $authUser, InstitutionFinancialTransaction $institutionFinancialTransaction): bool
    {
        return $authUser->can('Delete:InstitutionFinancialTransaction');
    }

    public function restore(AuthUser $authUser, InstitutionFinancialTransaction $institutionFinancialTransaction): bool
    {
        return $authUser->can('Restore:InstitutionFinancialTransaction');
    }

    public function forceDelete(AuthUser $authUser, InstitutionFinancialTransaction $institutionFinancialTransaction): bool
    {
        return $authUser->can('ForceDelete:InstitutionFinancialTransaction');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:InstitutionFinancialTransaction');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:InstitutionFinancialTransaction');
    }

    public function replicate(AuthUser $authUser, InstitutionFinancialTransaction $institutionFinancialTransaction): bool
    {
        return $authUser->can('Replicate:InstitutionFinancialTransaction');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:InstitutionFinancialTransaction');
    }

}