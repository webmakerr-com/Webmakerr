<?php

namespace FluentCartPro\App\Models;

use Webmakerr\App\Services\Permission\PermissionManager;
use Webmakerr\App\Models\User as FluentCartUser;

class User extends FluentCartUser
{
    public function capabilities(): \Webmakerr\Framework\Database\Orm\Relations\HasOne
    {
        return $this->hasOne(UserMeta::class, 'user_id', 'ID')
            ->where('meta_key', 'wp_capabilities');
    }

    public function adminRole(): \Webmakerr\Framework\Database\Orm\Relations\HasOne
    {
        return $this->hasOne(UserMeta::class, 'user_id', 'ID')
            ->where('meta_key', PermissionManager::$metaKey);
    }
}