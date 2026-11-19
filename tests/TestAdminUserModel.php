<?php

declare(strict_types=1);

namespace Brackets\Craftable\Tests;

use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Foundation\Auth\User as Authenticatable;

/**
 * The admin model of the default-permissions migration in tests.
 */
#[Table('test_admin_users')]
final class TestAdminUserModel extends Authenticatable
{
}
