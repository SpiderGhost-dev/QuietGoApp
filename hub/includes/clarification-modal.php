<!-- CalcuPlate Clarification Modal -->
<div class="clarification-modal" id="clarification-modal" style="display: none;">
    <div class="clarification-content">
        <div class="clarification-header">
            <div class="clarification-icon">🤔</div>
            <h2>Quick Clarification Needed</h2>
            <p>Help CalcuPlate be more accurate by answering these quick questions:</p>
        </div>

        <div class="clarification-body" id="clarification-questions">
            <!-- Questions populated by JavaScript -->
        </div>

        <div class="clarification-footer">
            <button onclick="submitClarifications()" class="btn-clarify-primary">
                ✅ Analyze with These Answers
            </button>
        </div>
    </div>
</div>

<style>
.clarification-modal {
    position: fixed;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: rgba(0, 0, 0, 0.9);
    display: flex;
    align-items: center;
    justify-content: center;
    z-index: 2100;
    animation: fadeIn 0.3s ease;
}

.clarification-content {
    background: var(--card-bg, #2a2a2a);
    border: 2px solid var(--primary-blue, #4682b4);
    border-radius: 16px;
    max-width: 600px;
    width: 90%;
    max-height: 80vh;
    overflow: hidden;
    display: flex;
    flex-direction: column;
    animation: slideUp 0.4s ease;
}

.clarification-header {
    background: linear-gradient(135deg, var(--primary-blue, #4682b4), #5a92c4);
    color: white;
    padding: 1.5rem;
    text-align: center;
}

.clarification-icon {
    font-size: 3rem;
    margin-bottom: 0.5rem;
}

.clarification-header h2 {
    margin: 0 0 0.5rem 0;
    font-size: 1.6rem;
}

.clarification-header p {
    margin: 0;
    opacity: 0.95;
    font-size: 0.95rem;
}

.clarification-body {
    padding: 1.5rem;
    overflow-y: auto;
    flex: 1;
}

.clarification-question {
    margin: 1.5rem 0;
    padding: 1.5rem;
    background: #333;
    border-radius: 12px;
    border-left: 4px solid var(--primary-blue, #4682b4);
}

.clarification-question h4 {
    color: white;
    margin: 0 0 0.5rem 0;
    font-size: 1.1rem;
}

.clarification-question p {
    color: #999;
    margin: 0 0 1rem 0;
    font-size: 0.9rem;
}

.clarification-options {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(140px, 1fr));
    gap: 0.75rem;
}

.clarification-option {
    background: #2a2a2a;
    border: 2px solid #404040;
    border-radius: 8px;
    padding: 0.875rem;
    cursor: pointer;
    transition: all 0.2s;
    text-align: center;
}

.clarification-option:hover {
    border-color: var(--primary-blue, #4682b4);
    transform: translateY(-2px);
}

.clarification-option.selected {
    background: rgba(70, 130, 180, 0.15);
    border-color: var(--primary-blue, #4682b4);
    box-shadow: 0 0 0 2px rgba(70, 130, 180, 0.3);
}

.clarification-option-label {
    color: white;
    font-weight: 600;
    display: block;
    margin-bottom: 0.25rem;
}

.clarification-option-detail {
    color: #999;
    font-size: 0.8rem;
}

.clarification-footer {
    padding: 1.5rem;
    border-top: 1px solid #404040;
    text-align: center;
}

.btn-clarify-primary {
    background: var(--success-color, #6c985f);
    color: white;
    border: none;
    padding: 1rem 2rem;
    border-radius: 8px;
    font-size: 1.1rem;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.2s;
    min-width: 300px;
}

.btn-clarify-primary:hover:not(:disabled) {
    background: #7aa570;
    transform: translateY(-2px);
}

.btn-clarify-primary:disabled {
    opacity: 0.5;
    cursor: not-allowed;
}

@media (max-width: 768px) {
    .clarification-options {
        grid-template-columns: 1fr;
    }
}
</style>

<script>
let clarificationData = null;
let clarificationAnswers = {};

function showClarificationModal(questions, originalData) {
    clarificationData = originalData;
    clarificationAnswers = {};
    
    const modal = document.getElementById('clarification-modal');
    const questionsContainer = document.getElementById('clarification-questions');
    
    questionsContainer.innerHTML = '';
    
    questions.forEach((question, index) => {
        const questionDiv = document.createElement('div');
        questionDiv.className = 'clarification-question';
        questionDiv.innerHTML = `
            <h4>${question.item}</h4>
            <p>${question.question}</p>
            <div class="clarification-options" data-question="${index}">
                ${question.options.map((opt, optIndex) => `
                    <div class="clarification-option" 
                         onclick="selectClarificationOption(${index}, ${optIndex}, '${opt}')"
                         data-option="${optIndex}">
                        <span class="clarification-option-label">${opt}</span>
                        ${question.impact ? `<span class="clarification-option-detail">${question.impact}</span>` : ''}
                    </div>
                `).join('')}
            </div>
        `;
        questionsContainer.appendChild(questionDiv);
    });
    
    modal.style.display = 'flex';
    updateClarifyButton();
}

function selectClarificationOption(questionIndex, optionIndex, value) {
    // Store the answer
    clarificationAnswers[questionIndex] = value;
    
    // Update UI
    const questionContainer = document.querySelector(`[data-question="${questionIndex}"]`);
    questionContainer.querySelectorAll('.clarification-option').forEach(opt => {
        opt.classList.remove('selected');
    });
    questionContainer.querySelector(`[data-option="${optionIndex}"]`).classList.add('selected');
    
    updateClarifyButton();
}

function updateClarifyButton() {
    const btn = document.querySelector('.btn-clarify-primary');
    const totalQuestions = document.querySelectorAll('.clarification-question').length;
    const answeredQuestions = Object.keys(clarificationAnswers).length;
    
    if (answeredQuestions === totalQuestions && totalQuestions > 0) {
        btn.disabled = false;
        btn.textContent = `✅ Analyze with These Answers (${answeredQuestions}/${totalQuestions})`;
    } else {
        btn.disabled = true;
        btn.textContent = `Answer All Questions (${answeredQuestions}/${totalQuestions})`;
    }
}

function submitClarifications() {
    const modal = document.getElementById('clarification-modal');
    const btn = document.querySelector('.btn-clarify-primary');
    
    // Show loading state
    btn.disabled = true;
    btn.textContent = '🔄 Re-analyzing with your answers...';
    
    // Prepare clarified data
    const clarifiedData = {
        ...clarificationData,
        clarifications: clarificationAnswers,
        skip_questions: true // Don't ask again
    };
    
    // Make AJAX request to re-analyze with clarifications
    fetch('/hub/ajax/reanalyze-meal.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
        },
        body: JSON.stringify(clarifiedData)
    })
    .then(response => response.json())
    .then(result => {
        modal.style.display = 'none';
        
        if (result.status === 'success') {
            // Show success modal with clarified analysis
            showSuccessModal(result);
        } else {
            alert(result.message || 'Analysis failed. Please try again.');
        }
    })
    .catch(error => {
        console.error('Clarification error:', error);
        alert('Failed to process clarifications. Please try again.');
        btn.disabled = false;
        btn.textContent = '✅ Analyze with These Answers';
    });
}

// Hook into upload response to check for clarification needs
function handleUploadResponse(result) {
    if (result.ai_analysis && result.ai_analysis.needs_clarification) {
        // Show clarification modal instead of success
        showClarificationModal(result.ai_analysis.questions, result);
    } else if (result.status === 'success') {
        // Show normal success modal
        showSuccessModal(result);
    }
}
</script>
