<?php

namespace Webmakerr\App\Http\Policies;

use Webmakerr\App\App;
use Webmakerr\App\Helpers\Helper;
use Webmakerr\Framework\Foundation\Policy as BasePolicy;
use Webmakerr\Framework\Support\Arr;

class Policy extends BasePolicy
{

    public function userCan($permission): bool
    {
        $currentUser = Helper::getCurrentUser();

        return $currentUser && $currentUser->userCan($permission);
    }

    public function userCanAny($permission): bool
    {
        $currentUser = Helper::getCurrentUser();
        return $currentUser && $currentUser->userCanAny($permission);
    }

    public function resolveRoutePermission(): array
    {
        $meta = App::route()->getMeta();
        $requiredPermission = Arr::get($meta, 'permissions');
        if (is_array($requiredPermission)) {
            $permissions = $requiredPermission;
        } elseif (is_string($requiredPermission)) {
            $permissions = array_map('trim', explode(',', $requiredPermission));
        } else {
            $permissions = [];
        }
        return $permissions;
    }

    public function hasRoutePermissions(): bool
    {
        $meta = App::route()->getMeta();
        $permissionType = Arr::get($meta, 'permissions_type', 'any');
        $requiredPermission = $this->resolveRoutePermission();



        if (empty($requiredPermission)) {
            return true;
        }


        if ($permissionType === 'any') {
            return $this->userCanAny($requiredPermission);
        } else {
            return $this->userCan($requiredPermission);
        }
    }
}
