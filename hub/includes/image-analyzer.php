<?php
/**
 * QuietGo Image Complexity Analyzer
 * Determines which AI model to use based on image characteristics
 */

class ImageComplexityAnalyzer {
    
    /**
     * Analyze image complexity and return recommended model tier
     * @param string $imagePath - Path to image file
     * @param string $photoType - Type of photo (meal, stool, symptom)
     * @return array - ['tier' => 'cheap'|'medium'|'expensive', 'confidence' => float, 'reasons' => array]
     */
    public static function analyzeImage($imagePath, $photoType = 'meal') {
        if (!file_exists($imagePath)) {
            return ['tier' => 'expensive', 'confidence' => 0, 'reasons' => ['File not found']];
        }

        $imageInfo = getimagesize($imagePath);
        if (!$imageInfo) {
            return ['tier' => 'expensive', 'confidence' => 0, 'reasons' => ['Invalid image']];
        }

        list($width, $height, $type) = $imageInfo;
        
        // Load image for analysis
        $image = self::loadImage($imagePath, $type);
        if (!$image) {
            return ['tier' => 'expensive', 'confidence' => 0, 'reasons' => ['Could not load image']];
        }

        // Perform various complexity checks
        $checks = [
            'brightness' => self::checkBrightness($image, $width, $height),
            'blur' => self::checkBlur($image, $width, $height),
            'color_variance' => self::checkColorVariance($image, $width, $height),
            'edge_density' => self::checkEdgeDensity($image, $width, $height),
            'resolution' => self::checkResolution($width, $height)
        ];

        imagedestroy($image);

        // Calculate overall complexity score
        $analysis = self::calculateComplexity($checks, $photoType);
        
        return $analysis;
    }

    private static function loadImage($path, $type) {
        switch ($type) {
            case IMAGETYPE_JPEG:
                return @imagecreatefromjpeg($path);
            case IMAGETYPE_PNG:
                return @imagecreatefrompng($path);
            case IMAGETYPE_GIF:
                return @imagecreatefromgif($path);
            default:
                return null;
        }
    }

    private static function checkBrightness($image, $width, $height) {
        $brightness = 0;
        $sampleSize = 50; // Sample every 50th pixel for performance
        $count = 0;

        for ($x = 0; $x < $width; $x += $sampleSize) {
            for ($y = 0; $y < $height; $y += $sampleSize) {
                $rgb = imagecolorat($image, $x, $y);
                $r = ($rgb >> 16) & 0xFF;
                $g = ($rgb >> 8) & 0xFF;
                $b = $rgb & 0xFF;
                $brightness += ($r + $g + $b) / 3;
                $count++;
            }
        }

        $avgBrightness = $brightness / $count;
        
        // Good lighting: 70-210 range (more forgiving)
        // Poor lighting: < 50 or > 230
        $score = 1.0;
        if ($avgBrightness < 50 || $avgBrightness > 230) {
            $score = 0.4; // Very poor lighting = complex
        } elseif ($avgBrightness < 70 || $avgBrightness > 210) {
            $score = 0.75; // OK lighting
        }

        return [
            'score' => $score,
            'value' => $avgBrightness,
            'reason' => $score < 0.6 ? 'Poor lighting' : 'Good lighting'
        ];
    }

    private static function checkBlur($image, $width, $height) {
        // Simple blur detection using Laplacian variance
        // Sample center area - cast to int to avoid deprecation warnings
        $centerX = (int)($width / 2);
        $centerY = (int)($height / 2);
        $sampleSize = (int)min(200, $width / 2, $height / 2);

        $variance = 0;
        $count = 0;
        $step = 10;

        for ($x = $centerX - $sampleSize; $x < $centerX + $sampleSize; $x += $step) {
            for ($y = $centerY - $sampleSize; $y < $centerY + $sampleSize; $y += $step) {
                $xPos = (int)$x;
                $yPos = (int)$y;
                
                if ($xPos < 0 || $xPos >= $width - 1 || $yPos < 0 || $yPos >= $height - 1) continue;

                $center = imagecolorat($image, $xPos, $yPos);
                $right = imagecolorat($image, $xPos + 1, $yPos);
                $down = imagecolorat($image, $xPos, $yPos + 1);

                $diff = abs($center - $right) + abs($center - $down);
                $variance += $diff;
                $count++;
            }
        }

        $avgVariance = $count > 0 ? $variance / $count : 0;

        // Higher variance = sharper image = simpler for AI
        // Adjusted threshold - 20000 is more realistic for sharp food photos
        $score = min(1.0, $avgVariance / 20000);

        return [
            'score' => $score,
            'value' => $avgVariance,
            'reason' => $score < 0.6 ? 'Blurry image' : 'Sharp image'
        ];
    }

    private static function checkColorVariance($image, $width, $height) {
        $colors = [];
        $sampleSize = 50;

        for ($x = 0; $x < $width; $x += $sampleSize) {
            for ($y = 0; $y < $height; $y += $sampleSize) {
                $rgb = imagecolorat($image, $x, $y);
                $colors[] = $rgb;
            }
        }

        $uniqueColors = count(array_unique($colors));
        $totalSamples = count($colors);
        $variance = $uniqueColors / $totalSamples;

        // FIXED LOGIC: Food photos naturally have variety - high variance is GOOD
        // Very low variance (< 0.3) = boring/monochrome = harder to analyze
        // High variance (0.5-1.0) = normal colorful food = easier for AI
        if ($variance < 0.3) {
            $score = 0.6; // Low color variety = slightly harder
        } elseif ($variance < 0.5) {
            $score = 0.8; // Moderate variety = good
        } else {
            $score = 1.0; // High variety = normal for food, easy for AI
        }

        return [
            'score' => $score,
            'value' => $variance,
            'reason' => $score < 0.7 ? 'Limited color range' : 'Good color variety'
        ];
    }

    private static function checkEdgeDensity($image, $width, $height) {
        // More edges = more objects = more complex
        $edges = 0;
        $sampleSize = 20;
        $threshold = 30;

        for ($x = 0; $x < $width - 1; $x += $sampleSize) {
            for ($y = 0; $y < $height - 1; $y += $sampleSize) {
                $xPos = (int)$x;
                $yPos = (int)$y;
                
                $current = imagecolorat($image, $xPos, $yPos);
                $right = imagecolorat($image, $xPos + 1, $yPos);
                $down = imagecolorat($image, $xPos, $yPos + 1);

                if (abs($current - $right) > $threshold || abs($current - $down) > $threshold) {
                    $edges++;
                }
            }
        }

        $totalSamples = (($width / $sampleSize) * ($height / $sampleSize));
        $density = $edges / $totalSamples;

        // ADJUSTED: Less aggressive penalty for edges
        // Density 0-0.3 = simple (score 1.0-0.7)
        // Density 0.3-0.6 = moderate (score 0.7-0.4)  
        // Density > 0.6 = complex (score < 0.4)
        if ($density < 0.3) {
            $score = 1.0 - ($density * 1.0); // Simple: 1.0 to 0.7
        } elseif ($density < 0.6) {
            $score = 0.7 - (($density - 0.3) * 1.0); // Moderate: 0.7 to 0.4
        } else {
            $score = max(0.2, 0.4 - (($density - 0.6) * 0.5)); // Complex: 0.4 to 0.2
        }

        return [
            'score' => $score,
            'value' => $density,
            'reason' => $score < 0.5 ? 'Many objects/details' : 'Simple composition'
        ];
    }

    private static function checkResolution($width, $height) {
        $pixels = $width * $height;
        
        // ADJUSTED: More forgiving for typical phone photos (0.5MP - 3MP is normal)
        // Very low res < 300k = might be cropped/zoomed = complex
        // Normal phone range 300k-5MP = good = high score
        // Very high res > 5MP = excellent = perfect score
        if ($pixels < 300000) { // < 0.3MP
            $score = 0.5;
            $reason = 'Low resolution';
        } elseif ($pixels < 1000000) { // 0.3-1MP
            $score = 0.85;
            $reason = 'Adequate resolution';
        } elseif ($pixels < 5000000) { // 1-5MP
            $score = 0.95;
            $reason = 'Good resolution';
        } else { // > 5MP
            $score = 1.0;
            $reason = 'High resolution';
        }

        return [
            'score' => $score,
            'value' => $pixels,
            'reason' => $reason
        ];
    }

    private static function calculateComplexity($checks, $photoType) {
        // Weighted average of all checks
        $weights = [
            'brightness' => 0.30,  // Most important - bad lighting kills accuracy
            'blur' => 0.25,        // Second most important - blurry = useless
            'resolution' => 0.20,  // Important for detail
            'edge_density' => 0.15, // Less important - food naturally has edges
            'color_variance' => 0.10 // Least important - variety is normal
        ];

        $totalScore = 0;
        $reasons = [];

        foreach ($checks as $key => $check) {
            $totalScore += $check['score'] * $weights[$key];
            if ($check['score'] < 0.7) {
                $reasons[] = $check['reason'];
            }
        }

        // Stool photos always use high-quality model
        if ($photoType === 'stool') {
            return [
                'tier' => 'expensive',
                'confidence' => 1.0,
                'reasons' => ['Medical analysis requires high-quality model'],
                'complexity_score' => $totalScore
            ];
        }

        // ADJUSTED THRESHOLDS: More realistic for achieving cost savings
        // Target: 70% cheap, 20% medium, 10% expensive
        if ($totalScore >= 0.80) {
            $tier = 'cheap';
        } elseif ($totalScore >= 0.65) {
            $tier = 'medium';
        } else {
            $tier = 'expensive';
        }

        return [
            'tier' => $tier,
            'confidence' => $totalScore,
            'reasons' => empty($reasons) ? ['Clear, well-lit image'] : $reasons,
            'complexity_score' => $totalScore,
            'checks' => $checks
        ];
    }
}
?>
