<?php

namespace App\Minecraft;

use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Support\Facades\Route;

/**
 * Minecraft tools listed in the sidebar and on the /minecraft overview.
 * Entries come from config/minecraft.php.
 */
final class ToolRegistry
{
    /** Permission for the Minecraft section itself (overview page). */
    public const SECTION_PERMISSION = 'view.minecraft';

    private const FIELDS = ['id', 'title', 'description', 'icon', 'route', 'permission'];

    /**
     * All registered tools whose route exists.
     *
     * @return array<int, array{id: string, title: string, description: string, icon: string, route: string, permission: string}>
     */
    public static function all(): array
    {
        $tools = [];
        foreach (config('minecraft.tools', []) as $tool) {
            if (array_diff(self::FIELDS, array_keys($tool)) !== [])
                continue;

            // a tool without its page is not built yet
            if (!Route::has($tool['route']))
                continue;

            $tools[] = $tool;
        }

        return $tools;
    }

    /**
     * Tools the user may open.
     */
    public static function visibleTo(?Authenticatable $user): array
    {
        if ($user === null || !method_exists($user, 'hasPermission'))
            return [];

        return array_values(array_filter(
            self::all(),
            fn (array $tool) => $user->hasPermission($tool['permission'])
        ));
    }

    public static function find(string $id): ?array
    {
        foreach (self::all() as $tool)
            if ($tool['id'] === $id)
                return $tool;

        return null;
    }

    /**
     * Route names of the section, used to keep the sidebar group open.
     *
     * @return string[]
     */
    public static function routeNames(): array
    {
        return array_merge(['minecraft.index'], array_column(self::all(), 'route'));
    }
}
