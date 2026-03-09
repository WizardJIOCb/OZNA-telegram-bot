<?php
/**
 * OZNA PWA - Install frontend files on server.
 * Run from the api/migrations/ directory:
 *   php install_pwa_frontend.php
 */

$target = dirname(dirname(__DIR__)) . '/app';
echo "=== OZNA PWA - Frontend installation ===\n";
echo "Target directory: $target\n\n";

// Read base64-encoded tar.gz from companion file
$b64File = __DIR__ . '/pwa_frontend.b64';
if (!file_exists($b64File)) {
    echo "ERROR: $b64File not found.\n";
    echo "Make sure pwa_frontend.b64 is in the same directory as this script.\n";
    exit(1);
}

$archive = trim(file_get_contents($b64File));
if (empty($archive)) {
    echo "ERROR: pwa_frontend.b64 is empty.\n";
    exit(1);
}

// Create target directories
if (!is_dir($target)) {
    mkdir($target, 0755, true);
}
if (!is_dir($target . '/assets')) {
    mkdir($target . '/assets', 0755, true);
}

// Decode and extract
$tmpFile = tempnam(sys_get_temp_dir(), 'pwa_');
$bytes = file_put_contents($tmpFile, base64_decode($archive));
echo "Archive decoded: $bytes bytes\n";

echo "Extracting files...\n";
$cmd = 'tar xzf ' . escapeshellarg($tmpFile) . ' -C ' . escapeshellarg($target);
exec($cmd, $output, $code);
unlink($tmpFile);

if ($code !== 0) {
    echo "ERROR: tar returned code $code\n";
    echo "Command: $cmd\n";
    exit(1);
}

// Generate PNG icons via GD (blue circle with white O ring)
echo "Generating icons...\n";
foreach ([192, 512] as $s) {
    $img = imagecreatetruecolor($s, $s);
    $bg = imagecolorallocate($img, 21, 101, 192);   // #1565c0
    $white = imagecolorallocate($img, 255, 255, 255);
    imagefill($img, 0, 0, $bg);

    // Draw "O" as a white ring
    $cx = intval($s / 2);
    $cy = intval($s / 2);
    $outerR = intval($s * 0.35);
    $innerR = intval($s * 0.22);
    imagefilledellipse($img, $cx, $cy, $outerR * 2, $outerR * 2, $white);
    imagefilledellipse($img, $cx, $cy, $innerR * 2, $innerR * 2, $bg);

    $path = $target . "/icon-{$s}.png";
    imagepng($img, $path);
    imagedestroy($img);
    echo "  icon-{$s}.png created\n";
}

echo "\nInstalled files:\n";
foreach (glob($target . '/*') as $f) {
    printf("  %-30s %s bytes\n", basename($f), filesize($f));
}
echo "\nAssets:\n";
foreach (glob($target . '/assets/*') as $f) {
    printf("  %-30s %s bytes\n", basename($f), filesize($f));
}

echo "\n=== Installation complete! ===\n";
echo "Open: https://api.ladyzenko.pro/tg/ozna/app/\n";
