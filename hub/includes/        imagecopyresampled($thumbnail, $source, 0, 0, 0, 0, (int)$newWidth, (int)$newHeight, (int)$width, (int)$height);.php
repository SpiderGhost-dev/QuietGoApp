319
<?php
/**
 * QuietGo Hub - Professional Storage Management
 * Handles organized data structure for mobile sync and uploads
 */

class QuietGoStorage {
    private $basePath;
    private $config;

    public function __construct() {
        $this->loadConfig();
        $this->ensureBaseStructure();
    }

    /**
     * Load storage configuration
     */
    private function loadConfig() {
        $configFile = __DIR__ . '/../../config/storage-config.php';

        if (file_exists($configFile)) {
            $this->config = include $configFile;
        } else {
            // Default configuration
            $this->config = [
                'base_path' => __DIR__ . '/../QuietGoData',
                'auto_cleanup' => true,
                'compression' => false,
                'max_file_size' => 20 * 1024 * 1024, // 20MB
                'thumbnail_size' => 300
            ];

            // Create config file for future use
            $this->createConfigFile();
        }

        $this->basePath = $this->config['base_path'];
    }

    /**
     * Create storage configuration file
     */
    private function createConfigFile() {
        $configDir = __DIR__ . '/../../config';
        if (!is_dir($configDir)) {
            mkdir($configDir, 0755, true);
        }

        $configContent = "<?php\n// QuietGo Storage Configuration\nreturn " . var_export($this->config, true) . ";\n";
        file_put_contents($configDir . '/storage-config.php', $configContent);
    }

    /**
     * Ensure base directory structure exists
     */
    private function ensureBaseStructure() {
        $systemDirs = [
            $this->basePath,
            $this->basePath . '/users',
            $this->basePath . '/system/logs',
            $this->basePath . '/system/cache',
            $this->basePath . '/system/temp',
            $this->basePath . '/system/api_usage',
            $this->basePath . '/backups/daily',
            $this->basePath . '/backups/weekly'
        ];

        foreach ($systemDirs as $dir) {
            if (!is_dir($dir)) {
                mkdir($dir, 0755, true);
            }
        }
    }

    /**
     * Create complete user directory structure
     */
    public function createUserStructure($userEmail) {
        $userPath = $this->getUserPath($userEmail);

        $userDirs = [
            $userPath,
            $userPath . '/photos/stool',
            $userPath . '/photos/meals',
            $userPath . '/photos/symptoms',
            $userPath . '/photos/thumbnails',
            $userPath . '/logs/manual_meals',
            $userPath . '/logs/quick_symptoms',
            $userPath . '/logs/energy_levels',
            $userPath . '/logs/sleep_tracking',
            $userPath . '/logs/custom_notes',
            $userPath . '/analysis/ai_results',
            $userPath . '/analysis/patterns',
            $userPath . '/analysis/correlations',
            $userPath . '/analysis/trends',
            $userPath . '/sync/mobile_backups',
            $userPath . '/sync/conflict_resolution',
            $userPath . '/sync/offline_queue',
            $userPath . '/exports/reports_pdf',
            $userPath . '/exports/data_csv',
            $userPath . '/exports/provider_shares',
            $userPath . '/app_data',
            $userPath . '/cache'
        ];

        foreach ($userDirs as $dir) {
            if (!is_dir($dir)) {
                mkdir($dir, 0755, true);
            }
        }

        // Create user metadata files
        $this->initializeUserMetadata($userEmail);

        return $userPath;
    }

    /**
     * Initialize user metadata files
     */
    private function initializeUserMetadata($userEmail) {
        $userPath = $this->getUserPath($userEmail);

        // User preferences
        if (!file_exists($userPath . '/app_data/user_preferences.json')) {
            $defaultPrefs = [
                'email' => $userEmail,
                'created' => date('Y-m-d H:i:s'),
                'journey' => 'best_life',
                'timezone' => 'America/New_York',
                'privacy_level' => 'high',
                'auto_backup' => true
            ];
            file_put_contents($userPath . '/app_data/user_preferences.json', json_encode($defaultPrefs, JSON_PRETTY_PRINT));
        }

        // Sync history
        if (!file_exists($userPath . '/sync/sync_history.json')) {
            $syncHistory = [
                'initial_sync' => date('Y-m-d H:i:s'),
                'last_sync' => null,
                'sync_count' => 0,
                'mobile_device_id' => null
            ];
            file_put_contents($userPath . '/sync/sync_history.json', json_encode($syncHistory, JSON_PRETTY_PRINT));
        }
    }

    /**
     * Store photo with organized structure
     */
    public function storePhoto($userEmail, $photoType, $file, $metadata) {
        $userPath = $this->getUserPath($userEmail);
        $month = date('Y-m');

        // Determine photo directory
        $photoDir = $userPath . '/photos/' . $photoType . '/' . $month;
        if (!is_dir($photoDir)) {
            mkdir($photoDir, 0755, true);
        }

        // Generate filename
        $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
        $filename = date('Ymd_His') . '_' . uniqid() . '.' . $extension;
        $filepath = $photoDir . '/' . $filename;

        // Move uploaded file
        if (move_uploaded_file($file['tmp_name'], $filepath)) {
            // Create thumbnail
            $thumbnailPath = $this->createThumbnail($filepath, $userEmail);

            // Save metadata
            $metadataFile = $photoDir . '/' . pathinfo($filename, PATHINFO_FILENAME) . '_metadata.json';
            $metadata['storage'] = [
                'filepath' => $filepath,
                'thumbnail' => $thumbnailPath,
                'size' => filesize($filepath),
                'stored_at' => date('Y-m-d H:i:s')
            ];
            file_put_contents($metadataFile, json_encode($metadata, JSON_PRETTY_PRINT));

            return [
                'success' => true,
                'filepath' => $filepath,
                'thumbnail' => $thumbnailPath,
                'metadata_file' => $metadataFile
            ];
        }

        return ['success' => false, 'error' => 'Failed to store file'];
    }

    /**
     * Store analysis results
     */
    public function storeAnalysis($userEmail, $analysisType, $data) {
        $userPath = $this->getUserPath($userEmail);
        $month = date('Y-m');

        $analysisDir = $userPath . '/analysis/' . $analysisType . '/' . $month;
        if (!is_dir($analysisDir)) {
            mkdir($analysisDir, 0755, true);
        }

        $filename = date('Ymd_His') . '_' . uniqid() . '.json';
        $filepath = $analysisDir . '/' . $filename;

        file_put_contents($filepath, json_encode($data, JSON_PRETTY_PRINT));
        return $filepath;
    }

    /**
     * Store manual log data
     */
    public function storeLog($userEmail, $logType, $data) {
        $userPath = $this->getUserPath($userEmail);
        $month = date('Y-m');

        $logDir = $userPath . '/logs/' . $logType . '/' . $month;
        if (!is_dir($logDir)) {
            mkdir($logDir, 0755, true);
        }

        $filename = date('Ymd_His') . '_' . uniqid() . '.json';
        $filepath = $logDir . '/' . $filename;

        file_put_contents($filepath, json_encode($data, JSON_PRETTY_PRINT));
        return $filepath;
    }

    /**
     * Handle complete mobile sync
     */
    public function handleMobileSync($userEmail, $mobileData) {
        // Ensure user structure exists
        $this->createUserStructure($userEmail);

        $syncResults = [
            'photos_synced' => 0,
            'logs_synced' => 0,
            'analysis_synced' => 0,
            'errors' => []
        ];

        // Process photos
        if (isset($mobileData['photos'])) {
            foreach ($mobileData['photos'] as $photo) {
                try {
                    $this->syncPhoto($userEmail, $photo);
                    $syncResults['photos_synced']++;
                } catch (Exception $e) {
                    $syncResults['errors'][] = 'Photo sync error: ' . $e->getMessage();
                }
            }
        }

        // Process logs
        if (isset($mobileData['logs'])) {
            foreach ($mobileData['logs'] as $log) {
                try {
                    $this->syncLog($userEmail, $log);
                    $syncResults['logs_synced']++;
                } catch (Exception $e) {
                    $syncResults['errors'][] = 'Log sync error: ' . $e->getMessage();
                }
            }
        }

        // Update sync history
        $this->updateSyncHistory($userEmail, $syncResults);

        return $syncResults;
    }

    /**
     * Create thumbnail
     */
    private function createThumbnail($imagePath, $userEmail) {
        $userPath = $this->getUserPath($userEmail);
        $thumbnailDir = $userPath . '/photos/thumbnails';

        if (!is_dir($thumbnailDir)) {
            mkdir($thumbnailDir, 0755, true);
        }

        $filename = basename($imagePath);
        $thumbnailPath = $thumbnailDir . '/thumb_' . $filename;

        // Get image info
        list($width, $height, $type) = getimagesize($imagePath);

        // Create image resource
        switch ($type) {
            case IMAGETYPE_JPEG:
                $source = imagecreatefromjpeg($imagePath);
                break;
            case IMAGETYPE_PNG:
                $source = imagecreatefrompng($imagePath);
                break;
            case IMAGETYPE_GIF:
                $source = imagecreatefromgif($imagePath);
                break;
            default:
                return null;
        }

        // Calculate thumbnail dimensions
        $maxSize = $this->config['thumbnail_size'];
        if ($width > $height) {
            $newWidth = (int)$maxSize;
            $newHeight = (int)(($height / $width) * $maxSize);
        } else {
            $newHeight = (int)$maxSize;
            $newWidth = (int)(($width / $height) * $maxSize);
        }

    // Create thumbnail
    $thumbnail = imagecreatetruecolor((int)$newWidth, (int)$newHeight);
    imagecopyresampled($thumbnail, $source, 0, 0, 0, 0, (int)$newWidth, (int)$newHeight, (int)$width, (int)$height);
    // Save thumbnail
    switch ($type) {
            case IMAGETYPE_JPEG:
                imagejpeg($thumbnail, $thumbnailPath, 85);
                break;
            case IMAGETYPE_PNG:
                imagepng($thumbnail, $thumbnailPath);
                break;
            case IMAGETYPE_GIF:
                imagegif($thumbnail, $thumbnailPath);
                break;
        }

        // Clean up
        imagedestroy($source);
        imagedestroy($thumbnail);

        return $thumbnailPath;
    }

    /**
     * Get user directory path
     */
    private function getUserPath($userEmail) {
        $safeEmail = preg_replace('/[^a-zA-Z0-9@.-]/', '_', $userEmail);
        return $this->basePath . '/users/' . $safeEmail;
    }

    /**
     * Update sync history
     */
    private function updateSyncHistory($userEmail, $syncResults) {
        $userPath = $this->getUserPath($userEmail);
        $historyFile = $userPath . '/sync/sync_history.json';

        if (file_exists($historyFile)) {
            $history = json_decode(file_get_contents($historyFile), true);
        } else {
            $history = ['sync_count' => 0];
        }

        $history['last_sync'] = date('Y-m-d H:i:s');
        $history['sync_count']++;
        $history['last_results'] = $syncResults;

        file_put_contents($historyFile, json_encode($history, JSON_PRETTY_PRINT));
    }

    /**
     * Sync individual photo from mobile
     */
    private function syncPhoto($userEmail, $photoData) {
        // TODO: Implement mobile photo sync logic
        // This will handle downloading photos from mobile API
    }

    /**
     * Sync individual log from mobile
     */
    private function syncLog($userEmail, $logData) {
        // TODO: Implement mobile log sync logic
        // This will handle syncing manual logs from mobile
    }

    /**
     * Get storage statistics for user
     */
    public function getUserStats($userEmail) {
        $userPath = $this->getUserPath($userEmail);

        if (!is_dir($userPath)) {
            return null;
        }

        $stats = [
            'total_photos' => 0,
            'stool_photos' => 0,
            'meal_photos' => 0,
            'symptom_photos' => 0,
            'manual_logs' => 0,
            'analysis_files' => 0,
            'storage_used' => 0
        ];

        // Count photos by type
        $photoTypes = ['stool', 'meals', 'symptoms'];
        foreach ($photoTypes as $type) {
            $photoDir = $userPath . '/photos/' . $type;
            if (is_dir($photoDir)) {
                $count = $this->countFilesInDir($photoDir, ['jpg', 'jpeg', 'png', 'gif']);
                $stats[$type . '_photos'] = $count;
                $stats['total_photos'] += $count;
            }
        }

        // Count logs
        $logDir = $userPath . '/logs';
        if (is_dir($logDir)) {
            $stats['manual_logs'] = $this->countFilesInDir($logDir, ['json']);
        }

        // Count analysis files
        $analysisDir = $userPath . '/analysis';
        if (is_dir($analysisDir)) {
            $stats['analysis_files'] = $this->countFilesInDir($analysisDir, ['json']);
        }

        // Calculate storage used
        $stats['storage_used'] = $this->calculateDirectorySize($userPath);

        return $stats;
    }

    /**
     * Count files in directory by extension
     */
    private function countFilesInDir($dir, $extensions) {
        $count = 0;

        if (is_dir($dir)) {
            $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($dir));
            foreach ($iterator as $file) {
                if ($file->isFile()) {
                    $ext = strtolower(pathinfo($file->getFilename(), PATHINFO_EXTENSION));
                    if (in_array($ext, $extensions)) {
                        $count++;
                    }
                }
            }
        }

        return $count;
    }

    /**
     * Calculate directory size
     */
    private function calculateDirectorySize($dir) {
        $size = 0;

        if (is_dir($dir)) {
            $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($dir));
            foreach ($iterator as $file) {
                if ($file->isFile()) {
                    $size += $file->getSize();
                }
            }
        }

        return $size;
    }
}

// Global helper function
function getQuietGoStorage() {
    static $storage = null;
    if ($storage === null) {
        $storage = new QuietGoStorage();
    }
    return $storage;
}
?>
