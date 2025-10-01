<?php
/**
 * QuietGo CalcuPlate Verification Modal v3.2
 * Smart UI for reviewing and correcting meal analysis
 * 
 * Features:
 * - Confidence-based prioritization (low confidence items first)
 * - Weight adjustment with ranges
 * - Cooking method selection
 * - Quick adder chips (oil, dressing, cheese, sauce)
 * - Visual "why" explanations
 * - Real-time recalculation
 */

// Expect $analysis array in cp.v1 format from analysis-functions.php
if (!isset($analysis) || !is_array($analysis)) {
    $analysis = [
        'schema_version' => 'cp.v1',
        'items' => [],
        'totals' => ['calories' => 0, 'protein_g' => 0, 'carbs_g' => 0, 'fat_g' => 0],
        'meal_confidence' => 0.5,
        'plate_model' => ['type' => 'none']
    ];
}

// Sort items by confidence (low to high) for priority review
$items = $analysis['items'] ?? [];
usort($items, function($a, $b) {
    $confA = $a['confidence'] ?? 0.5;
    $confB = $b['confidence'] ?? 0.5;
    return $confA <=> $confB; // Low confidence first
});
?>

<div class="calcuplate-verify-modal" id="calcuplate-verify-modal" style="display: none;">
    <div class="modal-overlay" onclick="closeVerifyModal()"></div>
    <div class="modal-content">
        <style>
            .calcuplate-verify-modal {
                position: fixed;
                top: 0;
                left: 0;
                right: 0;
                bottom: 0;
                z-index: 9999;
                display: flex;
                align-items: center;
                justify-content: center;
            }
            
            .modal-overlay {
                position: absolute;
                top: 0;
                left: 0;
                right: 0;
                bottom: 0;
                background: rgba(0, 0, 0, 0.85);
            }
            
            .modal-content {
                position: relative;
                background: #1a1a1a;
                border: 1px solid #404040;
                border-radius: 16px;
                max-width: 900px;
                width: 90%;
                max-height: 85vh;
                overflow-y: auto;
                padding: 2rem;
                color: #ffffff;
                font-family: system-ui, -apple-system, 'Segoe UI', Roboto, Ubuntu, sans-serif;
            }
            
            .modal-header {
                display: flex;
                justify-content: space-between;
                align-items: center;
                margin-bottom: 1.5rem;
                padding-bottom: 1rem;
                border-bottom: 2px solid #404040;
            }
            
            .modal-header h2 {
                color: #6c985f;
                margin: 0;
                font-size: 1.5rem;
            }
            
            .close-btn {
                background: transparent;
                border: none;
                color: #808080;
                font-size: 1.5rem;
                cursor: pointer;
                padding: 0.5rem;
                line-height: 1;
            }
            
            .close-btn:hover {
                color: #ffffff;
            }
            
            .confidence-badge {
                display: inline-block;
                padding: 0.25rem 0.75rem;
                border-radius: 12px;
                font-size: 0.75rem;
                font-weight: 600;
                margin-left: 0.5rem;
            }
            
            .confidence-high {
                background: rgba(108, 152, 95, 0.2);
                color: #6c985f;
                border: 1px solid #6c985f;
            }
            
            .confidence-medium {
                background: rgba(212, 167, 153, 0.2);
                color: #d4a799;
                border: 1px solid #d4a799;
            }
            
            .confidence-low {
                background: rgba(231, 76, 60, 0.2);
                color: #e74c3c;
                border: 1px solid #e74c3c;
            }
            
            .item-card {
                background: #2a2a2a;
                border: 1px solid #404040;
                border-radius: 12px;
                padding: 1.5rem;
                margin-bottom: 1rem;
            }
            
            .item-card.priority-review {
                border-color: #e74c3c;
                border-width: 2px;
            }
            
            .item-header {
                display: flex;
                justify-content: space-between;
                align-items: center;
                margin-bottom: 1rem;
            }
            
            .item-name {
                font-size: 1.1rem;
                font-weight: 600;
                color: #ffffff;
            }
            
            .item-category {
                font-size: 0.85rem;
                color: #808080;
                margin-left: 0.5rem;
            }
            
            .item-controls {
                display: grid;
                grid-template-columns: 1fr 1fr 2fr;
                gap: 1rem;
                margin-bottom: 1rem;
            }
            
            .control-group {
                display: flex;
                flex-direction: column;
            }
            
            .control-label {
                font-size: 0.85rem;
                color: #b0b0b0;
                margin-bottom: 0.5rem;
                font-weight: 500;
            }
            
            .weight-input-group {
                display: flex;
                align-items: center;
                gap: 0.5rem;
            }
            
            .weight-input {
                background: #1a1a1a;
                border: 1px solid #404040;
                color: #ffffff;
                padding: 0.5rem;
                border-radius: 6px;
                width: 80px;
                text-align: center;
                font-size: 1rem;
            }
            
            .weight-btn {
                background: #333;
                border: 1px solid #404040;
                color: #ffffff;
                padding: 0.5rem 0.75rem;
                border-radius: 6px;
                cursor: pointer;
                font-size: 0.9rem;
                transition: all 0.2s;
            }
            
            .weight-btn:hover {
                background: #404040;
            }
            
            .weight-range {
                font-size: 0.75rem;
                color: #808080;
                margin-top: 0.25rem;
            }
            
            .method-select {
                background: #1a1a1a;
                border: 1px solid #404040;
                color: #ffffff;
                padding: 0.5rem;
                border-radius: 6px;
                width: 100%;
                font-size: 0.95rem;
            }
            
            .adders-group {
                display: flex;
                flex-wrap: wrap;
                gap: 0.5rem;
            }
            
            .adder-chip {
                background: #333;
                border: 1px solid #404040;
                color: #b0b0b0;
                padding: 0.4rem 0.75rem;
                border-radius: 20px;
                cursor: pointer;
                font-size: 0.85rem;
                transition: all 0.2s;
                user-select: none;
            }
            
            .adder-chip:hover {
                background: #404040;
                border-color: #6c985f;
            }
            
            .adder-chip.active {
                background: #6c985f;
                border-color: #6c985f;
                color: #ffffff;
            }
            
            .visual-basis {
                background: rgba(60, 157, 155, 0.1);
                border: 1px solid #3c9d9b;
                border-radius: 8px;
                padding: 0.75rem;
                margin-top: 1rem;
            }
            
            .visual-basis-label {
                font-size: 0.75rem;
                color: #3c9d9b;
                font-weight: 600;
                margin-bottom: 0.25rem;
                text-transform: uppercase;
                letter-spacing: 0.5px;
            }
            
            .visual-basis-text {
                font-size: 0.85rem;
                color: #b0b0b0;
                line-height: 1.4;
            }
            
            .totals-section {
                background: #2a2a2a;
                border: 2px solid #6c985f;
                border-radius: 12px;
                padding: 1.5rem;
                margin-top: 2rem;
            }
            
            .totals-grid {
                display: grid;
                grid-template-columns: repeat(4, 1fr);
                gap: 1rem;
                margin-bottom: 1rem;
            }
            
            .total-item {
                text-align: center;
            }
            
            .total-label {
                font-size: 0.75rem;
                color: #808080;
                text-transform: uppercase;
                letter-spacing: 0.5px;
            }
            
            .total-value {
                font-size: 1.5rem;
                font-weight: 600;
                color: #6c985f;
                margin-top: 0.25rem;
            }
            
            .action-buttons {
                display: flex;
                gap: 1rem;
                margin-top: 1rem;
            }
            
            .btn {
                flex: 1;
                padding: 0.875rem;
                border-radius: 8px;
                font-weight: 600;
                font-size: 1rem;
                cursor: pointer;
                transition: all 0.2s;
                border: none;
            }
            
            .btn-secondary {
                background: transparent;
                border: 1px solid #404040;
                color: #b0b0b0;
            }
            
            .btn-secondary:hover {
                background: #333;
                border-color: #6c985f;
                color: #ffffff;
            }
            
            .btn-primary {
                background: #6c985f;
                color: #ffffff;
            }
            
            .btn-primary:hover {
                background: #7aa570;
            }
            
            .priority-notice {
                background: rgba(231, 76, 60, 0.1);
                border: 1px solid #e74c3c;
                border-radius: 8px;
                padding: 1rem;
                margin-bottom: 1.5rem;
                color: #e74c3c;
                font-size: 0.9rem;
            }
            
            @media (max-width: 768px) {
                .item-controls {
                    grid-template-columns: 1fr;
                }
                
                .totals-grid {
                    grid-template-columns: repeat(2, 1fr);
                }
                
                .action-buttons {
                    flex-direction: column;
                }
            }
        </style>
        
        <!-- Modal Header -->
        <div class="modal-header">
            <div>
                <h2>Verify Your Meal</h2>
                <span class="confidence-badge <?php 
                    $mealConf = ($analysis['meal_confidence'] ?? 0.5) * 100;
                    echo $mealConf >= 80 ? 'confidence-high' : ($mealConf >= 60 ? 'confidence-medium' : 'confidence-low');
                ?>">
                    <?php echo round($mealConf); ?>% Confidence
                </span>
            </div>
            <button class="close-btn" onclick="closeVerifyModal()">&times;</button>
        </div>
        
        <!-- Priority Review Notice -->
        <?php 
        $hasLowConfidence = false;
        foreach ($items as $item) {
            if (($item['confidence'] ?? 0.5) < 0.7) {
                $hasLowConfidence = true;
                break;
            }
        }
        if ($hasLowConfidence): 
        ?>
        <div class="priority-notice">
            <strong>Review Needed:</strong> Some items have low confidence. Please verify the measurements below.
        </div>
        <?php endif; ?>
        
        <!-- Items List (sorted by confidence - low first) -->
        <div class="items-list">
            <?php foreach ($items as $index => $item): 
                $confidence = ($item['confidence'] ?? 0.5) * 100;
                $isPriority = $confidence < 70;
                $confClass = $confidence >= 80 ? 'confidence-high' : ($confidence >= 60 ? 'confidence-medium' : 'confidence-low');
            ?>
            <div class="item-card <?php echo $isPriority ? 'priority-review' : ''; ?>" data-index="<?php echo $index; ?>">
                <!-- Item Header -->
                <div class="item-header">
                    <div>
                        <span class="item-name"><?php echo htmlspecialchars($item['name'] ?? 'Item'); ?></span>
                        <span class="item-category"><?php echo htmlspecialchars($item['category'] ?? 'food'); ?></span>
                    </div>
                    <span class="confidence-badge <?php echo $confClass; ?>">
                        <?php echo round($confidence); ?>%
                    </span>
                </div>
                
                <!-- Item Controls -->
                <div class="item-controls">
                    <!-- Weight Control -->
                    <div class="control-group">
                        <label class="control-label">Weight (g)</label>
                        <div class="weight-input-group">
                            <button class="weight-btn" onclick="adjustWeight(<?php echo $index; ?>, -10)">-</button>
                            <input type="number" 
                                   class="weight-input" 
                                   name="weight_<?php echo $index; ?>" 
                                   value="<?php echo round($item['est_weight_g'] ?? 100); ?>"
                                   min="1"
                                   step="5">
                            <button class="weight-btn" onclick="adjustWeight(<?php echo $index; ?>, 10)">+</button>
                        </div>
                        <div class="weight-range">
                            Range: <?php echo round($item['ci_low_g'] ?? 90); ?>-<?php echo round($item['ci_high_g'] ?? 110); ?>g
                        </div>
                    </div>
                    
                    <!-- Cooking Method -->
                    <div class="control-group">
                        <label class="control-label">Cooking Method</label>
                        <select class="method-select" name="method_<?php echo $index; ?>">
                            <?php 
                            $methods = ['raw', 'grilled', 'pan-seared', 'deep-fried', 'roasted', 'boiled', 'steamed', 'baked', 'sauteed'];
                            $currentMethod = $item['cooking_method'] ?? 'raw';
                            foreach ($methods as $method): 
                                $selected = ($method === $currentMethod) ? 'selected' : '';
                            ?>
                                <option value="<?php echo $method; ?>" <?php echo $selected; ?>>
                                    <?php echo ucwords(str_replace('-', ' ', $method)); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <!-- Adders -->
                    <div class="control-group">
                        <label class="control-label">Add Oil/Sauce/Cheese</label>
                        <div class="adders-group">
                            <div class="adder-chip" data-adder="oil:5ml" onclick="toggleAdder(this, <?php echo $index; ?>)">
                                + 1 tsp oil
                            </div>
                            <div class="adder-chip" data-adder="oil:15ml" onclick="toggleAdder(this, <?php echo $index; ?>)">
                                + 1 tbsp oil
                            </div>
                            <div class="adder-chip" data-adder="dressing:15ml" onclick="toggleAdder(this, <?php echo $index; ?>)">
                                + 1 tbsp dressing
                            </div>
                            <div class="adder-chip" data-adder="sauce:30ml" onclick="toggleAdder(this, <?php echo $index; ?>)">
                                + 2 tbsp sauce
                            </div>
                            <div class="adder-chip" data-adder="cheese:10g" onclick="toggleAdder(this, <?php echo $index; ?>)">
                                + 10g cheese
                            </div>
                            <div class="adder-chip" data-adder="cheese:28g" onclick="toggleAdder(this, <?php echo $index; ?>)">
                                + 1oz cheese
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Visual Basis / Why -->
                <?php if (!empty($item['visual_basis']) || !empty($item['pieces_note'])): ?>
                <div class="visual-basis">
                    <div class="visual-basis-label">Why this estimate?</div>
                    <div class="visual-basis-text">
                        <?php 
                        if (!empty($item['pieces_note'])) {
                            echo htmlspecialchars($item['pieces_note']) . '<br>';
                        }
                        if (!empty($item['visual_basis'])) {
                            echo htmlspecialchars(implode(' • ', $item['visual_basis']));
                        }
                        ?>
                    </div>
                </div>
                <?php endif; ?>
            </div>
            <?php endforeach; ?>
        </div>
        
        <!-- Totals Section -->
        <div class="totals-section">
            <div class="totals-grid">
                <div class="total-item">
                    <div class="total-label">Calories</div>
                    <div class="total-value" id="total-calories">
                        <?php echo round($analysis['totals']['calories'] ?? 0); ?>
                    </div>
                </div>
                <div class="total-item">
                    <div class="total-label">Protein</div>
                    <div class="total-value" id="total-protein">
                        <?php echo round($analysis['totals']['protein_g'] ?? 0); ?>g
                    </div>
                </div>
                <div class="total-item">
                    <div class="total-label">Carbs</div>
                    <div class="total-value" id="total-carbs">
                        <?php echo round($analysis['totals']['carbs_g'] ?? 0); ?>g
                    </div>
                </div>
                <div class="total-item">
                    <div class="total-label">Fat</div>
                    <div class="total-value" id="total-fat">
                        <?php echo round($analysis['totals']['fat_g'] ?? 0); ?>g
                    </div>
                </div>
            </div>
            
            <!-- Action Buttons -->
            <div class="action-buttons">
                <button class="btn btn-secondary" onclick="closeVerifyModal()">
                    Cancel
                </button>
                <button class="btn btn-primary" onclick="saveAndRecalculate()">
                    Save & Recalculate
                </button>
            </div>
        </div>
    </div>
</div>

<script>
// Weight adjustment
function adjustWeight(index, amount) {
    const input = document.querySelector(`.item-card[data-index="${index}"] input[name="weight_${index}"]`);
    const currentValue = parseInt(input.value) || 100;
    input.value = Math.max(1, currentValue + amount);
}

// Toggle adder chips
function toggleAdder(chip, index) {
    chip.classList.toggle('active');
}

// Close modal
function closeVerifyModal() {
    document.getElementById('calcuplate-verify-modal').style.display = 'none';
}

// Show modal
function showVerifyModal() {
    document.getElementById('calcuplate-verify-modal').style.display = 'flex';
}

// Collect current state and recalculate
function saveAndRecalculate() {
    const items = [];
    document.querySelectorAll('.item-card').forEach((card, idx) => {
        const index = card.dataset.index;
        const name = card.querySelector('.item-name').textContent;
        const weight = parseInt(card.querySelector(`input[name="weight_${index}"]`).value);
        const method = card.querySelector(`select[name="method_${index}"]`).value;
        
        // Collect active adders
        const adders = [];
        card.querySelectorAll('.adder-chip.active').forEach(chip => {
            const [type, spec] = chip.dataset.adder.split(':');
            if (spec.endsWith('ml')) {
                adders.push({
                    type: type,
                    amount_ml: parseFloat(spec),
                    amount_g: null
                });
            } else if (spec.endsWith('g')) {
                adders.push({
                    type: type,
                    amount_ml: null,
                    amount_g: parseFloat(spec)
                });
            }
        });
        
        items.push({
            name: name,
            est_weight_g: weight,
            cooking_method: method,
            adders: adders
        });
    });
    
    // Send to recalculate endpoint
    fetch('/hub/recalculate-nutrition.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({ items: items })
    })
    .then(response => response.json())
    .then(data => {
        if (data.ok) {
            // Update totals display
            document.getElementById('total-calories').textContent = data.totals.calories;
            document.getElementById('total-protein').textContent = data.totals.protein_g + 'g';
            document.getElementById('total-carbs').textContent = data.totals.carbs_g + 'g';
            document.getElementById('total-fat').textContent = data.totals.fat_g + 'g';
            
            // Show success message
            alert('Meal updated! New total: ' + data.totals.calories + ' calories');
            
            // Close modal and refresh page
            closeVerifyModal();
            setTimeout(() => location.reload(), 1000);
        } else {
            alert('Recalculation failed: ' + (data.error || 'Unknown error'));
        }
    })
    .catch(error => {
        console.error('Recalculate error:', error);
        alert('Failed to recalculate: ' + error.message);
    });
}

// Auto-show modal if it exists on page load
document.addEventListener('DOMContentLoaded', function() {
    const modal = document.getElementById('calcuplate-verify-modal');
    if (modal && <?php echo count($items) > 0 ? 'true' : 'false'; ?>) {
        // Only auto-show if there are items to verify
        showVerifyModal();
    }
});
</script>
