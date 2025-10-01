<?php
/**
 * QuietGo Smart AI Router v3.2
 * Intelligent model tier selection based on image complexity and available data
 * 
 * Routes analysis requests to optimal model tier:
 * - cheap: Simple meals with good scale cues
 * - standard: Default for most meals
 * - expensive: Complex dishes, poor lighting, no scale cues
 * - brand_lookup: Branded/chain foods with known nutrition
 */

require_once __DIR__ . '/image-analyzer.php';

class SmartAIRouter
{
    /**
     * Route image analysis to appropriate tier
     * 
     * @param string $imagePath Path to image
     * @param array $imageAnalysis Output from CalcuPlateImageAnalyzer::analyzeComplete()
     * @param string $photoType Type: meal, stool, symptom
     * @return array Routing decision with tier and reasoning
     */
    public static function routeImageAnalysis(string $imagePath, array $imageAnalysis, string $photoType): array
    {
        // Stool photos always use expensive tier (medical accuracy required)
        if ($photoType === 'stool') {
            return [
                'tier' => 'expensive',
                'model' => OPENAI_VISION_MODEL,
                'reasoning' => 'Medical analysis requires highest quality model',
                'estimated_cost' => 0.015
            ];
        }

        // Check for brand detection
        if (!empty($imageAnalysis['brand_hints']['detected'])) {
            $brand = $imageAnalysis['brand_hints']['detected'][0];
            return [
                'tier' => 'brand_lookup',
                'model' => OPENAI_VISION_MODEL, // Still use vision for verification
                'brand' => $brand,
                'reasoning' => "Brand detected: $brand - will use canonical nutrition data",
                'estimated_cost' => 0.005 // Lower cost due to less analysis needed
            ];
        }

        // Score complexity factors
        $complexityScore = self::calculateComplexityScore($imageAnalysis);
        
        // Route based on complexity score
        if ($complexityScore <= 30) {
            return [
                'tier' => 'cheap',
                'model' => 'gpt-4o-mini', // Faster, cheaper for simple cases
                'reasoning' => 'Simple meal with good scale cues',
                'complexity_score' => $complexityScore,
                'estimated_cost' => 0.002
            ];
        } elseif ($complexityScore <= 60) {
            return [
                'tier' => 'standard',
                'model' => OPENAI_VISION_MODEL,
                'reasoning' => 'Standard complexity meal',
                'complexity_score' => $complexityScore,
                'estimated_cost' => 0.010
            ];
        } else {
            return [
                'tier' => 'expensive',
                'model' => OPENAI_VISION_MODEL,
                'reasoning' => 'Complex meal requiring detailed analysis',
                'complexity_score' => $complexityScore,
                'estimated_cost' => 0.015
            ];
        }
    }

    /**
     * Calculate complexity score (0-100)
     * Lower score = simpler meal = cheaper model possible
     * 
     * @param array $imageAnalysis
     * @return int Complexity score
     */
    private static function calculateComplexityScore(array $imageAnalysis): int
    {
        $score = 50; // Start at medium complexity

        // Depth data availability (reduces complexity)
        if ($imageAnalysis['depth']['available'] === true) {
            $score -= 15;
        } elseif ($imageAnalysis['depth']['available'] === 'possible') {
            $score -= 5;
        }

        // Device with LiDAR (better scale estimation)
        if ($imageAnalysis['exif']['has_lidar'] ?? false) {
            $score -= 10;
        }

        // Image quality factors
        $fileSize = $imageAnalysis['metadata']['file_size'] ?? 0;
        if ($fileSize < 1 * 1024 * 1024) { // < 1MB = lower quality
            $score += 15;
        } elseif ($fileSize > 5 * 1024 * 1024) { // > 5MB = high quality
            $score -= 10;
        }

        // Image dimensions
        $width = $imageAnalysis['metadata']['image_dimensions']['width'] ?? 0;
        $height = $imageAnalysis['metadata']['image_dimensions']['height'] ?? 0;
        
        if ($width > 0 && $height > 0) {
            $pixels = $width * $height;
            
            if ($pixels < 500000) { // < 0.5MP = low res, harder
                $score += 20;
            } elseif ($pixels > 8000000) { // > 8MP = high res, easier
                $score -= 10;
            }
        }

        // Focal length (if available) can indicate how close/far shot was taken
        $focalLength = $imageAnalysis['exif']['focal_length_mm'] ?? null;
        if ($focalLength !== null) {
            // Typical phone focal lengths: 24-28mm
            // Very wide angle (< 20mm) or telephoto (> 50mm) can be harder
            if ($focalLength < 20 || $focalLength > 50) {
                $score += 10;
            }
        }

        // Device quality hints
        $device = strtolower($imageAnalysis['exif']['device'] ?? '');
        
        // Flagship devices with good cameras
        $premiumDevices = ['iphone 13', 'iphone 14', 'iphone 15', 'iphone 16', 
                          'pixel 7', 'pixel 8', 'galaxy s22', 'galaxy s23', 'galaxy s24'];
        
        foreach ($premiumDevices as $premium) {
            if (strpos($device, $premium) !== false) {
                $score -= 10;
                break;
            }
        }

        // Clamp to 0-100
        return max(0, min(100, $score));
    }

    /**
     * Estimate API cost based on routing decision
     * 
     * @param array $routingDecision
     * @return float Estimated cost in USD
     */
    public static function estimateCost(array $routingDecision): float
    {
        return $routingDecision['estimated_cost'] ?? 0.010;
    }

    /**
     * Log routing decision for analytics
     * 
     * @param array $routingDecision
     * @param string $userEmail
     */
    public static function logRoutingDecision(array $routingDecision, string $userEmail)
    {
        $logEntry = [
            'timestamp' => time(),
            'datetime' => date('Y-m-d H:i:s'),
            'user_email' => $userEmail,
            'tier' => $routingDecision['tier'],
            'model' => $routingDecision['model'],
            'reasoning' => $routingDecision['reasoning'],
            'complexity_score' => $routingDecision['complexity_score'] ?? null,
            'estimated_cost' => $routingDecision['estimated_cost'],
            'brand' => $routingDecision['brand'] ?? null
        ];

        $logDir = __DIR__ . '/../logs';
        if (!is_dir($logDir)) {
            mkdir($logDir, 0755, true);
        }

        $logFile = $logDir . '/routing_' . date('Y-m') . '.json';
        
        $existingLogs = [];
        if (file_exists($logFile)) {
            $existingLogs = json_decode(file_get_contents($logFile), true) ?: [];
        }

        $existingLogs[] = $logEntry;
        file_put_contents($logFile, json_encode($existingLogs, JSON_PRETTY_PRINT));
    }

    /**
     * Get routing statistics for analysis
     * 
     * @param string $month Format: Y-m (e.g., "2025-10")
     * @return array Statistics
     */
    public static function getRoutingStats(string $month = null): ?array
    {
        $month = $month ?: date('Y-m');
        $logFile = __DIR__ . '/../logs/routing_' . $month . '.json';

        if (!file_exists($logFile)) {
            return null;
        }

        $logs = json_decode(file_get_contents($logFile), true);
        
        $stats = [
            'total_requests' => count($logs),
            'total_cost' => 0,
            'by_tier' => [
                'cheap' => ['count' => 0, 'cost' => 0],
                'standard' => ['count' => 0, 'cost' => 0],
                'expensive' => ['count' => 0, 'cost' => 0],
                'brand_lookup' => ['count' => 0, 'cost' => 0]
            ],
            'avg_complexity_score' => 0,
            'brands_detected' => []
        ];

        $complexityScores = [];

        foreach ($logs as $log) {
            $tier = $log['tier'];
            $cost = $log['estimated_cost'];
            
            $stats['total_cost'] += $cost;
            $stats['by_tier'][$tier]['count']++;
            $stats['by_tier'][$tier]['cost'] += $cost;
            
            if (isset($log['complexity_score'])) {
                $complexityScores[] = $log['complexity_score'];
            }

            if (isset($log['brand'])) {
                $brand = $log['brand'];
                if (!isset($stats['brands_detected'][$brand])) {
                    $stats['brands_detected'][$brand] = 0;
                }
                $stats['brands_detected'][$brand]++;
            }
        }

        if (!empty($complexityScores)) {
            $stats['avg_complexity_score'] = round(array_sum($complexityScores) / count($complexityScores), 1);
        }

        $stats['avg_cost_per_request'] = $stats['total_requests'] > 0 
            ? round($stats['total_cost'] / $stats['total_requests'], 4)
            : 0;

        return $stats;
    }

    /**
     * Override routing for testing or manual control
     * 
     * @param string $forceTier Force specific tier: cheap|standard|expensive
     * @return array Forced routing decision
     */
    public static function forceRouting(string $forceTier): array
    {
        $tiers = [
            'cheap' => [
                'tier' => 'cheap',
                'model' => 'gpt-4o-mini',
                'reasoning' => 'Forced routing (testing)',
                'estimated_cost' => 0.002
            ],
            'standard' => [
                'tier' => 'standard',
                'model' => OPENAI_VISION_MODEL,
                'reasoning' => 'Forced routing (testing)',
                'estimated_cost' => 0.010
            ],
            'expensive' => [
                'tier' => 'expensive',
                'model' => OPENAI_VISION_MODEL,
                'reasoning' => 'Forced routing (testing)',
                'estimated_cost' => 0.015
            ]
        ];

        return $tiers[$forceTier] ?? $tiers['standard'];
    }
}

