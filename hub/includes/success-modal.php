<!-- Success Results Modal -->
<div class="success-modal" id="success-modal" style="display: none;">
    <div class="success-modal-content">
        <div class="success-header">
            <div class="success-icon" id="success-icon">✅</div>
            <h2 id="success-title">Analysis Complete!</h2>
            <button onclick="closeSuccessModal()" class="close-btn">×</button>
        </div>

        <div class="success-body" id="success-body">
            <!-- Content populated by JavaScript -->
        </div>

        <div class="success-footer">
            <button onclick="closeSuccessModal()" class="btn-secondary">Close</button>
            <button onclick="window.location='/hub/review-analysis.php'" class="btn-primary">View All Analyses</button>
        </div>
    </div>
</div>

<style>
/* Success Modal Styles */
.success-modal {
    position: fixed;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: rgba(0, 0, 0, 0.9);
    display: flex;
    align-items: center;
    justify-content: center;
    z-index: 2000;
    animation: fadeIn 0.3s ease;
}

@keyframes fadeIn {
    from { opacity: 0; }
    to { opacity: 1; }
}

.success-modal-content {
    background: var(--card-bg);
    border: 2px solid var(--success-color);
    border-radius: 16px;
    max-width: 700px;
    width: 90%;
    max-height: 85vh;
    overflow: hidden;
    display: flex;
    flex-direction: column;
    animation: slideUp 0.4s ease;
    box-shadow: 0 10px 40px rgba(0, 0, 0, 0.5);
}

@keyframes slideUp {
    from {
        opacity: 0;
        transform: translateY(30px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.success-header {
    background: linear-gradient(135deg, var(--success-color), #7aa570);
    color: white;
    padding: 2rem;
    text-align: center;
    position: relative;
}

.success-icon {
    font-size: 4rem;
    margin-bottom: 0.5rem;
    animation: bounceIn 0.6s ease;
}

@keyframes bounceIn {
    0%, 20%, 50%, 80%, 100% { transform: translateY(0); }
    40% { transform: translateY(-20px); }
    60% { transform: translateY(-10px); }
}

.success-header h2 {
    margin: 0;
    font-size: 1.8rem;
    font-weight: 700;
}

.close-btn {
    position: absolute;
    top: 1rem;
    right: 1rem;
    background: rgba(255, 255, 255, 0.2);
    border: none;
    color: white;
    width: 36px;
    height: 36px;
    border-radius: 50%;
    font-size: 1.5rem;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: all 0.2s;
}

.close-btn:hover {
    background: rgba(255, 255, 255, 0.3);
    transform: rotate(90deg);
}

.success-body {
    padding: 2rem;
    overflow-y: auto;
    flex: 1;
}

.result-section {
    margin: 1.5rem 0;
    padding: 1.5rem;
    background: #333;
    border-radius: 12px;
    border-left: 4px solid var(--success-color);
}

.result-section h3 {
    color: var(--success-color);
    margin: 0 0 1rem 0;
    font-size: 1.2rem;
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.result-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
    gap: 1rem;
    margin: 1rem 0;
}

.result-item {
    background: #2a2a2a;
    padding: 1rem;
    border-radius: 8px;
    text-align: center;
}

.result-label {
    color: var(--text-muted);
    font-size: 0.85rem;
    margin-bottom: 0.5rem;
}

.result-value {
    color: var(--text-primary);
    font-size: 1.4rem;
    font-weight: 700;
}

.insight-list {
    list-style: none;
    padding: 0;
    margin: 1rem 0;
}

.insight-list li {
    background: #2a2a2a;
    padding: 1rem;
    margin: 0.5rem 0;
    border-radius: 8px;
    color: var(--text-secondary);
    display: flex;
    align-items: start;
    gap: 0.75rem;
}

.insight-list li::before {
    content: "•";
    color: var(--success-color);
    font-size: 1.5rem;
    flex-shrink: 0;
}

.confidence-badge {
    display: inline-block;
    padding: 0.5rem 1rem;
    border-radius: 20px;
    font-weight: 600;
    font-size: 0.9rem;
}

.confidence-high {
    background: rgba(108, 152, 95, 0.2);
    color: var(--success-color);
    border: 1px solid var(--success-color);
}

.confidence-medium {
    background: rgba(255, 193, 7, 0.2);
    color: #ffc107;
    border: 1px solid #ffc107;
}

.confidence-low {
    background: rgba(231, 76, 60, 0.2);
    color: #e74c3c;
    border: 1px solid #e74c3c;
}

.result-section img {
    cursor: pointer;
    transition: all 0.3s ease;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.3);
}

.result-section img:hover {
    transform: scale(1.05);
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.5);
    border-color: var(--success-color) !important;
}

.success-footer {
    padding: 1.5rem 2rem;
    border-top: 1px solid var(--card-border);
    display: flex;
    gap: 1rem;
    justify-content: flex-end;
}

.btn-primary, .btn-secondary {
    padding: 0.875rem 1.5rem;
    border-radius: 8px;
    font-weight: 600;
    cursor: pointer;
    border: none;
    transition: all 0.2s;
}

.btn-primary {
    background: var(--success-color);
    color: white;
    flex: 1;
}

.btn-primary:hover {
    background: #7aa570;
    transform: translateY(-2px);
}

.btn-secondary {
    background: transparent;
    border: 1px solid var(--card-border);
    color: var(--text-secondary);
}

.btn-secondary:hover {
    border-color: var(--text-primary);
    color: var(--text-primary);
}

@media (max-width: 768px) {
    .success-modal-content {
        width: 95%;
        max-height: 90vh;
    }
    
    .result-grid {
        grid-template-columns: 1fr;
    }
    
    .success-footer {
        flex-direction: column;
    }
}
</style>

<script>
function closeSuccessModal() {
    document.getElementById('success-modal').style.display = 'none';
}

function showSuccessModal(result) {
    const modal = document.getElementById('success-modal');
    const body = document.getElementById('success-body');
    const icon = document.getElementById('success-icon');
    const title = document.getElementById('success-title');
    
    const analysis = result.ai_analysis;
    const photoType = result.metadata?.photo_type || result.ai_analysis?.photo_type || 'general';
    
    // Build image preview section
    let imagePreviewHTML = '';
    if (result.thumbnail || result.thumbnails) {
        imagePreviewHTML = '<div class="result-section" style="text-align: center;">';
        imagePreviewHTML += '<h3 style="margin-bottom: 1rem;">📸 Analyzed Photos</h3>';
        imagePreviewHTML += '<div style="display: flex; gap: 1rem; justify-content: center; flex-wrap: wrap; margin-bottom: 1rem;">';
        
        if (result.thumbnails && Array.isArray(result.thumbnails)) {
            // Multiple images
            result.thumbnails.forEach((thumb, index) => {
                if (thumb) {
                    imagePreviewHTML += `
                        <div style="position: relative;">
                            <img src="${thumb}" style="max-width: 200px; max-height: 200px; border-radius: 8px; border: 2px solid var(--card-border);" 
                                 alt="Uploaded image ${index + 1}" onclick="this.style.maxWidth = this.style.maxWidth === '200px' ? '90%' : '200px';" 
                                 title="Click to enlarge">
                            <div style="position: absolute; top: 5px; right: 5px; background: rgba(0,0,0,0.7); color: white; padding: 2px 8px; border-radius: 4px; font-size: 0.8rem;">
                                ${index + 1}/${result.thumbnails.length}
                            </div>
                        </div>
                    `;
                }
            });
        } else if (result.thumbnail) {
            // Single image
            imagePreviewHTML += `
                <img src="${result.thumbnail}" style="max-width: 300px; max-height: 300px; border-radius: 8px; border: 2px solid var(--card-border);" 
                     alt="Uploaded image" onclick="this.style.maxWidth = this.style.maxWidth === '300px' ? '90%' : '300px';" 
                     title="Click to enlarge">
            `;
        }
        
        imagePreviewHTML += '</div>';
        if (result.image_count && result.image_count > 1) {
            imagePreviewHTML += `<p style="color: var(--text-muted); font-size: 0.9rem;">${result.image_count} images analyzed as complete meal</p>`;
        }
        imagePreviewHTML += '</div>';
    }
    
    // Update icon and title based on photo type
    switch(photoType) {
        case 'stool':
            icon.textContent = '🚽';
            title.textContent = 'Stool Analysis Complete!';
            body.innerHTML = imagePreviewHTML + renderStoolAnalysis(analysis);
            break;
        case 'meal':
            icon.textContent = '🍽️';
            title.textContent = 'Meal Analysis Complete!';
            body.innerHTML = imagePreviewHTML + renderMealAnalysis(analysis);
            break;
        case 'symptom':
            icon.textContent = '🩺';
            title.textContent = 'Symptom Documented!';
            body.innerHTML = imagePreviewHTML + renderSymptomAnalysis(analysis);
            break;
    }
    
    modal.style.display = 'flex';
}

function renderStoolAnalysis(analysis) {
    const confidence = analysis.confidence || 85; // Default to reasonable confidence if missing
    const confidenceClass = confidence >= 85 ? 'confidence-high' : confidence >= 70 ? 'confidence-medium' : 'confidence-low';
    
    return `
        <div class="result-section">
            <h3>🎯 Bristol Stool Scale</h3>
            <div class="result-grid">
                <div class="result-item">
                    <div class="result-label">Type</div>
                    <div class="result-value">${analysis.bristol_scale || 'N/A'}</div>
                </div>
                <div class="result-item">
                    <div class="result-label">Consistency</div>
                    <div class="result-value">${analysis.consistency || 'N/A'}</div>
                </div>
                <div class="result-item">
                    <div class="result-label">Volume</div>
                    <div class="result-value">${analysis.volume_estimate || 'N/A'}</div>
                </div>
            </div>
            <p style="color: var(--text-secondary); margin-top: 1rem;">${analysis.bristol_description || ''}</p>
            <p style="color: var(--text-muted); margin-top: 0.5rem; font-size: 0.9rem;">Color: ${analysis.color_assessment || 'Not specified'}</p>
        </div>

        ${analysis.health_insights && analysis.health_insights.length > 0 ? `
        <div class="result-section">
            <h3>💡 Health Insights</h3>
            <ul class="insight-list">
                ${analysis.health_insights.map(insight => `<li>${insight}</li>`).join('')}
            </ul>
        </div>
        ` : ''}

        ${analysis.recommendations && analysis.recommendations.length > 0 ? `
        <div class="result-section">
            <h3>📋 Recommendations</h3>
            <ul class="insight-list">
                ${analysis.recommendations.map(rec => `<li>${rec}</li>`).join('')}
            </ul>
        </div>
        ` : ''}

        <div class="result-section" style="text-align: center;">
            <span class="${confidenceClass} confidence-badge">AI Confidence: ${confidence}%</span>
        </div>
    `;
}

function renderMealAnalysis(analysis) {
    if (!analysis.calcuplate) {
        return '<div class="result-section"><p style="color: var(--text-secondary);">Manual meal logging required. Analysis will be saved once you complete the form.</p></div>';
    }
    
    const calcuplate = analysis.calcuplate;
    // Look for confidence in multiple places
    const confidence = analysis.confidence || calcuplate.confidence || analysis.calcuplate?.confidence || 85;
    const confidenceClass = confidence >= 85 ? 'confidence-high' : confidence >= 70 ? 'confidence-medium' : 'confidence-low';
    
    return `
        <div class="result-section">
            <h3>🍽️ CalcuPlate Analysis</h3>
            <div class="result-grid">
                <div class="result-item">
                    <div class="result-label">Calories</div>
                    <div class="result-value">${calcuplate.total_calories || 'N/A'}</div>
                </div>
                <div class="result-item">
                    <div class="result-label">Protein</div>
                    <div class="result-value">${calcuplate.macros?.protein || 'N/A'}</div>
                </div>
                <div class="result-item">
                    <div class="result-label">Carbs</div>
                    <div class="result-value">${calcuplate.macros?.carbs || 'N/A'}</div>
                </div>
                <div class="result-item">
                    <div class="result-label">Fat</div>
                    <div class="result-value">${calcuplate.macros?.fat || 'N/A'}</div>
                </div>
            </div>
            <p style="color: var(--text-secondary); margin-top: 1rem;">
                <strong>Foods Detected:</strong> ${calcuplate.foods_detected?.join(', ') || 'None'}
            </p>
            <p style="color: var(--text-muted); margin-top: 0.5rem; font-size: 0.9rem;">
                Meal Quality: ${calcuplate.meal_quality_score || 'N/A'} | 
                Nutritional Completeness: ${calcuplate.nutritional_completeness || 'N/A'}
            </p>
        </div>

        ${analysis.nutrition_insights && analysis.nutrition_insights.length > 0 ? `
        <div class="result-section">
            <h3>💡 Nutrition Insights</h3>
            <ul class="insight-list">
                ${analysis.nutrition_insights.map(insight => `<li>${insight}</li>`).join('')}
            </ul>
        </div>
        ` : ''}

        ${analysis.recommendations && analysis.recommendations.length > 0 ? `
        <div class="result-section">
            <h3>📋 Recommendations</h3>
            <ul class="insight-list">
                ${analysis.recommendations.map(rec => `<li>${rec}</li>`).join('')}
            </ul>
        </div>
        ` : ''}

        <div class="result-section" style="text-align: center;">
            <span class="${confidenceClass} confidence-badge">AI Confidence: ${confidence}%</span>
        </div>
    `;
}

function renderSymptomAnalysis(analysis) {
    const confidence = analysis.confidence || 80; // Default to reasonable confidence if missing
    const confidenceClass = confidence >= 85 ? 'confidence-high' : confidence >= 70 ? 'confidence-medium' : 'confidence-low';
    
    return `
        <div class="result-section">
            <h3>🩺 Symptom Documentation</h3>
            <div class="result-grid">
                <div class="result-item">
                    <div class="result-label">Category</div>
                    <div class="result-value" style="font-size: 1.1rem;">${analysis.symptom_category || 'N/A'}</div>
                </div>
                <div class="result-item">
                    <div class="result-label">Severity</div>
                    <div class="result-value" style="font-size: 1.1rem;">${analysis.severity_estimate || 'N/A'}</div>
                </div>
            </div>
        </div>

        ${analysis.visual_characteristics && analysis.visual_characteristics.length > 0 ? `
        <div class="result-section">
            <h3>👁️ Visual Characteristics</h3>
            <ul class="insight-list">
                ${analysis.visual_characteristics.map(char => `<li>${char}</li>`).join('')}
            </ul>
        </div>
        ` : ''}

        ${analysis.tracking_recommendations && analysis.tracking_recommendations.length > 0 ? `
        <div class="result-section">
            <h3>📋 Tracking Recommendations</h3>
            <ul class="insight-list">
                ${analysis.tracking_recommendations.map(rec => `<li>${rec}</li>`).join('')}
            </ul>
        </div>
        ` : ''}

        ${analysis.correlation_potential ? `
        <div class="result-section">
            <h3>🔗 Pattern Analysis</h3>
            <p style="color: var(--text-secondary);">${analysis.correlation_potential}</p>
        </div>
        ` : ''}

        <div class="result-section" style="text-align: center;">
            <span class="${confidenceClass} confidence-badge">AI Confidence: ${confidence}%</span>
        </div>
    `;
}
</script>
