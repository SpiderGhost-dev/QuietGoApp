<?php
/**
 * QuietGo Image Analyzer v3.2
 * Enhanced depth camera integration and pre-detection analysis
 * 
 * Capabilities:
 * - Extract depth data from iPhone LiDAR and Android depth sensors
 * - Read EXIF metadata for device info and scale hints
 * - Basic brand logo detection (expandable)
 * - Quick complexity assessment for routing
 */

class CalcuPlateImageAnalyzer
{
    /**
     * Analyze image and extract all available metadata
     * 
     * @param string $imagePath Path to uploaded image
     * @return array Complete analysis with depth, EXIF, and hints
     */
    public static function analyzeComplete(string $imagePath): array
    {
        if (!file_exists($imagePath)) {
            return ['error' => 'File not found'];
        }

        $analysis = [
            'exif' => self::extractEXIF($imagePath),
            'depth' => self::extractDepthData($imagePath),
            'brand_hints' => self::detectBrandHints($imagePath),
            'complexity' => self::assessComplexity($imagePath),
            'metadata' => [
                'file_size' => filesize($imagePath),
                'image_dimensions' => self::getImageDimensions($imagePath),
                'mime_type' => mime_content_type($imagePath)
            ]
        ];

        return $analysis;
    }

    /**
     * Extract EXIF metadata for device info and camera settings
     * 
     * @param string $imagePath
     * @return array EXIF data including device model, focal length
     */
    private static function extractEXIF(string $imagePath): array
    {
        $exif = @exif_read_data($imagePath, 'IFD0,EXIF');
        
        if (!$exif) {
            return [
                'available' => false,
                'device' => 'unknown',
                'focal_length_mm' => null
            ];
        }

        // Extract key fields
        $device = $exif['Model'] ?? $exif['Make'] ?? 'unknown';
        $focalLength = null;
        
        if (isset($exif['FocalLength'])) {
            // FocalLength can be a fraction string like "26/1"
            if (is_string($exif['FocalLength']) && strpos($exif['FocalLength'], '/') !== false) {
                list($num, $den) = explode('/', $exif['FocalLength']);
                $focalLength = $den > 0 ? $num / $den : null;
            } else {
                $focalLength = floatval($exif['FocalLength']);
            }
        }

        // Detect iPhone models with LiDAR
        $hasLiDAR = self::deviceHasLiDAR($device);

        return [
            'available' => true,
            'device' => $device,
            'make' => $exif['Make'] ?? null,
            'model' => $exif['Model'] ?? null,
            'focal_length_mm' => $focalLength,
            'datetime' => $exif['DateTime'] ?? null,
            'orientation' => $exif['Orientation'] ?? 1,
            'has_lidar' => $hasLiDAR,
            'raw_exif' => $exif
        ];
    }

    /**
     * Detect if device has LiDAR or depth sensor capability
     * 
     * @param string $device Device model string
     * @return bool
     */
    private static function deviceHasLiDAR(string $device): bool
    {
        $lidarDevices = [
            // iPhone models with LiDAR
            'iPhone 12 Pro', 'iPhone 12 Pro Max',
            'iPhone 13 Pro', 'iPhone 13 Pro Max',
            'iPhone 14 Pro', 'iPhone 14 Pro Max',
            'iPhone 15 Pro', 'iPhone 15 Pro Max',
            'iPhone 16 Pro', 'iPhone 16 Pro Max',
            'iPad Pro', // iPad Pro models 2020+
        ];

        foreach ($lidarDevices as $lidarDevice) {
            if (stripos($device, $lidarDevice) !== false) {
                return true;
            }
        }

        // Android devices with depth sensors (common flagships)
        $androidDepthDevices = [
            'Pixel', 'Galaxy S', 'Galaxy Note', 'OnePlus'
        ];

        foreach ($androidDepthDevices as $androidDevice) {
            if (stripos($device, $androidDevice) !== false) {
                return true; // Most flagships have depth sensors
            }
        }

        return false;
    }

    /**
     * Extract depth data if available
     * 
     * For iPhone: Look for depth map in HEIC/ProRAW files
     * For Android: Look for depth metadata
     * 
     * @param string $imagePath
     * @return array Depth data or indication of availability
     */
    private static function extractDepthData(string $imagePath): array
    {
        $result = [
            'available' => false,
            'source' => null,
            'depth_map' => null,
            'format' => null
        ];

        // Check file extension
        $ext = strtolower(pathinfo($imagePath, PATHINFO_EXTENSION));

        // HEIC files (iPhone) may contain depth maps
        if ($ext === 'heic' || $ext === 'heif') {
            // Note: Full depth extraction from HEIC requires specialized libraries
            // For now, mark as potentially available
            $result['available'] = 'possible';
            $result['source'] = 'heic_container';
            $result['format'] = 'heic_depth_map';
            $result['note'] = 'HEIC file detected - depth may be embedded';
        }

        // DNG/ProRAW files (iPhone Pro models) contain depth
        if ($ext === 'dng') {
            $result['available'] = 'possible';
            $result['source'] = 'proraw';
            $result['format'] = 'dng_depth_map';
            $result['note'] = 'ProRAW file detected - depth available';
        }

        // TODO: Implement actual depth map extraction
        // This requires libraries like:
        // - php-heif for HEIC depth extraction
        // - Custom parsers for DNG depth maps
        // - Android depth metadata parsers

        return $result;
    }

    /**
     * Detect brand hints from filename, EXIF, or quick visual scan
     * 
     * @param string $imagePath
     * @return array Brand detection results
     */
    private static function detectBrandHints(string $imagePath): array
    {
        $brands = [
            'detected' => [],
            'confidence' => 0.0
        ];

        $filename = strtolower(basename($imagePath));

        // Quick filename-based detection (basic but fast)
        $brandPatterns = [
            'mcdonalds' => ['mcd', 'mcdo', 'mcdonald'],
            'burger_king' => ['bk', 'burger king', 'burgerking'],
            'starbucks' => ['sbux', 'starbucks', 'sbuck'],
            'chipotle' => ['chipotle', 'chip'],
            'subway' => ['subway', 'sub'],
            'pizza_hut' => ['pizza hut', 'pizzahut'],
            'dominos' => ['domino', 'dominos'],
            'taco_bell' => ['taco bell', 'tacobell'],
            'wendys' => ['wendy', 'wendys'],
            'chick_fil_a' => ['chick fil a', 'chickfila', 'cfa'],
            'panera' => ['panera'],
            'olive_garden' => ['olive garden', 'olivegarden'],
        ];

        foreach ($brandPatterns as $brand => $patterns) {
            foreach ($patterns as $pattern) {
                if (strpos($filename, $pattern) !== false) {
                    $brands['detected'][] = $brand;
                    $brands['confidence'] = 0.6; // Filename-based = moderate confidence
                    break 2;
                }
            }
        }

        // TODO: Implement actual visual brand detection
        // This would require:
        // - Logo detection model
        // - OCR for brand text on packaging
        // - Color pattern recognition (McDonald's red/yellow, Starbucks green)

        return $brands;
    }

    /**
     * Quick complexity assessment for routing decisions
     * 
     * @param string $imagePath
     * @return array Complexity metrics
     */
    private static function assessComplexity(string $imagePath): array
    {
        $dimensions = self::getImageDimensions($imagePath);
        
        // Basic heuristics (can be enhanced with actual CV)
        $complexity = [
            'estimated_items' => 'unknown', // Would need detection
            'has_plate' => 'unknown',
            'has_utensils' => 'unknown',
            'recommended_tier' => 'standard',
            'reasoning' => []
        ];

        // File size can hint at complexity
        $fileSize = filesize($imagePath);
        if ($fileSize > 5 * 1024 * 1024) { // > 5MB
            $complexity['reasoning'][] = 'High resolution suggests detailed capture';
        }

        // Aspect ratio hints
        if ($dimensions['width'] && $dimensions['height']) {
            $ratio = $dimensions['width'] / $dimensions['height'];
            if ($ratio > 1.5 || $ratio < 0.67) {
                $complexity['reasoning'][] = 'Unusual aspect ratio may indicate cropped or specialty shot';
            }
        }

        return $complexity;
    }

    /**
     * Get image dimensions
     * 
     * @param string $imagePath
     * @return array Width and height
     */
    private static function getImageDimensions(string $imagePath): array
    {
        $size = @getimagesize($imagePath);
        
        if (!$size) {
            return ['width' => null, 'height' => null];
        }

        return [
            'width' => $size[0],
            'height' => $size[1]
        ];
    }

    /**
     * Prepare context for CalcuPlate analysis
     * Formats extracted data for prompt consumption
     * 
     * @param array $analysis Full analysis from analyzeComplete()
     * @return array Formatted context for AI
     */
    public static function prepareAnalysisContext(array $analysis): array
    {
        $context = [
            'device' => $analysis['exif']['device'] ?? 'unknown',
            'focal_length_mm' => $analysis['exif']['focal_length_mm'] ?? null,
            'has_depth_data' => ($analysis['depth']['available'] === true || $analysis['depth']['available'] === 'possible'),
            'depth_source' => $analysis['depth']['source'] ?? null,
            'has_lidar' => $analysis['exif']['has_lidar'] ?? false,
            'image_width' => $analysis['metadata']['image_dimensions']['width'] ?? null,
            'image_height' => $analysis['metadata']['image_dimensions']['height'] ?? null,
            'file_size_mb' => round(($analysis['metadata']['file_size'] ?? 0) / 1024 / 1024, 2),
            'brand_detected' => !empty($analysis['brand_hints']['detected']) ? $analysis['brand_hints']['detected'][0] : null,
            'brand_confidence' => $analysis['brand_hints']['confidence'] ?? 0.0
        ];

        return $context;
    }

    /**
     * Simple routing recommendation based on available data
     * 
     * @param array $analysis
     * @return string 'cheap'|'standard'|'expensive'|'brand_lookup'
     */
    public static function recommendTier(array $analysis): string
    {
        // Brand detected = use brand lookup
        if (!empty($analysis['brand_hints']['detected'])) {
            return 'brand_lookup';
        }

        // Has depth data = can use cheaper model with confidence
        if ($analysis['depth']['available'] === true) {
            return 'cheap';
        }

        // High quality capture on capable device
        if ($analysis['exif']['has_lidar'] ?? false) {
            return 'standard';
        }

        // Default to standard
        return 'standard';
    }
}

