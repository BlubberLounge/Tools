<?php

namespace App\Http\Requests\Api;

use App\Models\MinecraftPortal;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Create or update a saved Minecraft portal.
 */
class StoreMinecraftPortalRequest extends FormRequest
{
    /** World border of the Overworld; the Nether border is 1/8 of it. */
    public const BORDER = 29999984;

    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array|string>
     */
    public function rules(): array
    {
        $border = $this->input('dimension') === 'nether' ? intdiv(self::BORDER, 8) : self::BORDER;

        return [
            'name' => ['required', 'string', 'max:64'],
            'world' => ['nullable', 'string', 'max:64'],
            'dimension' => ['required', Rule::in(MinecraftPortal::DIMENSIONS)],
            'x' => ['required', 'integer', 'between:-'.$border.','.$border],
            'y' => ['required', 'integer', 'between:-64,320'],
            'z' => ['required', 'integer', 'between:-'.$border.','.$border],
        ];
    }
}
