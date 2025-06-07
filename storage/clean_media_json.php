<?php
// Script de nettoyage pour media_library.json : ne garder que les images originales (pas de vignettes)
$media_json = __DIR__ . '/media_library.json';
$media_library = file_exists($media_json) ? json_decode(file_get_contents($media_json), true) : [];
$cleaned = array_filter($media_library, function($item) {
    return !preg_match('/(_m|_d|_t)\.[a-z0-9]+$/i', $item['filename']);
});
file_put_contents($media_json, json_encode(array_values($cleaned), JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
echo "Nettoyage terminé.\n";
