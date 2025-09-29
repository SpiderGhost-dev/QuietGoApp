<?php
/**
 * QuietGo Smart AI Router
 * Routes AI requests to optimal model based on complexity
 */

require_once __DIR__ . '/image-analyzer.php';
require_once __DIR__ . '/openai-config.php';

class SmartAIRouter {
    
    /**
     * Analyze image and route to appropriate AI model
     * @param string $imagePath - Path to image
     * @param array $messages - Messages for AI
     * @param string $photoType - Type of photo
     * @param int $maxTokens - Max tokens for response
     * @return array - AI response with routing metadata
     */
    public static function routeImageAnalysis($imagePath, $messages, $photoType = 'meal', $maxTokens = 1000) {
        
        // Skip complexity analysis for stool photos - always use best model
        if ($photoType === 'stool') {
            return self::callExpensiveModel($messages, $maxTokens, [
                'tier' => 'expensive',
                'reason' => 'Medical analysis requires high-quality model'
            ]);
        }

        // Analyze image complexity
        $complexity = ImageComplexityAnalyzer::analyzeImage($imagePath, $photoType);
        
        // Route based on complexity tier
        switch ($complexity['tier']) {
            case 'cheap':
                $result = self::callCheapModel($messages, $maxTokens, $complexity);
                break;
            
            case 'medium':
                $result = self::callMediumModel($messages, $maxTokens, $complexity);
                break;
            
            case 'expensive':
            default:
                $result = self::callExpensiveModel($messages, $maxTokens, $complexity);
                break;
        }

        // Add routing metadata to response
        if (!isset($result['error'])) {
            $result['routing_info'] = [
                'tier_used' => $complexity['tier'],
                'confidence' => $complexity['confidence'],
                'reasons' => $complexity['reasons'],
                'cost_tier' => self::getCostForTier($complexity['tier'])
            ];
        }

        return $result;
    }

    private static function callCheapModel($messages, $maxTokens, $complexity) {
        error_log("QuietGo Router: Using CHEAP tier (Anthropic Haiku) - Confidence: " . $complexity['confidence']);
        
        if (!defined('ANTHROPIC_API_KEY')) {
            error_log("QuietGo Router: Anthropic not configured, falling back to medium tier");
            return self::callMediumModel($messages, $maxTokens, $complexity);
        }

        return makeAnthropicRequest($messages, ANTHROPIC_HAIKU_MODEL, $maxTokens);
    }

    private static function callMediumModel($messages, $maxTokens, $complexity) {
        error_log("QuietGo Router: Using MEDIUM tier (GPT-4o-mini) - Confidence: " . $complexity['confidence']);
        
        // Use GPT-4o-mini (or GPT-3.5-turbo-16k as fallback)
        $mediumModel = 'gpt-4o-mini';
        return makeOpenAIRequest($messages, $mediumModel, $maxTokens);
    }

    private static function callExpensiveModel($messages, $maxTokens, $complexity) {
        error_log("QuietGo Router: Using EXPENSIVE tier (GPT-4 Vision) - Confidence: " . ($complexity['confidence'] ?? 'N/A'));
        
        return makeOpenAIRequest($messages, OPENAI_VISION_MODEL, $maxTokens);
    }

    private static function getCostForTier($tier) {
        $costs = [
            'cheap' => '$0.002',
            'medium' => '$0.005',
            'expensive' => '$0.015'
        ];
        return $costs[$tier] ?? '$0.015';
    }

    /**
     * Check if response needs retry with better model
     * @param array $response - AI response
     * @param string $currentTier - Tier that was used
     * @return bool - Whether retry is needed
     */
    public static function needsRetry($response, $currentTier) {
        // Don't retry expensive tier
        if ($currentTier === 'expensive') {
            return false;
        }

        // Check for low confidence in response
        if (isset($response['choices'][0]['message']['content'])) {
            $content = $response['choices'][0]['message']['content'];
            $data = json_decode($content, true);
            
            if (isset($data['confidence']) && $data['confidence'] < 70) {
                error_log("QuietGo Router: Low confidence detected ({$data['confidence']}), retry recommended");
                return true;
            }
        }

        return false;
    }
}
?>
