<?php

use Illuminate\Database\Migrations\Migration;

/**
 * Adds the Minecraft portal calculator permission to existing databases (fresh databases
 * get it from PermissionsTableSeeder / ConnectRelationshipsSeeder as well).
 */
return new class extends Migration
{
    private const PERMISSIONS = [
        [
            'name'          => 'Can Use Minecraft Portal Calculator',
            'slug'          => 'view.minecraft.portal.calculator',
            'description'   => 'Can use the Minecraft nether portal calculator and save portals',
        ],
    ];

    /** Roles that get the permissions; roles missing in this database are skipped. */
    private const ROLES = ['root', 'developer', 'admin', 'trusted', 'user'];

    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $permissionModel = config('roles.models.permission');
        $roleModel = config('roles.models.role');

        foreach (self::PERMISSIONS as $item) {
            $permission = $permissionModel::withTrashed()->where('slug', $item['slug'])->first()
                ?? $permissionModel::create($item);
            if ($permission->trashed())
                $permission->restore();

            foreach ($roleModel::whereIn('slug', self::ROLES)->get() as $role)
                if (!$role->permissions()->where('slug', $item['slug'])->exists())
                    $role->attachPermission($permission);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $permissionModel = config('roles.models.permission');

        foreach (self::PERMISSIONS as $item) {
            $permission = $permissionModel::withTrashed()->where('slug', $item['slug'])->first();
            if ($permission === null)
                continue;

            $permission->roles()->detach();
            $permission->forceDelete();
        }
    }
};
