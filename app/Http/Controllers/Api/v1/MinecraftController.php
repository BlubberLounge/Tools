<?php

namespace App\Http\Controllers\Api\v1;

use InvalidArgumentException;
use Illuminate\Http\Request;

use App\Http\Controllers\Api\v1\Controller;
use App\Minecraft\AnvilPlanner;
use App\Minecraft\EnchantmentData;
use App\Minecraft\FarmCatalog;

class MinecraftController extends Controller
{
    /**
     * @OA\Get(
     *      path="/minecraft/versions",
     *      operationId="minecraftVersions",
     *      tags={"Minecraft"},
     *      summary="Minecraft versions with anvil data",
     *      description="Java versions that have an enchantment data file, newest first",
     *      @OA\Response(response=200, description="Successful operation"),
     *      @OA\Response(response=403, description="Forbidden")
     * )
     */
    public function versions()
    {
        $versions = array_map(
            fn (string $version) => [
                'id' => $version,
                'name' => EnchantmentData::load($version)->name(),
            ],
            EnchantmentData::versions()
        );

        return $this->sendResponse($versions, 'Minecraft versions');
    }

    /**
     * @OA\Get(
     *      path="/minecraft/versions/{version}",
     *      operationId="minecraftVersion",
     *      tags={"Minecraft"},
     *      summary="Items and enchantments of one version",
     *      @OA\Parameter(name="version", in="path", required=true, @OA\Schema(type="string", example="26.3")),
     *      @OA\Response(response=200, description="Successful operation"),
     *      @OA\Response(response=404, description="Unknown version")
     * )
     */
    public function version(string $version)
    {
        if (!EnchantmentData::exists($version))
            return $this->sendError('Unknown Minecraft version.', [], 404);

        return $this->sendResponse(EnchantmentData::load($version)->toClientArray(), 'Minecraft version data');
    }

    /**
     * @OA\Post(
     *      path="/minecraft/anvil/plan",
     *      operationId="minecraftAnvilPlan",
     *      tags={"Minecraft"},
     *      summary="Cheapest anvil order for a set of enchanted books",
     *      @OA\RequestBody(
     *          required=true,
     *          @OA\JsonContent(
     *              required={"version", "item", "enchantments"},
     *              @OA\Property(property="version", type="string", example="26.3"),
     *              @OA\Property(property="item", type="string", example="diamond_sword"),
     *              @OA\Property(property="enchantments", type="object", example={"sharpness": 5, "looting": 3}),
     *              @OA\Property(property="present", type="object", example={"mending": 1}),
     *              @OA\Property(property="prior_work", type="integer", example=0)
     *          )
     *      ),
     *      @OA\Response(response=200, description="Plan (possible=false when a step would be too expensive)"),
     *      @OA\Response(response=422, description="Invalid selection")
     * )
     */
    public function plan(Request $request)
    {
        $validated = $request->validate([
            'version' => ['required', 'string', 'max:20'],
            'item' => ['required', 'string', 'max:64'],
            'enchantments' => ['present', 'array', 'max:'.AnvilPlanner::MAX_BOOKS],
            'enchantments.*' => ['integer', 'min:1', 'max:10'],
            'present' => ['sometimes', 'array', 'max:20'],
            'present.*' => ['integer', 'min:1', 'max:10'],
            'prior_work' => ['sometimes', 'integer', 'min:0', 'max:'.AnvilPlanner::MAX_PRIOR_WORK],
        ]);

        if (!EnchantmentData::exists($validated['version']))
            return $this->sendError('Unknown Minecraft version.', [], 422);

        $toLevels = fn (array $values) => array_map('intval', $values);

        try {
            $plan = (new AnvilPlanner(EnchantmentData::load($validated['version'])))->plan(
                $validated['item'],
                $toLevels($validated['enchantments']),
                $toLevels($validated['present'] ?? []),
                (int) ($validated['prior_work'] ?? 0),
            );
        } catch (InvalidArgumentException $e) {
            return $this->sendError($e->getMessage(), [], 422);
        }

        return $this->sendResponse($plan, $plan['possible'] ? 'Anvil plan' : 'No order stays below the level cap');
    }

    /**
     * @OA\Get(
     *      path="/minecraft/farms",
     *      operationId="minecraftFarmVersions",
     *      tags={"Minecraft"},
     *      summary="Minecraft versions with a farm catalogue",
     *      description="Java versions that have a farm catalogue file, newest first",
     *      @OA\Response(response=200, description="Successful operation"),
     *      @OA\Response(response=403, description="Forbidden")
     * )
     */
    public function farmVersions()
    {
        $versions = array_map(
            fn (string $version) => [
                'id' => $version,
                'name' => FarmCatalog::load($version)->name(),
                'designs' => count(FarmCatalog::load($version)->designs()),
            ],
            FarmCatalog::versions()
        );

        return $this->sendResponse($versions, 'Minecraft farm catalogue versions');
    }

    /**
     * @OA\Get(
     *      path="/minecraft/farms/{version}",
     *      operationId="minecraftFarms",
     *      tags={"Minecraft"},
     *      summary="Farm designs of one version, filtered and sorted",
     *      @OA\Parameter(name="version", in="path", required=true, @OA\Schema(type="string", example="26.3")),
     *      @OA\Parameter(name="item", in="query", description="Search in the produced items", @OA\Schema(type="string", example="iron")),
     *      @OA\Parameter(name="type", in="query", description="Farm type id", @OA\Schema(type="string", example="iron")),
     *      @OA\Parameter(name="difficulty", in="query", @OA\Schema(type="string", enum={"easy", "medium", "hard"})),
     *      @OA\Parameter(name="max_footprint", in="query", description="Longest horizontal side in blocks", @OA\Schema(type="integer", example=32)),
     *      @OA\Parameter(name="sort", in="query", @OA\Schema(type="string", enum={"rate", "footprint", "name"})),
     *      @OA\Parameter(name="direction", in="query", @OA\Schema(type="string", enum={"asc", "desc"})),
     *      @OA\Response(response=200, description="Successful operation"),
     *      @OA\Response(response=404, description="Unknown version"),
     *      @OA\Response(response=422, description="Invalid filter")
     * )
     */
    public function farms(Request $request, string $version)
    {
        if (!FarmCatalog::exists($version))
            return $this->sendError('Unknown Minecraft version.', [], 404);

        $catalog = FarmCatalog::load($version);

        $validated = $request->validate([
            'item' => ['nullable', 'string', 'max:64'],
            'type' => ['nullable', 'string', 'in:'.implode(',', array_keys($catalog->farmTypes()))],
            'difficulty' => ['nullable', 'string', 'in:'.implode(',', FarmCatalog::DIFFICULTIES)],
            'max_footprint' => ['nullable', 'integer', 'min:1', 'max:10000'],
            'sort' => ['nullable', 'string', 'in:'.implode(',', FarmCatalog::SORTS)],
            'direction' => ['nullable', 'string', 'in:asc,desc'],
        ]);

        $query = (string) ($validated['item'] ?? '');
        $designs = $catalog->sort(
            $catalog->filter($validated),
            $validated['sort'] ?? 'rate',
            $validated['direction'] ?? 'default',
            $query,
        );

        return $this->sendResponse($catalog->meta() + [
            'total' => count($catalog->designs()),
            'designs' => array_map(fn (array $design) => $catalog->presentDesign($design, $query), $designs),
        ], 'Minecraft farm designs');
    }

    /**
     * @OA\Get(
     *      path="/minecraft/farms/{version}/designs/{design}",
     *      operationId="minecraftFarmDesign",
     *      tags={"Minecraft"},
     *      summary="One farm design with the designs that make the same item",
     *      @OA\Parameter(name="version", in="path", required=true, @OA\Schema(type="string", example="26.3")),
     *      @OA\Parameter(name="design", in="path", required=true, @OA\Schema(type="string", example="booty-3-pod-iron-farm")),
     *      @OA\Response(response=200, description="Successful operation"),
     *      @OA\Response(response=404, description="Unknown version or design")
     * )
     */
    public function farmDesign(string $version, string $design)
    {
        if (!FarmCatalog::exists($version))
            return $this->sendError('Unknown Minecraft version.', [], 404);

        $catalog = FarmCatalog::load($version);
        $found = $catalog->design($design);
        if ($found === null)
            return $this->sendError('Unknown farm design.', [], 404);

        return $this->sendResponse([
            'design' => $catalog->presentDesign($found),
            'comparison' => $catalog->comparison($design),
        ], 'Minecraft farm design');
    }
}
