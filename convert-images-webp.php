<?php
/**
 * Convert PNG images to WebP format
 * Run: php convert-images-webp.php
 */

$images = [
    'public/images/privacy-legal.png' => 'public/images/privacy-legal.webp',
    'public/images/terms-legal.png' => 'public/images/terms-legal.webp',
    'public/resume.png' => 'public/resume-alt.webp',
    'public/resume.jpg' => 'public/resume-alt2.webp',
];

foreach ($images as $source => $destination) {
    if (!file_exists($source)) {
        echo "❌ Source not found: $source\n";
        continue;
    }

    $image = null;
    $ext = strtolower(pathinfo($source, PATHINFO_EXTENSION));

    // Load image based on type
    if ($ext === 'png') {
        $image = imagecreatefrompng($source);
    } elseif ($ext === 'jpg' || $ext === 'jpeg') {
        $image = imagecreatefromjpeg($source);
    }

    if ($image === false) {
        echo "❌ Failed to load image: $source\n";
        continue;
    }

    // Convert to WebP with 80% quality
    if (imagewebp($image, $destination, 80)) {
        $originalSize = filesize($source);
        $newSize = filesize($destination);
        $saved = round((1 - $newSize / $originalSize) * 100, 1);
        echo "✅ Converted: $source → $destination (Saved: {$saved}%)\n";
        imagedestroy($image);
    } else {
        echo "❌ Failed to convert: $source\n";
        imagedestroy($image);
    }
}

echo "\n✅ Conversion complete!\n";
?>
