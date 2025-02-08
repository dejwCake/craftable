<?php

declare(strict_types=1);

use Carbon\CarbonImmutable;
use Carbon\CarbonInterface;
use Illuminate\Contracts\Cache\Repository as Cache;
use Illuminate\Contracts\Config\Repository as Config;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Hashing\HashManager;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    protected Config $config;
    protected Cache $cache;
    protected HashManager $hashManager;
    protected string $guardName;

    /** @var class-string */
    protected string $userClassName;
    protected string $userTable;

    /** @var array<array<string, string|CarbonInterface>> */
    protected array $permissions;

    /** @var array<array<string, string|CarbonInterface|Collection<string>>> */
    protected array $roles;

    /** @var array<array<string, string|CarbonInterface|bool|Collection<string>|array>> */
    protected array $users;

    protected string $password = 'best package ever';

    public function __construct()
    {
        $this->config = app(Config::class);
        $this->cache = app(Cache::class);
        $this->hashManager = app(HashManager::class);
        $this->guardName = $this->config->get('admin-auth.defaults.guard');
        $providerName = $this->config->get('auth.guards.' . $this->guardName . '.provider');
        $provider = $this->config->get('auth.providers.' . $providerName);
        if ($provider['driver'] !== 'eloquent') {
            throw new RuntimeException('Only Eloquent user provider is supported');
        }
        if ($provider['model'] === null) {
            throw new RuntimeException('Admin user model not defined');
        }
        $this->userClassName = $provider['model'];
        $this->userTable = (new $this->userClassName())->getTable();

        $this->preparePermissions();
        $this->prepareRoles();
        $this->prepareUsers();
    }

    /**
     * Run the migrations.
     *
     * @throws Exception
     */
    public function up(): void
    {
        DB::transaction(function (): void {
            $this->upPermissions();
            $this->upRoles();
            $this->upUsers();
        });

        $this->cache->forget($this->config->get('permission.cache.key'));
    }

    /**
     * Reverse the migrations.
     *
     * @throws Exception
     */
    public function down(): void
    {
        DB::transaction(function (): void {
            $this->downUsers();
            $this->downRoles();
            $this->downPermissions();
        });

        $this->cache->forget($this->config->get('permission.cache.key'));
    }

    private function prepareUsers(): void
    {
        $this->users = [
            [
                'first_name' => 'Administrator',
                'last_name' => 'Administrator',
                'email' => 'admin@getcraftable.com',
                'password' => $this->hashManager->make($this->password),
                'remember_token' => null,
                'created_at' => CarbonImmutable::now(),
                'updated_at' => CarbonImmutable::now(),
                'activated' => true,
                'roles' => [
                    [
                        'name' => 'Administrator',
                        'guard_name' => $this->guardName,
                    ],
                ],
                'permissions' => [
                ],
            ],
        ];
    }

    private function prepareRoles(): void
    {
        $this->roles = [
            [
                'name' => 'Administrator',
                'guard_name' => $this->guardName,
                'created_at' => CarbonImmutable::now(),
                'updated_at' => CarbonImmutable::now(),
                'permissions' => (new Collection($this->permissions))
                    ->map(static fn (array $permission) => $permission['name'])
                    ->reject(static fn ($permission) => $permission === 'admin.admin-user.impersonal-login'),
            ],
        ];
    }

    private function preparePermissions(): void
    {
        $defaultPermissions = new Collection([
            // view admin as a whole
            'admin',

            // manage translations
            'admin.translation.index',
            'admin.translation.edit',
            'admin.translation.rescan',

            // manage users (access)
            'admin.admin-user.index',
            'admin.admin-user.create',
            'admin.admin-user.edit',
            'admin.admin-user.delete',

            // ability to upload
            'admin.upload',

            //ability to impersonal login
            'admin.admin-user.impersonal-login',
        ]);

        //Add new permissions
        $this->permissions = $defaultPermissions->map(fn ($permission) => [
            'name' => $permission,
            'guard_name' => $this->guardName,
            'created_at' => CarbonImmutable::now(),
            'updated_at' => CarbonImmutable::now(),
        ])->toArray();
    }

    private function upPermissions(): void
    {
        foreach ($this->permissions as $permission) {
            $permissionItem = DB::table('permissions')->where([
                'name' => $permission['name'],
                'guard_name' => $permission['guard_name'],
            ])->first();
            if ($permissionItem === null) {
                DB::table('permissions')->insert($permission);
            }
        }
    }

    private function upRoles(): void
    {
        foreach ($this->roles as $role) {
            $permissions = $role['permissions'];
            unset($role['permissions']);

            $roleItem = DB::table('roles')->where([
                'name' => $role['name'],
                'guard_name' => $role['guard_name'],
            ])->first();
            $roleId = $roleItem === null ? DB::table('roles')->insertGetId($role) : $roleItem->id;

            $permissionItems = DB::table('permissions')
                ->whereIn('name', $permissions)
                ->where('guard_name', $role['guard_name'])->get();
            foreach ($permissionItems as $permissionItem) {
                $roleHasPermissionData = [
                    'permission_id' => $permissionItem->id,
                    'role_id' => $roleId,
                ];
                $roleHasPermissionItem = DB::table('role_has_permissions')
                    ->where($roleHasPermissionData)->first();
                if ($roleHasPermissionItem === null) {
                    DB::table('role_has_permissions')->insert($roleHasPermissionData);
                }
            }
        }
    }

    private function upUsers(): void
    {
        foreach ($this->users as $user) {
            $roles = $user['roles'];
            unset($user['roles']);

            $permissions = $user['permissions'];
            unset($user['permissions']);

            $userItem = DB::table($this->userTable)->where([
                'email' => $user['email'],
            ])->first();

            if ($userItem === null) {
                $userId = DB::table($this->userTable)->insertGetId($user);

                try {
                    $this->userClassName::find($userId)->addMedia(storage_path() . '/images/avatar.png')
                        ->preservingOriginal()
                        ->toMediaCollection('avatar', 'media');
                } catch (Throwable) {
                    // do nothing
                }

                $this->upUserRoles($roles, $userId);
                $this->upUserPermissions($permissions, $userId);
            }
        }
    }

    /** @param array<array<string, string|CarbonInterface|Collection<string>>> $roles */
    private function upUserRoles(array $roles, int $userId): void
    {
        foreach ($roles as $role) {
            $roleItem = DB::table('roles')->where([
                'name' => $role['name'],
                'guard_name' => $role['guard_name'],
            ])->first();

            $modelHasRoleData = [
                'role_id' => $roleItem->id,
                'model_id' => $userId,
                'model_type' => $this->userClassName,
            ];
            $modelHasRoleItem = DB::table('model_has_roles')->where($modelHasRoleData)->first();
            if ($modelHasRoleItem === null) {
                DB::table('model_has_roles')->insert($modelHasRoleData);
            }
        }
    }

    /** @param array<array<string, string|CarbonInterface>> $permissions */
    private function upUserPermissions(array $permissions, int $userId): void
    {
        foreach ($permissions as $permission) {
            $permissionItem = DB::table('permissions')->where([
                'name' => $permission['name'],
                'guard_name' => $permission['guard_name'],
            ])->first();

            $modelHasPermissionData = [
                'permission_id' => $permissionItem->id,
                'model_id' => $userId,
                'model_type' => $this->userClassName,
            ];
            $modelHasPermissionItem = DB::table('model_has_permissions')->where($modelHasPermissionData)->first();
            if ($modelHasPermissionItem === null) {
                DB::table('model_has_permissions')->insert($modelHasPermissionData);
            }
        }
    }

    private function downUsers(): void
    {
        foreach ($this->users as $user) {
            $userItem = DB::table($this->userTable)->where('email', $user['email'])->first();
            if ($userItem !== null) {
                try {
                    $this->userClassName::find($userItem->id)->media()->delete();
                } catch (Throwable) {
                    // do nothing
                }
                DB::table($this->userTable)->where('id', $userItem->id)->delete();
                DB::table('model_has_permissions')->where([
                    'model_id' => $userItem->id,
                    'model_type' => $this->userClassName,
                ])->delete();
                DB::table('model_has_roles')->where([
                    'model_id' => $userItem->id,
                    'model_type' => $this->userClassName,
                ])->delete();
            }
        }
    }

    private function downRoles(): void
    {
        foreach ($this->roles as $role) {
            $roleItem = DB::table('roles')->where([
                'name' => $role['name'],
                'guard_name' => $role['guard_name'],
            ])->first();
            if ($roleItem !== null) {
                DB::table('roles')->where('id', $roleItem->id)->delete();
                DB::table('model_has_roles')->where('role_id', $roleItem->id)->delete();
            }
        }
    }

    private function downPermissions(): void
    {
        foreach ($this->permissions as $permission) {
            $permissionItem = DB::table('permissions')->where([
                'name' => $permission['name'],
                'guard_name' => $permission['guard_name'],
            ])->first();
            if ($permissionItem !== null) {
                DB::table('permissions')->where('id', $permissionItem->id)->delete();
                DB::table('model_has_permissions')->where('permission_id', $permissionItem->id)->delete();
            }
        }
    }
};
