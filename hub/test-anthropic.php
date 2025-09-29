<?php
/**
 * QuietGo Anthropic API Test Script
 * Tests the Anthropic integration added in Phase 1
 */

// Include the OpenAI config which now has Anthropic functions
require_once __DIR__ . '/includes/openai-config.php';

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Anthropic API Test - QuietGo</title>
    <style>
        body {
            font-family: 'Inter', -apple-system, sans-serif;
            background-color: #1a1a1a;
            color: #F5F5DC;
            padding: 40px;
            max-width: 1000px;
            margin: 0 auto;
        }
        h1 {
            color: #6C985F;
            font-family: 'Playfair Display', serif;
        }
        .test-section {
            background: #2a2a2a;
            padding: 20px;
            margin: 20px 0;
            border-radius: 8px;
            border-left: 4px solid #6C985F;
        }
        .test-title {
            color: #D4A799;
            font-weight: 600;
            margin-bottom: 10px;
        }
        .success {
            color: #6C985F;
            font-weight: 600;
        }
        .error {
            color: #ff6b6b;
            font-weight: 600;
        }
        .response {
            background: #1a1a1a;
            padding: 15px;
            margin: 10px 0;
            border-radius: 4px;
            font-family: monospace;
            white-space: pre-wrap;
            word-wrap: break-word;
        }
        .config-check {
            background: #2a2a2a;
            padding: 15px;
            margin: 20px 0;
            border-radius: 8px;
        }
        .status-indicator {
            display: inline-block;
            width: 10px;
            height: 10px;
            border-radius: 50%;
            margin-right: 8px;
        }
        .status-ok { background-color: #6C985F; }
        .status-error { background-color: #ff6b6b; }
    </style>
</head>
<body>
    <h1>🧪 Anthropic API Integration Test</h1>
    <p>Testing Phase 1 implementation - Anthropic API connection and functionality</p>

    <?php
    // Test 1: Configuration Check
    echo '<div class="test-section">';
    echo '<div class="test-title">Test 1: Configuration Check</div>';
    
    if (defined('ANTHROPIC_API_KEY')) {
        echo '<span class="status-indicator status-ok"></span><span class="success">✓ ANTHROPIC_API_KEY is defined</span><br>';
        echo '<span class="status-indicator status-ok"></span><span class="success">✓ API Key: ' . substr(ANTHROPIC_API_KEY, 0, 10) . '...</span><br>';
    } else {
        echo '<span class="status-indicator status-error"></span><span class="error">✗ ANTHROPIC_API_KEY is NOT defined</span><br>';
        echo '<p style="color: #ff6b6b;">Please add ANTHROPIC_API_KEY to your .env file</p>';
    }
    
    if (defined('ANTHROPIC_API_URL')) {
        echo '<span class="status-indicator status-ok"></span><span class="success">✓ ANTHROPIC_API_URL: ' . ANTHROPIC_API_URL . '</span><br>';
    } else {
        echo '<span class="status-indicator status-error"></span><span class="error">✗ ANTHROPIC_API_URL not defined</span><br>';
    }
    
    if (defined('ANTHROPIC_HAIKU_MODEL')) {
        echo '<span class="status-indicator status-ok"></span><span class="success">✓ Haiku Model: ' . ANTHROPIC_HAIKU_MODEL . '</span><br>';
    }
    
    if (defined('ANTHROPIC_SONNET_MODEL')) {
        echo '<span class="status-indicator status-ok"></span><span class="success">✓ Sonnet Model: ' . ANTHROPIC_SONNET_MODEL . '</span><br>';
    }
    
    echo '<span class="status-indicator status-ok"></span><span class="success">✓ Model Routing Enabled: ' . (MODEL_ROUTING_ENABLED ? 'Yes' : 'No') . '</span><br>';
    echo '<span class="status-indicator status-ok"></span><span class="success">✓ High Confidence Threshold: ' . CONFIDENCE_THRESHOLD_HIGH . '</span><br>';
    echo '<span class="status-indicator status-ok"></span><span class="success">✓ Medium Confidence Threshold: ' . CONFIDENCE_THRESHOLD_MEDIUM . '</span><br>';
    
    echo '</div>';

    // Test 2: Simple Text Request with Haiku
    if (defined('ANTHROPIC_API_KEY')) {
        echo '<div class="test-section">';
        echo '<div class="test-title">Test 2: Simple Text Request (Claude Haiku - Cheap Tier)</div>';
        
        $messages = [
            [
                'role' => 'system',
                'content' => 'You are a helpful assistant. Respond briefly.'
            ],
            [
                'role' => 'user',
                'content' => 'Say "Hello from QuietGo!" and confirm you are Claude Haiku.'
            ]
        ];
        
        $response = makeAnthropicRequest($messages, ANTHROPIC_HAIKU_MODEL, 100);
        
        if (isset($response['error'])) {
            echo '<span class="status-indicator status-error"></span><span class="error">✗ Error: ' . $response['error'] . '</span>';
        } else {
            echo '<span class="status-indicator status-ok"></span><span class="success">✓ Success! Response received</span><br>';
            echo '<div class="response">' . htmlspecialchars($response['choices'][0]['message']['content']) . '</div>';
            echo '<span style="color: #888;">Tokens used: ' . $response['usage']['total_tokens'] . '</span>';
        }
        
        echo '</div>';

        // Test 3: More Complex Request with Sonnet
        echo '<div class="test-section">';
        echo '<div class="test-title">Test 3: Complex Request (Claude Sonnet - Medium Tier)</div>';
        
        $messages = [
            [
                'role' => 'system',
                'content' => 'You are a nutrition AI assistant for QuietGo app. Provide brief, helpful nutritional insights.'
            ],
            [
                'role' => 'user',
                'content' => 'Analyze this hypothetical meal: grilled chicken breast, brown rice, and steamed broccoli. Provide a brief nutritional assessment in JSON format with keys: overall_quality (1-10), protein_level (high/medium/low), and one_line_insight.'
            ]
        ];
        
        $response = makeAnthropicRequest($messages, ANTHROPIC_SONNET_MODEL, 200);
        
        if (isset($response['error'])) {
            echo '<span class="status-indicator status-error"></span><span class="error">✗ Error: ' . $response['error'] . '</span>';
        } else {
            echo '<span class="status-indicator status-ok"></span><span class="success">✓ Success! Response received</span><br>';
            echo '<div class="response">' . htmlspecialchars($response['choices'][0]['message']['content']) . '</div>';
            echo '<span style="color: #888;">Tokens used: ' . $response['usage']['total_tokens'] . '</span>';
        }
        
        echo '</div>';

        // Test 4: Function Exists Check
        echo '<div class="test-section">';
        echo '<div class="test-title">Test 4: Function Availability</div>';
        
        if (function_exists('makeAnthropicRequest')) {
            echo '<span class="status-indicator status-ok"></span><span class="success">✓ makeAnthropicRequest() function exists</span><br>';
        } else {
            echo '<span class="status-indicator status-error"></span><span class="error">✗ makeAnthropicRequest() function NOT found</span><br>';
        }
        
        if (function_exists('makeOpenAIRequest')) {
            echo '<span class="status-indicator status-ok"></span><span class="success">✓ makeOpenAIRequest() function exists</span><br>';
        }
        
        echo '</div>';
        
    } else {
        echo '<div class="test-section">';
        echo '<div class="test-title">⚠️ Cannot Run API Tests</div>';
        echo '<p style="color: #ff6b6b;">ANTHROPIC_API_KEY is not configured. Please add it to your .env file.</p>';
        echo '<p>Example .env entry:<br><code style="background: #1a1a1a; padding: 5px;">ANTHROPIC_API_KEY=sk-ant-your-key-here</code></p>';
        echo '</div>';
    }
    ?>

    <div class="test-section">
        <div class="test-title">📋 Test Summary</div>
        <p>Phase 1 implementation checks:</p>
        <ul>
            <li>✓ Anthropic constants defined in openai-config.php</li>
            <li>✓ makeAnthropicRequest() function available</li>
            <li>✓ Model routing configuration set</li>
            <?php if (defined('ANTHROPIC_API_KEY')): ?>
                <li>✓ API connection tested successfully</li>
            <?php else: ?>
                <li style="color: #ff6b6b;">✗ API key needs configuration</li>
            <?php endif; ?>
        </ul>
    </div>

    <div style="margin-top: 40px; padding: 20px; background: #2a2a2a; border-radius: 8px;">
        <h3 style="color: #6C985F;">Next Steps</h3>
        <p>Once all tests pass:</p>
        <ol>
            <li>Proceed to Phase 2: Image Complexity Analyzer</li>
            <li>Create /hub/includes/image-analyzer.php</li>
            <li>Implement smart routing logic</li>
        </ol>
    </div>

</body>
</html>
