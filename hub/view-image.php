<?php
/**
 * QuietGo Image Viewer
 * Serves protected images from storage with proper security
 */

session_start();

// Authentication check
if (!isset($_SESSION['hub_user']) && !isset($_COOKIE['hub_auth']) && !isset($_SESSION['admin_logged_in'])) {
    header('HTTP/1.1 403 Forbidden');
    exit('Access denied');
}

// Get parameters
$type = $_GET['type'] ?? 'photo';
$path = $_GET['path'] ?? '';

if (empty($path)) {
    header('HTTP/1.1 404 Not Found');
    exit('Image not found');
}

// Include storage helper
require_once __DIR__ . '/includes/storage-helper.php';
$storage = getQuietGoStorage();

// Get user email
$userEmail = $_SESSION['hub_user']['email'] ?? 'anonymous';

// Build full path based on type
$basePath = __DIR__ . '/QuietGoData/users/' . preg_replace('/[^a-zA-Z0-9@.-]/', '_', $userEmail);

switch ($type) {
    case 'thumbnail':
        $fullPath = $basePath . '/photos/thumbnails/' . basename($path);
        break;
    
    case 'stool':
        // Find in monthly folders
        $fullPath = findImageInMonthlyFolders($basePath . '/photos/stool', basename($path));
        break;
    
    case 'meal':
        $fullPath = findImageInMonthlyFolders($basePath . '/photos/meals', basename($path));
        break;
    
    case 'symptom':
        $fullPath = findImageInMonthlyFolders($basePath . '/photos/symptoms', basename($path));
        break;
    
    default:
        // Try to find in any photos folder
        $fullPath = findImageAnywhere($basePath . '/photos', basename($path));
}

// Check if file exists
if (!$fullPath || !file_exists($fullPath)) {
    // Try without user-specific path for thumbnails (backwards compatibility)
    if ($type === 'thumbnail') {
        $altPath = __DIR__ . '/QuietGoData/thumbnails/' . basename($path);
        if (file_exists($altPath)) {
            $fullPath = $altPath;
        } else {
            header('HTTP/1.1 404 Not Found');
            exit('Image not found');
        }
    } else {
        header('HTTP/1.1 404 Not Found');
        exit('Image not found');
    }
}

// Security check - ensure path doesn't escape storage directory
$realPath = realpath($fullPath);
$baseRealPath = realpath(__DIR__ . '/QuietGoData');
if (strpos($realPath, $baseRealPath) !== 0) {
    header('HTTP/1.1 403 Forbidden');
    exit('Access denied');
}

// Get image info
$imageInfo = getimagesize($fullPath);
if (!$imageInfo) {
    header('HTTP/1.1 415 Unsupported Media Type');
    exit('Not a valid image');
}

// Set appropriate content type
$mimeType = $imageInfo['mime'];
header('Content-Type: ' . $mimeType);
header('Content-Length: ' . filesize($fullPath));
header('Cache-Control: private, max-age=3600');
header('Expires: ' . gmdate('D, d M Y H:i:s', time() + 3600) . ' GMT');

// Output image
readfile($fullPath);
exit;

/**
 * Find image in monthly folders
 */
function findImageInMonthlyFolders($baseDir, $filename) {
    if (!is_dir($baseDir)) {
        return false;
    }
    
    // Check current and last 3 months
    for ($i = 0; $i < 3; $i++) {
        $month = date('Y-m', strtotime("-$i months"));
        $monthPath = $baseDir . '/' . $month . '/' . $filename;
        if (file_exists($monthPath)) {
            return $monthPath;
        }
    }
    
    // Check all folders if not found
    $dirs = glob($baseDir . '/*', GLOB_ONLYDIR);
    foreach ($dirs as $dir) {
        $filePath = $dir . '/' . $filename;
        if (file_exists($filePath)) {
            return $filePath;
        }
    }
    
    return false;
}

/**
 * Find image anywhere in photos directory
 */
function findImageAnywhere($baseDir, $filename) {
    if (!is_dir($baseDir)) {
        return false;
    }
    
    $iterator = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($baseDir),
        RecursiveIteratorIterator::SELF_FIRST
    );
    
    foreach ($iterator as $file) {
        if ($file->isFile() && $file->getFilename() === $filename) {
            return $file->getPathname();
        }
    }
    
    return false;
}
?>
