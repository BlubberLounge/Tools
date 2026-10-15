<?php

namespace App\Http\Controllers\Api\v1;

use InvalidArgumentException;
use Illuminate\Http\Request;

use App\Http\Controllers\Api\v1\Controller;
use App\Minecraft\AnvilPlanner;
use App\Minecraft\EnchantmentData;

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
}
