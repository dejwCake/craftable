<?php

declare(strict_types=1);

namespace Brackets\Craftable\Traits;

use Brackets\AdminAuth\Models\AdminUser;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

trait UpdatedByAdminUserTrait
{
    public function updatedByAdminUser(): BelongsTo
    {
        return $this->belongsTo(AdminUser::class, 'updated_by_admin_user_id');
    }
}
