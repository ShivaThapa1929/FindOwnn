<?php
/**
 * Generate favicon PNGs from assets/images/logo.png
 * Run: php scripts/generate-favicons.php
 */
$src = dirname(__DIR__) . '/assets/images/logo.png';
$dir = dirname(__DIR__) . '/assets/images';

if (!file_exists($src)) {
    fwrite(STDERR, "Logo not found: {$src}\n");
    exit(1);
}

if (!function_exists('imagecreatefrompng')) {
    fwrite(STDERR, "PHP GD extension required.\n");
    exit(1);
}

$img = imagecreatefrompng($src);
if (!$img) {
    fwrite(STDERR, "Could not load logo PNG.\n");
    exit(1);
}

$w = imagesx($img);
$h = imagesy($img);

function makeSquareIcon($source, int $size, int $srcW, int $srcH, string $path, float $padding = 0.12): void
{
    $out = imagecreatetruecolor($size, $size);
    imagealphablending($out, false);
    imagesavealpha($out, true);
    $transparent = imagecolorallocatealpha($out, 8, 12, 9, 0);
    imagefill($out, 0, 0, $transparent);

    $inner = $size * (1 - $padding * 2);
    $scale = min($inner / $srcW, $inner / $srcH);
    $nw = (int) round($srcW * $scale);
    $nh = (int) round($srcH * $scale);
    $dx = (int) round(($size - $nw) / 2);
    $dy = (int) round(($size - $nh) / 2);

    imagealphablending($out, true);
    imagecopyresampled($out, $source, $dx, $dy, 0, 0, $nw, $nh, $srcW, $srcH);
    imagepng($out, $path);
    imagedestroy($out);
    echo "Wrote {$path}\n";
}

makeSquareIcon($img, 16, $w, $h, $dir . '/favicon-16x16.png');
makeSquareIcon($img, 32, $w, $h, $dir . '/favicon-32x32.png');
makeSquareIcon($img, 180, $w, $h, $dir . '/apple-touch-icon.png', 0.15);
makeSquareIcon($img, 192, $w, $h, $dir . '/icon-192.png', 0.15);
makeSquareIcon($img, 512, $w, $h, $dir . '/icon-512.png', 0.15);

imagedestroy($img);
echo "Done.\n";
