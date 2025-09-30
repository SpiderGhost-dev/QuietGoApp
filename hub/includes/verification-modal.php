<!-- CalcuPlate Verification Modal -->
<div class="verification-modal" id="verification-modal" style="display: none;">
    <div class="verification-modal-content">
        <div class="verification-header">
            <h2>🔍 Verify CalcuPlate Analysis</h2>
            <p style="color: var(--text-secondary); margin: 0.5rem 0 0 0;">Quick check before finalizing - correct any mistakes</p>
        </div>

        <div class="verification-body" id="verification-body">
            <!-- Items will be populated by JavaScript -->
        </div>

        <div class="verification-footer">
            <button onclick="cancelVerification()" class="btn-secondary">Cancel</button>
            <button onclick="acceptVerification()" class="btn-primary">✓ Looks Good - Calculate Nutrition</button>
        </div>
    </div>
</div>

<style>
.verification-modal {
    position: fixed;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: rgba(0, 0, 0, 0.95);
    display: flex;
    align-items: center;
    justify-content: center;
    z-index: 2001;
    animation: fadeIn 0.2s ease;
}

.verification-modal-content {
    background: var(--card-bg);
    border: 2px solid var(--primary-blue);
    border-radius: 16px;
    max-width: 600px;
    width: 90%;
    max-height: 85vh;
    overflow: hidden;
    display: flex;
    flex-direction: column;
    animation: slideUp 0.3s ease;
}

.verification-header {
    background: linear-gradient(135deg, var(--primary-blue), var(--accent-teal));
    color: white;
    padding: 1.5rem;
    text-align: center;
}

.verification-header h2 {
    margin: 0;
    font-size: 1.5rem;
}

.verification-body {
    padding: 1.5rem;
    overflow-y: auto;
    flex: 1;
}

.verification-item {
    background: #333;
    border: 1px solid var(--card-border);
    border-radius: 8px;
    padding: 1rem;
    margin: 0.75rem 0;
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 1rem;
}

.verification-item-info {
    flex: 1;
}

.verification-item-name {
    color: var(--text-primary);
    font-weight: 600;
    font-size: 1rem;
    margin-bottom: 0.25rem;
}

.verification-item-method {
    color: var(--text-muted);
    font-size: 0.85rem;
}

.verification-item-count {
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.verification-item-count input {
    background: #222;
    border: 2px solid var(--card-border);
    color: var(--text-primary);
    padding: 0.5rem;
    border-radius: 6px;
    width: 80px;
    text-align: center;
    font-size: 1rem;
    font-weight: 600;
}

.verification-item-count input:focus {
    border-color: var(--primary-blue);
    outline: none;
}

.verification-item-count button {
    background: var(--slate-blue);
    border: none;
    color: white;
    width: 32px;
    height: 32px;
    border-radius: 6px;
    cursor: pointer;
    font-size: 1.2rem;
    display: flex;
    align-items: center;
    justify-content: center;
}

.verification-item-count button:hover {
    background: var(--primary-blue);
}

.verification-footer {
    padding: 1.5rem;
    border-top: 1px solid var(--card-border);
    display: flex;
    gap: 1rem;
}

.verification-footer .btn-secondary {
    flex: 1;
    background: transparent;
    border: 1px solid var(--card-border);
    color: var(--text-secondary);
    padding: 0.875rem;
    border-radius: 8px;
    font-weight: 600;
    cursor: pointer;
}

.verification-footer .btn-primary {
    flex: 2;
    background: var(--success-color);
    color: white;
    border: none;
    padding: 0.875rem;
    border-radius: 8px;
    font-weight: 600;
    cursor: pointer;
}

.verification-footer .btn-primary:hover {
    background: #7aa570;
}

.verification-notice {
    background: rgba(70, 130, 180, 0.1);
    border: 1px solid var(--primary-blue);
    border-radius: 8px;
    padding: 1rem;
    margin-bottom: 1rem;
    color: var(--text-secondary);
    font-size: 0.9rem;
    text-align: center;
}
</style>

<script>
let currentVerificationData = null;
let originalAnalysis = null;

function showVerificationModal(result) {
    console.log('showVerificationModal called with:', result);
    
    // Store original data for recalculation
    currentVerificationData = result;
    originalAnalysis = JSON.parse(JSON.stringify(result.ai_analysis)); // Deep copy
    
    const modal = document.getElementById('verification-modal');
    const body = document.getElementById('verification-body');
    
    if (!result.ai_analysis || !result.ai_analysis.pass_1_detection) {
        console.error('No pass_1_detection data available');
        // Skip verification if no detection data
        showSuccessModal(result);
        return;
    }
    
    const detection = result.ai_analysis.pass_1_detection;
    
    // Build verification UI
    let html = '<div class="verification-notice">AI detected these items. Tap + or - to correct any mistakes.</div>';
    
    // Convert detection object to array of items
    const items = [];
    for (const [key, value] of Object.entries(detection)) {
        if (value && typeof value === 'object') {
            items.push({
                key: key,
                name: key.replace(/_/g, ' ').replace(/\b\w/g, l => l.toUpperCase()),
                count: value.count || 0,
                amount: value.amount || null,
                method: value.method || 'detected'
            });
        }
    }
    
    // Render each item
    items.forEach((item, index) => {
        const displayValue = item.count || item.amount || 'N/A';
        const isCountable = typeof item.count === 'number';
        
        html += `
            <div class="verification-item">
                <div class="verification-item-info">
                    <div class="verification-item-name">${item.name}</div>
                    <div class="verification-item-method">${item.method}</div>
                </div>
                ${isCountable ? `
                    <div class="verification-item-count">
                        <button onclick="adjustCount('${item.key}', -1)">-</button>
                        <input 
                            type="number" 
                            id="verify-${item.key}" 
                            value="${item.count}" 
                            min="0"
                            onchange="updateCount('${item.key}', this.value)"
                        >
                        <button onclick="adjustCount('${item.key}', 1)">+</button>
                    </div>
                ` : `
                    <div style="color: var(--text-muted); font-size: 0.9rem;">${displayValue}</div>
                `}
            </div>
        `;
    });
    
    body.innerHTML = html;
    modal.style.display = 'flex';
}

function adjustCount(itemKey, delta) {
    const input = document.getElementById(`verify-${itemKey}`);
    if (input) {
        const currentValue = parseInt(input.value) || 0;
        const newValue = Math.max(0, currentValue + delta);
        input.value = newValue;
        updateCount(itemKey, newValue);
    }
}

function updateCount(itemKey, newValue) {
    // Update the stored detection data
    if (currentVerificationData && currentVerificationData.ai_analysis.pass_1_detection[itemKey]) {
        currentVerificationData.ai_analysis.pass_1_detection[itemKey].count = parseInt(newValue);
        console.log(`Updated ${itemKey} to ${newValue}`);
    }
}

function cancelVerification() {
    document.getElementById('verification-modal').style.display = 'none';
    closeUploadModal();
}

function acceptVerification() {
    // Check if any counts were changed
    let changesDetected = false;
    const detection = currentVerificationData.ai_analysis.pass_1_detection;
    const originalDetection = originalAnalysis.pass_1_detection;
    
    for (const key in detection) {
        if (detection[key].count !== originalDetection[key].count) {
            changesDetected = true;
            console.log(`User corrected ${key}: ${originalDetection[key].count} → ${detection[key].count}`);
        }
    }
    
    if (changesDetected) {
        // User made corrections - recalculate nutrition with corrected counts
        console.log('Recalculating nutrition with user-corrected quantities...');
        recalculateNutrition(currentVerificationData);
    } else {
        // No changes - use original analysis
        console.log('No corrections made, using original analysis');
        document.getElementById('verification-modal').style.display = 'none';
        showSuccessModal(currentVerificationData);
    }
}

function recalculateNutrition(verificationData) {
    // Show loading state
    const modal = document.getElementById('verification-modal');
    const body = document.getElementById('verification-body');
    body.innerHTML = '<div style="text-align: center; padding: 2rem;"><div style="font-size: 2rem; margin-bottom: 1rem;">🔄</div><p style="color: var(--text-secondary);">Recalculating nutrition with your corrections...</p></div>';
    
    // Build corrected items list
    const detection = verificationData.ai_analysis.pass_1_detection;
    const correctedItems = [];
    
    for (const [key, value] of Object.entries(detection)) {
        if (value && typeof value === 'object' && value.count) {
            correctedItems.push({
                item: key.replace(/_/g, ' '),
                quantity: value.count,
                type: value.type || 'food'
            });
        }
    }
    
    // Make new API call with corrected quantities
    fetch('/hub/recalculate-nutrition.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
        },
        body: JSON.stringify({
            corrected_items: correctedItems,
            original_photo_ids: verificationData.photo_ids,
            user_journey: <?php echo json_encode($userJourney); ?>
        })
    })
    .then(response => response.json())
    .then(recalculatedData => {
        console.log('Recalculated nutrition:', recalculatedData);
        
        // Update the analysis with recalculated data
        verificationData.ai_analysis.calcuplate = recalculatedData.calcuplate;
        verificationData.ai_analysis.user_corrected = true;
        verificationData.ai_analysis.corrections_made = correctedItems;
        
        // Close verification modal and show success
        modal.style.display = 'none';
        showSuccessModal(verificationData);
    })
    .catch(error => {
        console.error('Recalculation error:', error);
        alert('Failed to recalculate nutrition. Using original analysis.');
        modal.style.display = 'none';
        showSuccessModal(verificationData);
    });
}
</script>
