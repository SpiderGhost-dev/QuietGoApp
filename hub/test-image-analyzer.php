<?php
/**
 * Test Image Complexity Analyzer
 * Tests Phase 2 implementation with sample meal photos
 */

require_once __DIR__ . '/includes/image-analyzer.php';

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Image Analyzer Test - QuietGo</title>
    <style>
        body {
            font-family: 'Inter', -apple-system, sans-serif;
            background-color: #1a1a1a;
            color: #F5F5DC;
            padding: 40px;
            max-width: 1200px;
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
        .image-result {
            display: grid;
            grid-template-columns: 300px 1fr;
            gap: 20px;
            margin: 20px 0;
            background: #2a2a2a;
            padding: 20px;
            border-radius: 8px;
        }
        .image-preview {
            max-width: 100%;
            border-radius: 8px;
        }
        .tier-cheap { color: #6C985F; font-weight: 700; }
        .tier-medium { color: #4682B4; font-weight: 700; }
        .tier-expensive { color: #D4A799; font-weight: 700; }
        .detail-row {
            padding: 8px 0;
            border-bottom: 1px solid #3a3a3a;
        }
        .detail-label {
            color: #888;
            display: inline-block;
            width: 180px;
        }
        .score-bar {
            background: #1a1a1a;
            height: 20px;
            border-radius: 10px;
            overflow: hidden;
            margin: 5px 0;
        }
        .score-fill {
            height: 100%;
            background: linear-gradient(90deg, #ff6b6b, #4682B4, #6C985F);
            transition: width 0.3s;
        }
    </style>
</head>
<body>
    <h1>🖼️ Image Complexity Analyzer Test</h1>
    <p>Testing Phase 2 - Image analysis and tier routing</p>

    <?php
    $testImages = [
        'meal1.jpg' => 'Colorful bowl with chicken, rice, salad',
        'meal2.jpg' => 'Salmon with asparagus',
        'meal3.jpg' => 'Bowl with salmon, quinoa, vegetables',
        'meal4.jpg' => 'Thanksgiving plate - complex',
        'meal5.jpg' => 'Sushi rolls',
        'meal6.jpg' => 'Layered drinks',
        'meal7.jpg' => 'Iced coffee'
    ];

    $testDir = __DIR__ . '/test_images/';
    $results = [];
    $tierCount = ['cheap' => 0, 'medium' => 0, 'expensive' => 0];

    foreach ($testImages as $filename => $description) {
        $filepath = $testDir . $filename;
        
        if (!file_exists($filepath)) {
            echo "<div class='test-section'>";
            echo "<strong style='color: #ff6b6b;'>⚠️ Missing: $filename</strong>";
            echo "</div>";
            continue;
        }

        $analysis = ImageComplexityAnalyzer::analyzeImage($filepath, 'meal');
        $results[$filename] = $analysis;
        $tierCount[$analysis['tier']]++;

        echo "<div class='image-result'>";
        
        // Image preview
        echo "<div>";
        echo "<img src='test_images/$filename' class='image-preview' alt='$description'>";
        echo "<p style='color: #888; font-size: 0.9em; margin-top: 10px;'>$description</p>";
        echo "</div>";
        
        // Analysis results
        echo "<div>";
        echo "<h3 style='color: #D4A799; margin-top: 0;'>$filename</h3>";
        
        $tierClass = 'tier-' . $analysis['tier'];
        echo "<div class='detail-row'>";
        echo "<span class='detail-label'>Recommended Tier:</span>";
        echo "<span class='$tierClass'>" . strtoupper($analysis['tier']) . "</span>";
        echo "</div>";
        
        echo "<div class='detail-row'>";
        echo "<span class='detail-label'>Confidence Score:</span>";
        echo number_format($analysis['confidence'] * 100, 1) . "%";
        echo "<div class='score-bar'><div class='score-fill' style='width: " . ($analysis['confidence'] * 100) . "%;'></div></div>";
        echo "</div>";
        
        echo "<div class='detail-row'>";
        echo "<span class='detail-label'>Reasons:</span>";
        echo implode(', ', $analysis['reasons']);
        echo "</div>";
        
        if (isset($analysis['checks'])) {
            echo "<div style='margin-top: 15px; padding-top: 15px; border-top: 1px solid #3a3a3a;'>";
            echo "<strong style='color: #888;'>Detailed Checks:</strong><br>";
            
            foreach ($analysis['checks'] as $checkName => $checkData) {
                echo "<div style='margin: 8px 0; font-size: 0.9em;'>";
                echo "<span style='color: #6C985F;'>" . ucwords(str_replace('_', ' ', $checkName)) . ":</span> ";
                echo "Score " . number_format($checkData['score'], 2) . " - " . $checkData['reason'];
                echo "</div>";
            }
            echo "</div>";
        }
        
        echo "</div>";
        echo "</div>";
    }

    // Summary
    echo "<div class='test-section'>";
    echo "<h2 style='color: #6C985F;'>📊 Test Summary</h2>";
    
    $total = array_sum($tierCount);
    if ($total > 0) {
        echo "<div style='margin: 20px 0;'>";
        echo "<div class='detail-row'>";
        echo "<span class='detail-label'>Total Images Analyzed:</span><strong>$total</strong>";
        echo "</div>";
        
        foreach ($tierCount as $tier => $count) {
            $percentage = round(($count / $total) * 100, 1);
            $tierClass = 'tier-' . $tier;
            echo "<div class='detail-row'>";
            echo "<span class='detail-label'>" . ucfirst($tier) . " Tier:</span>";
            echo "<span class='$tierClass'>$count images ($percentage%)</span>";
            echo "</div>";
        }
        echo "</div>";
        
        echo "<h3 style='color: #D4A799;'>Expected Cost Distribution</h3>";
        $avgCost = ($tierCount['cheap'] * 0.002 + $tierCount['medium'] * 0.005 + $tierCount['expensive'] * 0.015) / $total;
        echo "<p>Average cost per image: <strong style='color: #6C985F;'>$" . number_format($avgCost, 4) . "</strong></p>";
        echo "<p style='color: #888; font-size: 0.9em;'>Compare to all-expensive: $0.015 per image</p>";
        echo "<p>Projected savings: <strong style='color: #6C985F;'>" . round((1 - ($avgCost / 0.015)) * 100, 1) . "%</strong></p>";
    } else {
        echo "<p style='color: #ff6b6b;'>No images analyzed. Please add images to /hub/test_images/</p>";
    }
    
    echo "</div>";

    echo "<div class='test-section'>";
    echo "<h2 style='color: #6C985F;'>✅ Next Steps</h2>";
    echo "<p>Once satisfied with image analysis results:</p>";
    echo "<ol>";
    echo "<li>Proceed to Phase 3: Create Smart AI Router</li>";
    echo "<li>Integrate router into upload.php</li>";
    echo "<li>Test full workflow with real meal analysis</li>";
    echo "</ol>";
    echo "</div>";
    ?>

</body>
</html>
