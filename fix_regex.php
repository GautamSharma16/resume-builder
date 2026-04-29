<?php

$filePath = __DIR__ . '/resources/views/resume/partials/editor-script.blade.php';
$newFilePath = __DIR__ . '/resources/views/resume/partials/editor-script-new.blade.php';

// Read the new fixed file
$newContent = file_get_contents($newFilePath);

// Backup the original
copy($filePath, $filePath . '.bak');

// Write the fixed content
file_put_contents($filePath, $newContent);

// Delete the temp file
unlink($newFilePath);

echo "✓ File fixed successfully!\n";
echo "✓ Backup saved to editor-script.blade.php.bak\n";

?>
