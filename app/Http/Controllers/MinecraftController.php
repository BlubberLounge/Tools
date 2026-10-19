<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;

use Illuminate\Http\Request;

use App\Minecraft\EnchantmentData;
use App\Http\Controllers\Api\v1\MinecraftController as MinecraftApiController;
use App\Minecraft\FarmCatalog;
use App\Minecraft\FarmModels;
use App\Minecraft\ToolRegistry;
use App\Minecraft\ChainPlanner;
use App\Minecraft\RecipeData;

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
     * Farm module catalogue.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function showFarmCatalogue()
    {
        $data['versions'] = array_map(
            fn (string $version) => [
                'id' => $version,
                'name' => FarmCatalog::load($version)->name(),
            ],
            FarmCatalog::versions()
        );

        return view('minecraft.farm-catalogue', $data);
    }

    /**
     * Farm chain planner.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function showChainPlanner()
    {
        $data['versions'] = MinecraftApiController::chainVersionList();

        return view('minecraft.chain-planner', $data);
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

    /**
     * Nether portal calculator. The page loads the saved portals from
     * /api/v1/minecraft/portals.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function showPortalCalculator()
    {
        return view('minecraft.portal-calculator');
    }

    /**
     * Stack calculator: items, stacks, chests and shulker boxes. Works fully
     * in the browser.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function showStackCalculator()
    {
        return view('minecraft.stack-calculator');
    }

    /**
     * Material calculator for boxes, rooms, walls and frames. Works fully
     * in the browser.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function showMaterialCalculator()
    {
        return view('minecraft.material-calculator');
    }

    /**
     * XP calculator. Mob XP comes from the newest enchantment data file.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function showXpCalculator()
    {
        $data = EnchantmentData::load(EnchantmentData::latestVersion());

        return view('minecraft.xp-calculator', ['mobs' => $data->xpSources()['mobs']]);
    }

    /**
     * Smelting and fuel calculator. Fuels come from the newest recipe data file.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function showSmeltCalculator()
    {
        $recipes = RecipeData::load(RecipeData::versions()[0]);

        return view('minecraft.smelt-calculator', ['fuels' => $recipes->meta()['fuels']]);
    }

    /**
     * Crafting calculator. Recipes and default ways come from the newest
     * recipe data file (villager trades left out).
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function showCraftCalculator()
    {
        $version = RecipeData::versions()[0];
        $recipes = RecipeData::load($version);
        $meta = $recipes->meta();

        $list = array_values(array_filter($recipes->recipes(), fn (array $recipe) => $recipe['type'] !== 'trading'));
        $ids = array_column($list, 'id');

        // default ways of the chain planner; a trade becomes 'gather'
        $planner = new ChainPlanner($recipes, FarmCatalog::load($version));
        $choices = array_map(
            fn (string $choice) => in_array($choice, $ids, true) ? $choice : ChainPlanner::SOURCE,
            $planner->defaultChoices($meta['defaults']['fuel'])
        );

        return view('minecraft.craft-calculator', [
            'items' => $meta['items'],
            'recipes' => $list,
            'choices' => $choices,
            'fuels' => $meta['fuels'],
            'versionName' => $recipes->name(),
        ]);
    }

    /**
     * Circle and sphere generator. Works fully in the browser.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function showCircleGenerator()
    {
        return view('minecraft.circle-generator');
    }

    /**
     * Slime chunk finder. Works fully in the browser; the seed never
     * leaves it.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function showSlimeFinder()
    {
        return view('minecraft.slime-finder');
    }
}
