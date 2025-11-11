<?php

declare(strict_types=1);

namespace App\Policies;

use Illuminate\Foundation\Auth\User as AuthUser;
use App\Models\ContactSetting;
use Illuminate\Auth\Access\HandlesAuthorization;

class ContactSettingPolicy
{
    use HandlesAuthorization;
    
    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:ContactSetting');
    }

    public function view(AuthUser $authUser, ContactSetting $contactSetting): bool
    {
        return $authUser->can('View:ContactSetting');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:ContactSetting');
    }

    public function update(AuthUser $authUser, ContactSetting $contactSetting): bool
    {
        return $authUser->can('Update:ContactSetting');
    }

    public function delete(AuthUser $authUser, ContactSetting $contactSetting): bool
    {
        return $authUser->can('Delete:ContactSetting');
    }

    public function restore(AuthUser $authUser, ContactSetting $contactSetting): bool
    {
        return $authUser->can('Restore:ContactSetting');
    }

    public function forceDelete(AuthUser $authUser, ContactSetting $contactSetting): bool
    {
        return $authUser->can('ForceDelete:ContactSetting');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:ContactSetting');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:ContactSetting');
    }

    public function replicate(AuthUser $authUser, ContactSetting $contactSetting): bool
    {
        return $authUser->can('Replicate:ContactSetting');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:ContactSetting');
    }

}