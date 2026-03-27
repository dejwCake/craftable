<?php

declare(strict_types=1);

namespace Brackets\Craftable\Tests\Feature\Database\Migrations;

use Brackets\Craftable\Tests\Feature\TestCase;
use Illuminate\Database\Schema\Blueprint;
use Override;

abstract class PermissionMigrationTestCase extends TestCase
{
    #[Override]
    public function setUp(): void
    {
        parent::setUp();

        $this->app['config']->set('admin-auth.defaults.guard', 'admin');
        $this->app['config']->set('permission.table_names', [
            'roles' => 'roles',
            'permissions' => 'permissions',
            'model_has_permissions' => 'model_has_permissions',
            'model_has_roles' => 'model_has_roles',
            'role_has_permissions' => 'role_has_permissions',
        ]);
        $this->app['config']->set('permission.cache.key', 'spatie.permission.cache');

        $schemaBuilder = $this->app['db']->connection()->getSchemaBuilder();

        $schemaBuilder->dropIfExists('role_has_permissions');
        $schemaBuilder->dropIfExists('model_has_roles');
        $schemaBuilder->dropIfExists('model_has_permissions');
        $schemaBuilder->dropIfExists('roles');
        $schemaBuilder->dropIfExists('permissions');

        $schemaBuilder->create('permissions', static function (Blueprint $table): void {
            $table->bigIncrements('id');
            $table->string('name');
            $table->string('guard_name');
            $table->timestamps();
            $table->unique(['name', 'guard_name']);
        });

        $schemaBuilder->create('roles', static function (Blueprint $table): void {
            $table->bigIncrements('id');
            $table->string('name');
            $table->string('guard_name');
            $table->timestamps();
            $table->unique(['name', 'guard_name']);
        });

        $schemaBuilder->create('model_has_permissions', static function (Blueprint $table): void {
            $table->unsignedBigInteger('permission_id');
            $table->string('model_type');
            $table->unsignedBigInteger('model_id');
            $table->index(['model_id', 'model_type'], 'model_has_permissions_model_id_model_type_index');
            $table->foreign('permission_id')->references('id')->on('permissions')->onDelete('cascade');
            $table->primary(
                ['permission_id', 'model_id', 'model_type'],
                'model_has_permissions_permission_model_type_primary',
            );
        });

        $schemaBuilder->create('model_has_roles', static function (Blueprint $table): void {
            $table->unsignedBigInteger('role_id');
            $table->string('model_type');
            $table->unsignedBigInteger('model_id');
            $table->index(['model_id', 'model_type'], 'model_has_roles_model_id_model_type_index');
            $table->foreign('role_id')->references('id')->on('roles')->onDelete('cascade');
            $table->primary(['role_id', 'model_id', 'model_type'], 'model_has_roles_role_model_type_primary');
        });

        $schemaBuilder->create('role_has_permissions', static function (Blueprint $table): void {
            $table->unsignedBigInteger('permission_id');
            $table->unsignedBigInteger('role_id');
            $table->foreign('permission_id')->references('id')->on('permissions')->onDelete('cascade');
            $table->foreign('role_id')->references('id')->on('roles')->onDelete('cascade');
            $table->primary(['permission_id', 'role_id'], 'role_has_permissions_permission_id_role_id_primary');
        });

        $this->app['db']->connection()->table('roles')->insert([
            'name' => 'Administrator',
            'guard_name' => 'admin',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}
