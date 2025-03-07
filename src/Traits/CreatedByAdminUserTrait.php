<?php

declare(strict_types=1);

namespace Brackets\Craftable\Traits;

use Brackets\AdminAuth\Models\AdminUser;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

trait CreatedByAdminUserTrait
{
    public function createdByAdminUser(): BelongsTo
    {
        return $this->belongsTo(AdminUser::class, 'created_by_admin_user_id');
    }
}
