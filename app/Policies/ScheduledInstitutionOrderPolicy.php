<?php

declare(strict_types=1);

namespace App\Policies;

use Illuminate\Foundation\Auth\User as AuthUser;
use App\Models\ScheduledInstitutionOrder;
use Illuminate\Auth\Access\HandlesAuthorization;

class ScheduledInstitutionOrderPolicy
{
    use HandlesAuthorization;

    public function viewAny(AuthUser $authUser): bool
    {
        // للمستخدمين من نوع institution
        if($authUser->hasRole('institution')) {

            if($authUser->officialInstitution && $authUser->officialInstitution->institution_type == 'scheduled') {
                return $authUser->can('ViewAny:ScheduledInstitutionOrder');
            }
            return false;
        }

        // للمشرفين
        if($authUser->hasRole('super_admin')) {
            return $authUser->can('ViewAny:ScheduledInstitutionOrder');
        }
        if($authUser->hasRole('kitchen')) {
            return $authUser->can('ViewAny:ScheduledInstitutionOrder');
        }

        return false;
    }

    public function view(AuthUser $authUser, ScheduledInstitutionOrder $scheduledInstitutionOrder): bool
    {
        return $authUser->can('View:ScheduledInstitutionOrder');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:ScheduledInstitutionOrder');
    }

    public function update(AuthUser $authUser, ScheduledInstitutionOrder $scheduledInstitutionOrder): bool
    {
        return $authUser->can('Update:ScheduledInstitutionOrder');
    }

    public function delete(AuthUser $authUser, ScheduledInstitutionOrder $scheduledInstitutionOrder): bool
    {
        return $authUser->can('Delete:ScheduledInstitutionOrder');
    }

    public function restore(AuthUser $authUser, ScheduledInstitutionOrder $scheduledInstitutionOrder): bool
    {
        return $authUser->can('Restore:ScheduledInstitutionOrder');
    }

    public function forceDelete(AuthUser $authUser, ScheduledInstitutionOrder $scheduledInstitutionOrder): bool
    {
        return $authUser->can('ForceDelete:ScheduledInstitutionOrder');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:ScheduledInstitutionOrder');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:ScheduledInstitutionOrder');
    }

    public function replicate(AuthUser $authUser, ScheduledInstitutionOrder $scheduledInstitutionOrder): bool
    {
        return $authUser->can('Replicate:ScheduledInstitutionOrder');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:ScheduledInstitutionOrder');
    }

}
