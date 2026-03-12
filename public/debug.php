<?php
echo "<pre>";
echo "Document root: " . $_SERVER['DOCUMENT_ROOT'] . "\n\n";
echo "Files in uploads/products:\n";
$path = __DIR__ . "/uploads/products";
if (is_dir($path)) {
    print_r(scandir($path));
} else {
    echo "Directory not found: $path";
}
