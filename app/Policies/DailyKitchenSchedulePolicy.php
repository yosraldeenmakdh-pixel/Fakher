<?php

declare(strict_types=1);

namespace App\Policies;

use Illuminate\Foundation\Auth\User as AuthUser;
use App\Models\DailyKitchenSchedule;
use Illuminate\Auth\Access\HandlesAuthorization;

class DailyKitchenSchedulePolicy
{
    use HandlesAuthorization;
    
    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:DailyKitchenSchedule');
    }

    public function view(AuthUser $authUser, DailyKitchenSchedule $dailyKitchenSchedule): bool
    {
        return $authUser->can('View:DailyKitchenSchedule');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:DailyKitchenSchedule');
    }

    public function update(AuthUser $authUser, DailyKitchenSchedule $dailyKitchenSchedule): bool
    {
        return $authUser->can('Update:DailyKitchenSchedule');
    }

    public function delete(AuthUser $authUser, DailyKitchenSchedule $dailyKitchenSchedule): bool
    {
        return $authUser->can('Delete:DailyKitchenSchedule');
    }

    public function restore(AuthUser $authUser, DailyKitchenSchedule $dailyKitchenSchedule): bool
    {
        return $authUser->can('Restore:DailyKitchenSchedule');
    }

    public function forceDelete(AuthUser $authUser, DailyKitchenSchedule $dailyKitchenSchedule): bool
    {
        return $authUser->can('ForceDelete:DailyKitchenSchedule');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:DailyKitchenSchedule');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:DailyKitchenSchedule');
    }

    public function replicate(AuthUser $authUser, DailyKitchenSchedule $dailyKitchenSchedule): bool
    {
        return $authUser->can('Replicate:DailyKitchenSchedule');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:DailyKitchenSchedule');
    }

}