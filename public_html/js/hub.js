// QuietGo Hub JavaScript - Subscriber dashboard functionality
// Handles authentication, data management, and API interactions

// State management
let userData = null;
let filesData = [];

// Initialize hub on page load
document.addEventListener('DOMContentLoaded', async function() {
    await checkAuthentication();
});

// Check if user is authenticated
async function checkAuthentication() {
    try {
        const response = await fetch('/api/auth/user', {
            credentials: 'include'
        });
        
        if (response.ok) {
            userData = await response.json();
            showHubContent();
            updateUserInfo();
            await loadDashboardData();
        } else {
            // User not authenticated, redirect to login
            window.location.href = '/api/login';
        }
    } catch (error) {
        console.error('Authentication check failed:', error);
        showError('Failed to check authentication. Please try again.');
        setTimeout(() => {
            window.location.href = '/api/login';
        }, 3000);
    }
}

// Show main hub content
function showHubContent() {
    document.getElementById('loadingState').style.display = 'none';
    document.getElementById('hubContent').style.display = 'block';
}

// Update user info in navigation
function updateUserInfo() {
    if (userData && userData.email) {
        // Update existing elements (with null checks for new structure)
        const userEmailEl = document.getElementById('userEmail');
        const userEmailMobileEl = document.getElementById('userEmailMobile');
        if (userEmailEl) userEmailEl.textContent = userData.email;
        if (userEmailMobileEl) userEmailMobileEl.textContent = userData.email;
        
        // Update new enhanced profile elements
        const userName = `${userData.firstName || 'Health'} ${userData.lastName || 'Tracker'}`;
        const displayName = userData.firstName || 'Friend';
        
        // Update profile displays
        const userNameEl = document.getElementById('userName');
        const welcomeNameEl = document.getElementById('welcomeName');
        const userNameMobileEl = document.getElementById('userNameMobile');
        
        if (userNameEl) userNameEl.textContent = userData.email;
        if (welcomeNameEl) welcomeNameEl.textContent = displayName;
        if (userNameMobileEl) userNameMobileEl.textContent = userName;
        
        // Handle profile images
        if (userData.profileImageUrl) {
            const avatar = document.getElementById('userAvatar');
            const avatarMobile = document.getElementById('userAvatarMobile');
            if (avatar) {
                avatar.src = userData.profileImageUrl;
                avatar.style.display = 'block';
            }
            if (avatarMobile) {
                avatarMobile.src = userData.profileImageUrl;
                avatarMobile.style.display = 'block';
            }
        }
        
        // Update subscription status
        updateSubscriptionDisplay(userData.subscriptionStatus || 'free', userData.subscriptionPlan || 'free');
    }
}

// Load dashboard data
async function loadDashboardData() {
    try {
        const stats = await loadDashboardStats();
        updateDataOverview(stats);
        populateHealthStats();
        await loadRecentFiles();
    } catch (error) {
        console.error('Failed to load dashboard data:', error);
        showError('Failed to load your data. Please refresh the page.');
    }
}

// Update data overview stats
function updateDataOverview(stats) {
    const totalEntriesEl = document.getElementById('totalEntries');
    const lastSyncEl = document.getElementById('lastSync');
    const dataSizeEl = document.getElementById('dataSize');
    
    if (totalEntriesEl) totalEntriesEl.textContent = stats.totalEntries || '--';
    if (lastSyncEl) lastSyncEl.textContent = stats.lastSync || '--';
    if (dataSizeEl) dataSizeEl.textContent = stats.dataSize || '--';
}

// Load dashboard stats from API
async function loadDashboardStats() {
    try {
        const [logsResponse, uploadsResponse] = await Promise.all([
            fetch('/api/health-logs?limit=1000', { credentials: 'include' }),
            fetch('/api/uploads', { credentials: 'include' })
        ]);
        
        let totalEntries = 0;
        let lastSync = 'Never';
        let dataSize = '0 MB';
        
        if (logsResponse.ok) {
            const logs = await logsResponse.json();
            totalEntries = logs.length;
            if (logs.length > 0) {
                const lastLog = new Date(logs[0].createdAt || logs[0].timestamp);
                lastSync = formatRelativeTime(lastLog);
            }
        }
        
        if (uploadsResponse.ok) {
            const uploads = await uploadsResponse.json();
            const totalBytes = uploads.reduce((sum, file) => sum + (file.size || 0), 0);
            dataSize = formatFileSize(totalBytes);
        }
        
        return { totalEntries, lastSync, dataSize };
    } catch (error) {
        console.error('Failed to load dashboard stats:', error);
        return { totalEntries: '--', lastSync: '--', dataSize: '--' };
    }
}

// Load recent files
async function loadRecentFiles() {
    try {
        const response = await fetch('/api/uploads', {
            credentials: 'include'
        });
        
        const filesList = document.getElementById('filesList');
        
        if (response.ok) {
            filesData = await response.json();
            filesData = filesData.map(file => ({
                id: file.id,
                name: file.originalName || file.filename,
                size: formatFileSize(file.size),
                uploadDate: file.createdAt || file.uploadDate,
                type: file.type || 'unknown'
            }));
        } else {
            console.error('Failed to load files:', response.statusText);
            filesData = [];
        }
        
        if (filesData.length === 0) {
            filesList.innerHTML = `
                <div class="text-center muted" style="padding: 32px;">
                    No files uploaded yet. Use the upload button above to sync your first data.
                </div>
            `;
        } else {
            renderFilesList();
        }
    } catch (error) {
        console.error('Failed to load files:', error);
        const filesList = document.getElementById('filesList');
        filesList.innerHTML = `
            <div class="text-center muted" style="padding: 32px;">
                Failed to load files. <button onclick="loadRecentFiles()" class="btn btn-outline" style="margin-left: 8px;">Try Again</button>
            </div>
        `;
    }
}

// Render files list
function renderFilesList() {
    const filesList = document.getElementById('filesList');
    
    // Clear existing content
    filesList.innerHTML = '';
    
    filesData.forEach(file => {
        // Create container div
        const fileRow = document.createElement('div');
        fileRow.className = 'flex flex-between';
        fileRow.style.cssText = 'padding: 16px; border-bottom: 1px solid var(--border-color);';
        
        // Create file info section
        const fileInfo = document.createElement('div');
        
        // File name (safely set as text content)
        const fileName = document.createElement('div');
        fileName.style.cssText = 'font-weight: 500; margin-bottom: 4px;';
        fileName.textContent = file.name; // Safe: no HTML injection
        fileInfo.appendChild(fileName);
        
        // File details (safely set as text content)
        const fileDetails = document.createElement('div');
        fileDetails.className = 'muted';
        fileDetails.style.cssText = 'font-size: 0.875rem;';
        fileDetails.textContent = `${file.size} • Uploaded ${formatDate(file.uploadDate)} • ${file.type}`; // Safe: no HTML injection
        fileInfo.appendChild(fileDetails);
        
        // Create buttons section
        const buttonsDiv = document.createElement('div');
        buttonsDiv.className = 'flex gap-sm';
        
        // Download button
        const downloadBtn = document.createElement('button');
        downloadBtn.className = 'btn btn-outline';
        downloadBtn.style.cssText = 'padding: 8px 16px;';
        downloadBtn.onclick = () => downloadFile(file.id); // Safe: direct function call
        downloadBtn.innerHTML = `
            <svg class="icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" style="width: 16px; height: 16px; margin-right: 4px;">
                <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/>
                <polyline points="7,10 12,15 17,10"/>
                <line x1="12" y1="15" x2="12" y2="3"/>
            </svg>
            Download
        `;
        
        // Delete button
        const deleteBtn = document.createElement('button');
        deleteBtn.className = 'btn btn-outline';
        deleteBtn.style.cssText = 'padding: 8px 16px; color: #ef4444; border-color: #ef4444;';
        deleteBtn.onclick = () => deleteFile(file.id); // Safe: direct function call
        deleteBtn.innerHTML = `
            <svg class="icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" style="width: 16px; height: 16px;">
                <polyline points="3,6 5,6 21,6"/>
                <path d="M19,6V20a2,2,0,0,1-2,2H7a2,2,0,0,1-2-2V6M8,6V4a2,2,0,0,1,2-2h4a2,2,0,0,1,2,2V6"/>
            </svg>
        `;
        
        buttonsDiv.appendChild(downloadBtn);
        buttonsDiv.appendChild(deleteBtn);
        
        // Assemble the row
        fileRow.appendChild(fileInfo);
        fileRow.appendChild(buttonsDiv);
        
        filesList.appendChild(fileRow);
    });
}

// Upload modal functions
function openUploadModal() {
    const modal = document.getElementById('uploadModal');
    modal.classList.add('open');
    document.body.style.overflow = 'hidden';
}

function closeUploadModal() {
    const modal = document.getElementById('uploadModal');
    modal.classList.remove('open');
    document.body.style.overflow = '';
    
    // Reset form
    document.getElementById('uploadForm').reset();
    document.getElementById('uploadProgress').style.display = 'none';
    document.getElementById('progressBar').style.width = '0%';
}

// Handle file upload
document.getElementById('uploadForm').addEventListener('submit', async function(e) {
    e.preventDefault();
    
    const fileInput = document.getElementById('fileInput');
    const files = fileInput.files;
    
    if (files.length === 0) {
        showError('Please select at least one file to upload.');
        return;
    }
    
    // Validate files
    for (let file of files) {
        if (file.size > 10 * 1024 * 1024) { // 10MB limit
            showError(`File ${file.name} is too large. Maximum size is 10MB.`);
            return;
        }
        
        const allowedTypes = ['text/csv', 'application/json'];
        if (!allowedTypes.includes(file.type)) {
            showError(`File ${file.name} has an unsupported format. Please use CSV or JSON files.`);
            return;
        }
    }
    
    await uploadFiles(files);
});

// Upload files to server
async function uploadFiles(files) {
    const progressDiv = document.getElementById('uploadProgress');
    const progressBar = document.getElementById('progressBar');
    const statusText = document.getElementById('uploadStatus');
    const uploadBtn = document.getElementById('uploadBtn');
    
    progressDiv.style.display = 'block';
    uploadBtn.disabled = true;
    uploadBtn.textContent = 'Uploading...';
    
    try {
        const formData = new FormData();
        for (let file of files) {
            formData.append('files', file);
        }
        
        // Simulate upload progress
        let progress = 0;
        const progressInterval = setInterval(() => {
            progress += 10;
            progressBar.style.width = `${Math.min(progress, 90)}%`;
            statusText.textContent = `Uploading... ${Math.min(progress, 90)}%`;
        }, 200);
        
        // Upload to existing endpoint
        const response = await fetch('/api/upload', {
            method: 'POST',
            body: formData,
            credentials: 'include'
        });
        
        clearInterval(progressInterval);
        progressBar.style.width = '100%';
        statusText.textContent = 'Processing files...';
        
        if (response.ok) {
            const result = await response.json();
            statusText.textContent = 'Upload completed successfully!';
            
            setTimeout(() => {
                closeUploadModal();
                showSuccess(`Successfully uploaded ${files.length} file(s).`);
                loadRecentFiles(); // Refresh the files list
                loadDashboardData(); // Refresh stats
            }, 1000);
        } else {
            throw new Error('Upload failed');
        }
    } catch (error) {
        console.error('Upload error:', error);
        statusText.textContent = 'Upload failed. Please try again.';
        showError('Failed to upload files. Please check your connection and try again.');
    } finally {
        uploadBtn.disabled = false;
        uploadBtn.textContent = 'Upload Files';
    }
}

// Generate report
async function generateReport() {
    try {
        showSuccess('Generating your health report... This may take a moment.');
        
        // Use the export data endpoint for now
        const response = await fetch('/api/export-data', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({ format: 'csv' }),
            credentials: 'include'
        });
        
        if (response.ok) {
            const blob = await response.blob();
            const url = window.URL.createObjectURL(blob);
            const a = document.createElement('a');
            a.href = url;
            a.download = `quietgo-health-report-${formatDate(new Date())}.csv`;
            document.body.appendChild(a);
            a.click();
            window.URL.revokeObjectURL(url);
            document.body.removeChild(a);
            
            showSuccess('Report generated and downloaded successfully!');
        } else {
            throw new Error('Report generation failed');
        }
    } catch (error) {
        console.error('Report generation error:', error);
        showError('Failed to generate report. Please try again later.');
    }
}

// Export data
async function exportData() {
    try {
        showSuccess('Preparing your data export...');
        
        // Use the existing export endpoint
        const response = await fetch('/api/export-data', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({ format: 'csv' }),
            credentials: 'include'
        });
        
        if (response.ok) {
            const blob = await response.blob();
            const url = window.URL.createObjectURL(blob);
            const a = document.createElement('a');
            a.href = url;
            a.download = `quietgo-data-export-${formatDate(new Date())}.csv`;
            document.body.appendChild(a);
            a.click();
            window.URL.revokeObjectURL(url);
            document.body.removeChild(a);
            
            showSuccess('Data exported and downloaded successfully!');
        } else {
            throw new Error('Data export failed');
        }
    } catch (error) {
        console.error('Export error:', error);
        showError('Failed to export data. Please try again later.');
    }
}

// Download individual file (currently not supported by backend)
async function downloadFile(fileId) {
    try {
        const file = filesData.find(f => f.id === fileId);
        if (!file) {
            showError('File not found.');
            return;
        }
        
        // For now, export all data as CSV since individual file download isn't available
        showSuccess('Exporting your data instead of individual file download...');
        await exportData();
        
    } catch (error) {
        console.error('Download error:', error);
        showError('Failed to download file. Please try again.');
    }
}

// Delete file (currently not supported by backend)
async function deleteFile(fileId) {
    const file = filesData.find(f => f.id === fileId);
    if (!file) {
        showError('File not found.');
        return;
    }
    
    // Individual file deletion not currently supported by backend
    showError('Individual file deletion is not currently available. Please contact support if you need files removed.');
}

// Refresh data
async function refreshData() {
    showSuccess('Refreshing your data...');
    await loadDashboardData();
    showSuccess('Data refreshed successfully!');
}

// Sign out
async function handleSignOut() {
    try {
        const response = await fetch('/api/auth/logout', {
            method: 'POST',
            credentials: 'include'
        });
        
        // Redirect to home page regardless of response
        window.location.href = '/';
    } catch (error) {
        console.error('Logout error:', error);
        // Still redirect on error
        window.location.href = '/';
    }
}

// Utility functions
function formatDate(dateInput) {
    const date = typeof dateInput === 'string' ? new Date(dateInput) : dateInput;
    return date.toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'short',
        day: 'numeric'
    });
}

function showSuccess(message) {
    showToast(message, 'success');
}

function showError(message) {
    showToast(message, 'error');
}

function showToast(message, type = 'info') {
    // Create toast element
    const toast = document.createElement('div');
    toast.style.cssText = `
        position: fixed;
        top: 20px;
        right: 20px;
        padding: 16px 24px;
        border-radius: 8px;
        color: white;
        font-weight: 500;
        z-index: 10000;
        max-width: 400px;
        box-shadow: 0 8px 25px rgba(0, 0, 0, 0.3);
        opacity: 0;
        transform: translateX(100%);
        transition: all 0.3s ease;
    `;
    
    // Set color based on type
    if (type === 'success') {
        toast.style.backgroundColor = 'var(--green-color)';
    } else if (type === 'error') {
        toast.style.backgroundColor = '#ef4444';
    } else {
        toast.style.backgroundColor = 'var(--accent-color)';
    }
    
    toast.textContent = message;
    document.body.appendChild(toast);
    
    // Animate in
    setTimeout(() => {
        toast.style.opacity = '1';
        toast.style.transform = 'translateX(0)';
    }, 100);
    
    // Remove after 5 seconds
    setTimeout(() => {
        toast.style.opacity = '0';
        toast.style.transform = 'translateX(100%)';
        setTimeout(() => {
            if (toast.parentNode) {
                toast.parentNode.removeChild(toast);
            }
        }, 300);
    }, 5000);
}

// Utility function to format file sizes
function formatFileSize(bytes) {
    if (bytes === 0) return '0 Bytes';
    const k = 1024;
    const sizes = ['Bytes', 'KB', 'MB', 'GB'];
    const i = Math.floor(Math.log(bytes) / Math.log(k));
    return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
}

// Utility function to format relative time
function formatRelativeTime(date) {
    const now = new Date();
    const diffMs = now - date;
    const diffHours = Math.floor(diffMs / (1000 * 60 * 60));
    const diffDays = Math.floor(diffHours / 24);
    
    if (diffHours < 1) {
        return 'Just now';
    } else if (diffHours < 24) {
        return `${diffHours} hour${diffHours > 1 ? 's' : ''} ago`;
    } else if (diffDays < 7) {
        return `${diffDays} day${diffDays > 1 ? 's' : ''} ago`;
    } else {
        return date.toLocaleDateString();
    }
}

// Enhanced Hub Functions for new interface

// Update subscription display in header and sidebar
function updateSubscriptionDisplay(status, plan) {
    const subscriptionElements = [
        document.getElementById('userSubscription'),
        document.getElementById('userSubscriptionMobile'),
        document.getElementById('subscriptionTier')
    ];
    
    let badgeClass = 'status-free';
    let displayText = 'Free Plan';
    
    if (status === 'active') {
        if (plan === 'pro_yearly' || plan === 'pro_monthly') {
            badgeClass = 'status-pro';
            displayText = 'Pro Plan';
        }
    }
    
    subscriptionElements.forEach(el => {
        if (el) {
            el.className = `status-badge ${badgeClass}`;
            el.textContent = displayText;
        }
    });
}

// Populate health statistics with demo/real data
function populateHealthStats() {
    // Demo data - replace with real API calls when backend is ready
    const stats = {
        totalEntries: 24,
        lastSync: '2h',
        insights: 7,
        healthStreak: 3
    };
    
    // Update stat displays
    const totalEntriesEl = document.getElementById('totalEntries');
    const lastSyncEl = document.getElementById('lastSync');
    const insightsEl = document.getElementById('insights');
    const healthStreakEl = document.getElementById('healthStreak');
    const totalInsightsEl = document.getElementById('totalInsights');
    
    if (totalEntriesEl) totalEntriesEl.textContent = stats.totalEntries;
    if (lastSyncEl) lastSyncEl.textContent = stats.lastSync;
    if (insightsEl) insightsEl.textContent = stats.insights;
    if (totalInsightsEl) totalInsightsEl.textContent = stats.insights;
    if (healthStreakEl) healthStreakEl.innerHTML = `🔥 ${stats.healthStreak} day tracking streak`;
}

// Health tracking actions
function openStoolEntry() {
    showToast('Stool logging feature coming soon! This will include Bristol Scale classification and AI analysis.', 'info');
    // TODO: Navigate to stool entry form when implemented
}

function openMealEntry() {
    showToast('Meal logging feature coming soon! Upload photos for AI nutrition analysis.', 'info');
    // TODO: Navigate to meal entry form when implemented
}

function openInsights() {
    showToast('Health insights dashboard coming soon! View patterns and correlations in your data.', 'info');
    // TODO: Navigate to insights dashboard when implemented
}

function openMobileApp() {
    showToast('Subscription management is handled through the mobile app.', 'info');
    // TODO: Deep link to mobile app subscription page
    setTimeout(() => {
        window.open('https://apps.apple.com/app/quietgo', '_blank');
    }, 1000);
}

// Initialize modal close functionality for upload modal
document.getElementById('uploadModal').addEventListener('click', function(e) {
    if (e.target === this) {
        closeUploadModal();
    }
});