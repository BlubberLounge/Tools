<?php

$versionFile = base_path('version.json');

$data = file_exists($versionFile)
    ? json_decode(file_get_contents($versionFile), true)
    : [];

return [
    'version' => $data['version'] ?? '0.0.0',
    'timestamp' => $data['timestamp'] ?? null,
];
