<?php

// Rebuild the web client's design resources from the native app, without modifying it.
$source = $argv[1] ?? 'C:/Users/Hp/AndroidStudioProjects/Babagyi/app/src/main/res';
$target = dirname(__DIR__).'/public/client';
foreach ([$target, "$target/icons", "$target/fonts", "$target/images"] as $directory) {
    if (! is_dir($directory)) mkdir($directory, 0775, true);
}
$android = 'http://schemas.android.com/apk/res/android';
$resources = ['strings' => [], 'colors' => [], 'nightColors' => [], 'dimens' => [], 'layouts' => [], 'icons' => []];
foreach (glob("$source/values/*.xml") as $file) {
    $xml = simplexml_load_file($file);
    foreach ($xml->children() as $child) {
        $kind = ['string' => 'strings', 'color' => 'colors', 'dimen' => 'dimens'][$child->getName()] ?? null;
        if ($kind) $resources[$kind][(string) $child['name']] = (string) $child;
    }
}
foreach (glob("$source/values-night/*.xml") as $file) {
    foreach (simplexml_load_file($file)->color as $child) {
        $resources['nightColors'][(string) $child['name']] = (string) $child;
    }
}
function resolveResource(string $value, array $map): string {
    for ($i = 0; $i < 10 && str_starts_with($value, '@'); $i++) {
        $key = substr($value, strpos($value, '/') + 1);
        if (! isset($map[$key])) break;
        $value = $map[$key];
    }
    return $value;
}
foreach (['strings', 'colors'] as $kind) {
    foreach ($resources[$kind] as &$value) $value = resolveResource($value, $resources[$kind]);
    unset($value);
}
$resources['nightColors'] = array_merge($resources['colors'], $resources['nightColors']);
foreach ($resources['nightColors'] as &$value) $value = resolveResource($value, $resources['nightColors']);
unset($value);
function layoutNode(SimpleXMLElement $xml): array {
    global $android;
    $attributes = [];
    foreach ($xml->attributes() as $key => $value) $attributes[$key] = (string) $value;
    foreach ($xml->attributes($android) as $key => $value) $attributes[$key] = (string) $value;
    foreach ($xml->attributes('http://schemas.android.com/apk/res-auto') as $key => $value) $attributes[$key] = (string) $value;
    $children = [];
    foreach ($xml->children() as $child) $children[] = layoutNode($child);
    return ['tag' => $xml->getName(), 'attrs' => $attributes, 'children' => $children];
}
foreach (glob("$source/layout/*.xml") as $file) {
    $resources['layouts'][basename($file, '.xml')] = layoutNode(simplexml_load_file($file));
}
function svgColor(string $value): string {
    global $resources;
    if ($value === '@android:color/transparent') return 'none';
    if ($value === '@android:color/white') return '#FFFFFF';
    if ($value === '@android:color/black') return '#000000';
    $value = resolveResource($value, $resources['colors']);
    if (str_starts_with($value, '@') || str_starts_with($value, '?') || $value === '') return 'currentColor';
    if (preg_match('/^#([0-9a-f]{2})([0-9a-f]{6})$/i', $value, $match)) return '#'.$match[2].$match[1];
    return $value;
}
function vectorNodes(SimpleXMLElement $xml): string {
    global $android;
    $result = '';
    foreach ($xml->children() as $node) {
        $a = $node->attributes($android);
        if ($node->getName() === 'path') {
            $result .= '<path d="'.htmlspecialchars((string) $a['pathData'], ENT_QUOTES).'"';
            foreach (['fillColor' => 'fill', 'strokeColor' => 'stroke', 'strokeWidth' => 'stroke-width',
                'strokeLineCap' => 'stroke-linecap', 'strokeLineJoin' => 'stroke-linejoin',
                'fillAlpha' => 'fill-opacity', 'strokeAlpha' => 'stroke-opacity'] as $key => $attribute) {
                if (isset($a[$key])) $result .= ' '.$attribute.'="'.htmlspecialchars(str_contains($key, 'Color') ? svgColor((string) $a[$key]) : (string) $a[$key], ENT_QUOTES).'"';
            }
            if (! isset($a['fillColor'])) $result .= ' fill="none"';
            if ((string) $a['fillType'] === 'evenOdd') $result .= ' fill-rule="evenodd"';
            $result .= '/>';
        } elseif ($node->getName() === 'group') {
            $transform = 'translate('.($a['translateX'] ?? 0).' '.($a['translateY'] ?? 0).') rotate('.($a['rotation'] ?? 0).' '.($a['pivotX'] ?? 0).' '.($a['pivotY'] ?? 0).') scale('.($a['scaleX'] ?? 1).' '.($a['scaleY'] ?? 1).')';
            $result .= '<g transform="'.$transform.'">'.vectorNodes($node).'</g>';
        }
    }
    return $result;
}
$icons = 0;
foreach (glob("$source/drawable/*.xml") as $file) {
    $xml = simplexml_load_file($file);
    if ($xml->getName() !== 'vector') continue;
    $a = $xml->attributes($android);
    $svg = '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 '.$a['viewportWidth'].' '.$a['viewportHeight'].'">'.vectorNodes($xml).'</svg>';
    file_put_contents("$target/icons/".basename($file, '.xml').'.svg', $svg);
    $resources['icons'][basename($file, '.xml')] = $svg;
    $icons++;
}
foreach (glob("$source/font/poppins_*.ttf") as $file) copy($file, "$target/fonts/".basename($file));
foreach (glob("$source/drawable-nodpi/*.webp") as $file) copy($file, "$target/images/".basename($file));
file_put_contents("$target/android-resources.json", json_encode($resources, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT));
echo count($resources['layouts']).' layouts, '.count($resources['strings'])." strings, $icons native vector icons exported.\n";
