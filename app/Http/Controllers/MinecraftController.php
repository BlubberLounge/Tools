<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;

use App\Minecraft\EnchantmentData;
use App\Minecraft\ToolRegistry;

class MinecraftController extends Controller
{
    /**
     * Overview of the Minecraft tools the user can open.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function index()
    {
        $data['tools'] = ToolRegistry::visibleTo(Auth::user());

        return view('minecraft.index', $data);
    }

    /**
     * Anvil enchantment planner.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function showAnvilPlanner()
    {
        $data['versions'] = array_map(
            fn (string $version) => [
                'id' => $version,
                'name' => EnchantmentData::load($version)->name(),
            ],
            EnchantmentData::versions()
        );

        return view('minecraft.anvil-planner', $data);
    }
}
