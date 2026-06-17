<?php

$path = __DIR__ . '/uploads/logos/cargo';

echo "Attempting to fix permissions for: " . $path . "<br>";

if (!file_exists($path)) {
    if (mkdir($path, 0777, true)) {
        echo "Directory created.<br>";
    } else {
        echo "Failed to create directory.<br>";
    }
}

if (chmod($path, 0777)) {
    echo "Permissions set to 777 successfully.<br>";
} else {
    echo "Failed to set permissions. Please contact your hosting provider or use FTP to set 'public/uploads/logos/cargo' to 755.<br>";
}

echo "<br>You can now delete this file (fix-permissions.php).";
