<?php
$tree = json_decode(file_get_contents(__DIR__ . '/api/data/activity-tree.json'), true);

function findNodeByKey($nodes, $key) {
    foreach ($nodes as $node) {
        if (($node['key'] ?? '') === $key) return $node;
        if (!empty($node['children'])) {
            $found = findNodeByKey($node['children'], $key);
            if ($found) return $found;
        }
    }
    return null;
}

function findNodePath($nodes, $targetKey, &$path) {
    foreach ($nodes as $node) {
        $key = $node['key'] ?? '';
        $label = $node['label'] ?? $key;
        if ($key === $targetKey) {
            $path[] = $label;
            return true;
        }
        if (!empty($node['children'])) {
            $path[] = $label;
            if (findNodePath($node['children'], $targetKey, $path)) return true;
            array_pop($path);
        }
    }
    return false;
}

function stripEmoji($text) {
    $cleaned = preg_replace('/[\x{1F000}-\x{1FFFF}\x{2300}-\x{23FF}\x{2600}-\x{27BF}\x{FE0E}\x{FE0F}\x{200D}\x{20E3}\x{E0020}-\x{E007F}]/u', '', $text);
    return trim($cleaned);
}

function resolve($tree, $leafKey) {
    $idleRootKey = null;
    if (str_starts_with($leafKey, 'pnr_idle_')) $idleRootKey = 'Простой';
    elseif (str_starts_with($leafKey, 'smr_idle_')) $idleRootKey = 'ШМ_Простой';
    elseif (str_starts_with($leafKey, 'mob_idle_')) $idleRootKey = 'МобБриг_Простой';
    if (!$idleRootKey) return null;

    $idleRoot = findNodeByKey($tree, $idleRootKey);
    if (!$idleRoot || empty($idleRoot['children'])) return null;

    $path = [];
    if (findNodePath($idleRoot['children'], $leafKey, $path)) {
        if (count($path) === 0) return null;
        if (count($path) === 1) return stripEmoji($path[0]);
        $category = stripEmoji($path[0]);
        $details = array_map('stripEmoji', array_slice($path, 1));
        return $category . ' (' . implode(', ', $details) . ')';
    }
    return null;
}

$mappings = [
    'Простой' => 'Простой (Пусконаладка)',
    'ШМ_Простой' => 'Простой (Шеф-монтаж)',
    'МобБриг_Простой' => 'Простой (Монтажные работы)',
];

$tests = [
    'pnr_idle_2_sog'    => 'Простой',
    'pnr_idle_1_izm'    => 'Простой',
    'smr_idle_3_rab'    => 'ШМ_Простой',
    'pnr_idle_2_teh_es' => 'Простой',
    'mob_idle_5_mor'    => 'МобБриг_Простой',
    'pnr_idle_6_kom'    => 'Простой',
    'pnr_idle_1_def'    => 'Простой',
];

echo "=== Idle Reason Resolution Test ===\n\n";
foreach ($tests as $leafKey => $rootKey) {
    $idleReason = resolve($tree, $leafKey);
    $activityText = $mappings[$rootKey] ?? $rootKey;
    if (!empty($idleReason)) {
        $activityText .= " ({$idleReason})";
    }
    echo "Leaf: {$leafKey}\n";
    echo "  idle_reason:   {$idleReason}\n";
    echo "  Final text:    {$activityText}\n\n";
}
