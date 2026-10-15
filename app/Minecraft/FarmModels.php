<?php

namespace App\Minecraft;

/**
 * 3D farm models for the farm viewer: one JSON file per model in
 * public/minecraft/models/{id}.json (format: resources/js/minecraft/viewer/model.js).
 * A model may name the farm catalogue design it shows (`design`), so the
 * catalogue can link to the viewer with ?design={design id}.
 */
final class FarmModels
{
    public const FORMAT = 1;
    public const MAX_SIZE = 128;

    private const ID_PATTERN = '/^[a-z0-9]+(?:-[a-z0-9]+)*$/';

    /** @var array<int, array>|null */
    private static ?array $summaries = null;

    public static function directory(): string
    {
        return public_path('minecraft/models');
    }

    public static function blockTablePath(): string
    {
        return resource_path('minecraft/viewer/blocks.json');
    }

    /**
     * Model files on disk.
     *
     * @return string[]
     */
    public static function files(): array
    {
        $files = glob(self::directory() . DIRECTORY_SEPARATOR . '*.json') ?: [];
        sort($files);

        return $files;
    }

    /**
     * Summaries of all models (no block data), sorted by name.
     *
     * @return array<int, array{id: string, design: ?string, name: string, version: ?string, size: int[], source: array, url: string}>
     */
    public static function all(): array
    {
        if (self::$summaries !== null)
            return self::$summaries;

        $models = [];
        foreach (self::files() as $file) {
            $json = json_decode((string) file_get_contents($file), true);
            if (!is_array($json) || !isset($json['id'], $json['name'], $json['size']))
                continue;
            if (basename($file, '.json') !== $json['id'])
                continue;

            $models[] = [
                'id' => $json['id'],
                'design' => $json['design'] ?? null,
                'name' => $json['name'],
                'version' => $json['version'] ?? null,
                'size' => $json['size'],
                'source' => $json['source'] ?? [],
                'url' => asset('minecraft/models/' . $json['id'] . '.json'),
            ];
        }

        usort($models, fn (array $a, array $b) => strcasecmp($a['name'], $b['name']));

        return self::$summaries = $models;
    }

    /**
     * Model for a catalogue design id, or by its own model id.
     */
    public static function find(string $id): ?array
    {
        return self::forDesign($id) ?? self::byId($id);
    }

    public static function forDesign(string $designId): ?array
    {
        foreach (self::all() as $model)
            if ($model['design'] === $designId)
                return $model;

        return null;
    }

    public static function byId(string $id): ?array
    {
        foreach (self::all() as $model)
            if ($model['id'] === $id)
                return $model;

        return null;
    }

    /**
     * Block ids the viewer knows (keys of the block table).
     *
     * @return array<string, true>
     */
    public static function knownBlocks(): array
    {
        $table = json_decode((string) file_get_contents(self::blockTablePath()), true);

        return array_fill_keys(array_keys($table['blocks'] ?? []), true);
    }

    /**
     * Problems of a model (empty when valid). Mirrors validateModel() in model.js.
     *
     * @param  array<string, true>  $knownBlocks
     * @return string[]
     */
    public static function validate(mixed $model, array $knownBlocks): array
    {
        if (!is_array($model))
            return ['model is not an object'];

        $errors = [];
        if (($model['format'] ?? null) !== self::FORMAT)
            $errors[] = 'format must be ' . self::FORMAT;
        if (!is_string($model['id'] ?? null) || !preg_match(self::ID_PATTERN, $model['id']))
            $errors[] = 'id must be kebab-case';
        $design = $model['design'] ?? null;
        if ($design !== null && (!is_string($design) || !preg_match(self::ID_PATTERN, $design)))
            $errors[] = 'design must be a kebab-case catalogue id or null';
        if (!is_string($model['name'] ?? null) || $model['name'] === '')
            $errors[] = 'name is missing';

        $size = $model['size'] ?? null;
        $sizeOk = is_array($size) && count($size) === 3 && array_is_list($size);
        if ($sizeOk)
            foreach ($size as $n)
                if (!is_int($n) || $n < 1 || $n > self::MAX_SIZE)
                    $sizeOk = false;
        if (!$sizeOk)
            $errors[] = 'size must be [x, y, z] with 1..' . self::MAX_SIZE . ' each';

        $url = $model['source']['url'] ?? null;
        if (!is_string($url) || !preg_match('#^https?://#', $url))
            $errors[] = 'source.url is missing';
        if (!is_string($model['source']['license'] ?? null) || $model['source']['license'] === '')
            $errors[] = 'source.license is missing';

        $palette = $model['palette'] ?? null;
        if (!is_array($palette) || $palette === [] || ($palette[0]['name'] ?? null) !== 'minecraft:air') {
            $errors[] = 'palette must start with minecraft:air';
        } else {
            $seen = [];
            foreach ($palette as $i => $entry) {
                $name = $entry['name'] ?? null;
                if (!is_string($name)) {
                    $errors[] = "palette[$i] has no name";
                    continue;
                }
                $props = $entry['properties'] ?? [];
                ksort($props);
                $key = $name . json_encode($props);
                if (isset($seen[$key]))
                    $errors[] = "palette[$i] duplicates $name";
                $seen[$key] = true;
                if (!isset($knownBlocks[$name]))
                    $errors[] = "unknown block id $name";
            }
        }

        if ($errors !== [])
            return $errors;

        // run-length encoded blocks: [run, index, run, index, ...]
        $blocks = $model['blocks'] ?? null;
        if (!is_array($blocks) || count($blocks) % 2 !== 0)
            return ['blocks must be a flat list of [run, index] pairs'];

        $total = $size[0] * $size[1] * $size[2];
        $covered = 0;
        $paletteSize = count($palette);
        for ($i = 0; $i < count($blocks); $i += 2) {
            $run = $blocks[$i];
            $index = $blocks[$i + 1];
            if (!is_int($run) || $run < 1 || !is_int($index) || $index < 0)
                return ["invalid run at $i"];
            if ($index >= $paletteSize)
                return ["palette index $index out of range"];
            $covered += $run;
        }
        if ($covered !== $total)
            $errors[] = "blocks cover $covered cells, the size needs $total";

        return $errors;
    }

    /** Forget cached summaries (tests). */
    public static function flush(): void
    {
        self::$summaries = null;
    }
}
