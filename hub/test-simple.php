<?php
// Simple test to see if JavaScript works
include __DIR__ . '/includes/header-hub.php';
?>

<style>
.test-button {
    background: #6c985f;
    color: white;
    padding: 1rem 2rem;
    border: none;
    border-radius: 8px;
    cursor: pointer;
    font-size: 1.1rem;
    margin: 2rem;
}
</style>

<main>
    <div style="padding: 2rem; text-align: center;">
        <h1>Test Page</h1>
        <button class="test-button" onclick="alert('BUTTON WORKS!')">Test Button</button>
        
        <a href="/hub/upload.php" class="test-button" style="display: inline-block; text-decoration: none;">Go to Upload</a>
        
        <div class="action-item" style="background: #333; padding: 1rem; margin: 1rem; cursor: pointer; border-radius: 8px;">
            <h3>Test Feature Button</h3>
            <p>This should show an alert when clicked</p>
        </div>
    </div>
</main>

<script>
console.log('TEST: JavaScript loading...');

document.addEventListener('DOMContentLoaded', function() {
    console.log('TEST: DOM loaded');
    
    const actionItem = document.querySelector('.action-item');
    if (actionItem) {
        console.log('TEST: Found action item');
        actionItem.addEventListener('click', function(e) {
            e.preventDefault();
            alert('ACTION ITEM CLICKED!');
        });
    }
});
</script>

<?php include __DIR__ . '/includes/footer-hub.php'; ?>
