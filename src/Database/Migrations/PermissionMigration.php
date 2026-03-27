<?php

declare(strict_types=1);

namespace Brackets\Craftable\Database\Migrations;

use Brackets\Craftable\Database\Migrations\Dtos\Permission;
use Brackets\Craftable\Database\Migrations\Dtos\Role;
use Carbon\CarbonImmutable;
use Exception;
use Illuminate\Contracts\Cache\Repository as Cache;
use Illuminate\Contracts\Config\Repository as Config;
use Illuminate\Database\DatabaseManager;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Collection;

abstract class PermissionMigration extends Migration
{
    private Config $config;
    private Cache $cache;
    private DatabaseManager $databaseManager;
    private string $guardName;

    /** @var Collection<int, Permission> */
    private Collection $permissions;

    /** @var Collection<int, Role> */
    private Collection $roles;

    /** @var array<string, string> */
    private array $tableNames;

    public function __construct()
    {
        $this->config = app(Config::class);
        $this->cache = app(Cache::class);
        $this->databaseManager = app(DatabaseManager::class);
        $this->guardName = $this->config->get('admin-auth.defaults.guard');
        $this->tableNames = $this->getTableNames();
        $this->permissions = new Collection();
        $this->roles = new Collection();
    }

    /**
     * @param Collection<string> $permissions
     * @param Collection<string> $roles
     */
    protected function setPermissionsAndRoles(Collection $permissions, Collection $roles): void
    {
        //Add New permissions
        $this->permissions = $permissions->map(fn (string $permission)
            => new Permission($permission, $this->guardName, CarbonImmutable::now(), CarbonImmutable::now()));

        //Role should already exist
        $this->roles = $roles->isNotEmpty()
            ? $roles->map(fn (string $role) => new Role($role, $this->guardName))
            : new Collection([new Role('Administrator', $this->guardName)]);
    }

    /**
     * @throws Exception
     */
    protected function migrateUp(): void
    {
        $this->databaseManager->transaction(function (): void {
            $this->insertMissingPermissions();
            $this->assignPermissionsToRoles();
        });
        $this->forget();
    }

    /**
     * @throws Exception
     */
    protected function migrateDown(): void
    {
        $this->databaseManager->transaction(function (): void {
            $this->deleteExistingPermissions();
        });
        $this->forget();
    }

    /** @return array<string, string> */
    private function getTableNames(): array
    {
        return $this->config->get(
            'permission.table_names',
            [
                'roles' => 'roles',
                'permissions' => 'permissions',
                'model_has_permissions' => 'model_has_permissions',
                'model_has_roles' => 'model_has_roles',
                'role_has_permissions' => 'role_has_permissions',
            ],
        );
    }

    private function insertMissingPermissions(): void
    {
        $this->permissions->each(function (Permission $permission): void {
            $permissionItem = $this->databaseManager
                ->table($this->tableNames['permissions'])
                ->where([
                    'name' => $permission->name,
                    'guard_name' => $permission->guardName,
                ])->first();
            if ($permissionItem === null) {
                $this->databaseManager
                    ->table($this->tableNames['permissions'])
                    ->insert($permission->toArray());
            }
        });
    }

    private function assignPermissionsToRoles(): void
    {
        $this->roles->each(function (Role $role): void {
            $roleItem = $this->databaseManager
                ->table($this->tableNames['roles'])
                ->where([
                    'name' => $role->name,
                    'guard_name' => $role->guardName,
                ])->first();
            if ($roleItem === null) {
                return;
            }

            $permissionItems = $this->databaseManager
                ->table($this->tableNames['permissions'])
                ->whereIn('name', $this->permissions->pluck('name'))
                ->where('guard_name', $role->guardName)
                ->get();
            foreach ($permissionItems as $permissionItem) {
                $roleHasPermissionData = [
                    'permission_id' => $permissionItem->id,
                    'role_id' => $roleItem->id,
                ];
                $roleHasPermissionItem = $this->databaseManager
                    ->table($this->tableNames['role_has_permissions'])
                    ->where($roleHasPermissionData)
                    ->first();
                if ($roleHasPermissionItem === null) {
                    $this->databaseManager
                        ->table($this->tableNames['role_has_permissions'])
                        ->insert($roleHasPermissionData);
                }
            }
        });
    }

    private function deleteExistingPermissions(): void
    {
        $this->permissions->each(function (Permission $permission): void {
            $permissionItem = $this->databaseManager
                ->table($this->tableNames['permissions'])
                ->where([
                    'name' => $permission->name,
                    'guard_name' => $permission->guardName,
                ])->first();
            if ($permissionItem !== null) {
                $this->databaseManager
                    ->table($this->tableNames['permissions'])
                    ->where('id', $permissionItem->id)
                    ->delete();
                $this->databaseManager
                    ->table($this->tableNames['model_has_permissions'])
                    ->where('permission_id', $permissionItem->id)
                    ->delete();
            }
        });
    }

    private function forget(): void
    {
        $this->cache->forget($this->config->get('permission.cache.key'));
    }
}
