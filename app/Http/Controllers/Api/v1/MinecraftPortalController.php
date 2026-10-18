<?php

namespace App\Http\Controllers\Api\v1;

use Illuminate\Http\Request;

use App\Http\Requests\Api\StoreMinecraftPortalRequest;
use App\Models\MinecraftPortal;

/**
 * Portals the user saved in the nether portal calculator. Every user only
 * sees and changes their own portals.
 */
class MinecraftPortalController extends Controller
{
    public function index(Request $request)
    {
        $portals = MinecraftPortal::where('user_id', $request->user()->id)
            ->orderBy('world')
            ->orderBy('name')
            ->get();

        return $this->sendResponse($portals, 'Portals');
    }

    public function store(StoreMinecraftPortalRequest $request)
    {
        $portal = MinecraftPortal::create($request->validated() + ['user_id' => $request->user()->id]);

        return $this->sendResponse($portal, 'Portal saved');
    }

    public function update(StoreMinecraftPortalRequest $request, int $portal)
    {
        $model = $this->owned($request, $portal);
        if ($model === null)
            return $this->sendError('Portal not found.');

        $model->update($request->validated());

        return $this->sendResponse($model, 'Portal saved');
    }

    public function destroy(Request $request, int $portal)
    {
        $model = $this->owned($request, $portal);
        if ($model === null)
            return $this->sendError('Portal not found.');

        $model->delete();

        return $this->sendResponse(null, 'Portal deleted');
    }

    private function owned(Request $request, int $id): ?MinecraftPortal
    {
        return MinecraftPortal::where('user_id', $request->user()->id)->find($id);
    }
}
