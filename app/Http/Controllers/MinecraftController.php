<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;

use Illuminate\Http\Request;

use App\Minecraft\EnchantmentData;
use App\Minecraft\FarmModels;
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

    /**
     * 3D farm viewer. `?design=` opens the model of a farm catalogue design
     * (or a model id); without it the first model is shown.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function showFarmViewer(Request $request)
    {
        $models = FarmModels::all();
        $requested = $request->query('design');

        $selected = is_string($requested) ? FarmModels::find($requested) : null;
        $data['models'] = $models;
        $data['selected'] = $selected ?? ($models[0] ?? null);
        $data['notFound'] = is_string($requested) && $selected === null;

        return view('minecraft.farm-viewer', $data);
    }
}
