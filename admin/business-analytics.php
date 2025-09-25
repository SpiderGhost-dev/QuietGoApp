<?php 
require_once __DIR__ . '/includes/admin-auth.php';
$adminUser = require_admin_login();

// Set title for this page
$pageTitle = "Business Intelligence Dashboard";
include __DIR__ . '/includes/header-admin.php'; 
?>

<div class="admin-layout">
    <!-- Sidebar Navigation -->
    <aside class="admin-sidebar">
        <div class="sidebar-section">
            <div class="sidebar-title">Business Intelligence</div>
            <a href="#financial" class="nav-item active" onclick="showSection('financial')">
                <span class="nav-item-icon">💰</span>
                Financial Health
            </a>
            <a href="#users" class="nav-item" onclick="showSection('users')">
                <span class="nav-item-icon">👥</span>
                User Analytics
            </a>
            <a href="#journeys" class="nav-item" onclick="showSection('journeys')">
                <span class="nav-item-icon">🎯</span>
                Journey Analytics
            </a>
            <a href="#appstore" class="nav-item" onclick="showSection('appstore')">
                <span class="nav-item-icon">📱</span>
                App Store Metrics
            </a>
        </div>
        
        <div class="sidebar-section">
            <div class="sidebar-title">Operations</div>
            <a href="#alerts" class="nav-item" onclick="showSection('alerts')">
                <span class="nav-item-icon">🚨</span>
                Critical Alerts
            </a>
            <a href="#revenue" class="nav-item" onclick="showSection('revenue')">
                <span class="nav-item-icon">📊</span>
                Revenue Breakdown
            </a>
        </div>
        
        <div class="sidebar-section">
            <div class="sidebar-title">Navigation</div>
            <a href="/admin/dashboard.php" class="nav-item">
                <span class="nav-item-icon">🏠</span>
                Main Dashboard
            </a>
        </div>
    </aside>
    
    <!-- Main Content -->
    <main class="admin-content">
        <!-- TIER 1: FINANCIAL HEALTH -->
        <div id="financial" class="content-section active">
            <div class="content-header">
                <h1 class="content-title">💰 Financial Health Dashboard</h1>
                <p class="content-subtitle">Monitor QuietGo's financial performance and subscription metrics</p>
                <div class="placeholder-notice">
                    🚀 <strong>POST-LAUNCH:</strong> Connect to Stripe API, App Store Connect, Google Play Console for real data
                </div>
            </div>
            
            <!-- Core Financial Metrics -->
            <div class="dashboard-section">
                <h3 class="section-title">Revenue & Growth</h3>
                <div class="stats-grid">
                    <div class="stat-card featured financial">
                        <div class="stat-header">
                            <div class="stat-icon">💰</div>
                            <div class="stat-period">This Month</div>
                        </div>
                        <div class="stat-value">$12,847</div>
                        <div class="stat-label">Monthly Recurring Revenue</div>
                        <div class="stat-change positive">+18% vs last month</div>
                        <div class="stat-breakdown">
                            <small>Pro: $8,210 • CalcuPlate: $4,637</small>
                        </div>
                        <div class="stat-comment">/* PLACEHOLDER: Stripe MRR calculation */</div>
                    </div>
                    
                    <div class="stat-card revenue">
                        <div class="stat-header">
                            <div class="stat-icon">📈</div>
                            <div class="stat-period">Annual</div>
                        </div>
                        <div class="stat-value">$154,164</div>
                        <div class="stat-label">Annual Recurring Revenue</div>
                        <div class="stat-change positive">+24% YoY growth</div>
                        <div class="stat-comment">/* PLACEHOLDER: MRR × 12 + annual subscriptions */</div>
                    </div>
                    
                    <div class="stat-card warning">
                        <div class="stat-header">
                            <div class="stat-icon">⚠️</div>
                            <div class="stat-period">Monthly</div>
                        </div>
                        <div class="stat-value">3.2%</div>
                        <div class="stat-label">Churn Rate</div>
                        <div class="stat-change negative">+0.4% vs last month</div>
                        <div class="stat-comment">/* PLACEHOLDER: Track subscription cancellations */</div>
                    </div>
                    
                    <div class="stat-card financial">
                        <div class="stat-header">
                            <div class="stat-icon">💎</div>
                            <div class="stat-period">Average</div>
                        </div>
                        <div class="stat-value">$287</div>
                        <div class="stat-label">Customer Lifetime Value</div>
                        <div class="stat-change positive">+12% vs last quarter</div>
                        <div class="stat-comment">/* PLACEHOLDER: LTV = ARPU ÷ Churn Rate */</div>
                    </div>
                </div>
            </div>
            
            <!-- Key Financial Insights -->
            <div class="dashboard-section">
                <h3 class="section-title">Financial Health Alerts</h3>
                <div class="alert-list">
                    <div class="alert-item warning">
                        <div class="alert-icon">⚠️</div>
                        <div class="alert-content">
                            <div class="alert-title">Payment Failures Increasing</div>
                            <div class="alert-desc">47 failed payments this week (+23% vs last week). Review billing retry logic and email campaigns.</div>
                        </div>
                        <div class="alert-actions">
                            <button class="btn-outline-small" onclick="viewFailedPayments()">View Details</button>
                            <button class="btn-primary-small" onclick="fixBillingIssues()">Fix Issues</button>
                        </div>
                    </div>
                    
                    <div class="alert-item info">
                        <div class="alert-icon">💡</div>
                        <div class="alert-content">
                            <div class="alert-title">CalcuPlate Conversion Opportunity</div>
                            <div class="alert-desc">156 Pro users haven't tried CalcuPlate add-on. Consider targeted promotion campaign.</div>
                        </div>
                        <div class="alert-actions">
                            <button class="btn-outline-small" onclick="createCalcuPlatecampaign()">Create Campaign</button>
                        </div>
                    </div>
                    
                    <div class="alert-item info">
                        <div class="alert-icon">🎯</div>
                        <div class="alert-content">
                            <div class="alert-title">Annual Plan Opportunity</div>
                            <div class="alert-desc">Monthly subscribers with 6+ months tenure are good candidates for annual plan upgrade.</div>
                        </div>
                        <div class="alert-actions">
                            <button class="btn-primary-small" onclick="targetAnnualUpgrade()">Target Users</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- TIER 1: USER ANALYTICS -->
        <div id="users" class="content-section">
            <div class="content-header">
                <h1 class="content-title">👥 User Analytics</h1>
                <p class="content-subtitle">Track user growth, engagement, and subscription conversions</p>
            </div>
            
            <!-- User Growth Metrics -->
            <div class="dashboard-section">
                <h3 class="section-title">User Growth & Engagement</h3>
                <div class="metrics-grid">
                    <div class="metric-card">
                        <div class="metric-header">
                            <h4>Total Active Users</h4>
                            <span class="metric-trend positive">↗️ +12%</span>
                        </div>
                        <div class="metric-value">1,247</div>
                        <div class="metric-details">
                            <div class="detail-row">
                                <span>Pro Subscribers</span>
                                <span>634 (50.8%)</span>
                            </div>
                            <div class="detail-row">
                                <span>CalcuPlate Add-on</span>
                                <span>312 (49.2% of Pro)</span>
                            </div>
                            <div class="detail-row">
                                <span>Free Users</span>
                                <span>613 (49.2%)</span>
                            </div>
                        </div>
                        <div class="stat-comment">/* PLACEHOLDER: Track active app users from mobile analytics */</div>
                    </div>
                    
                    <div class="metric-card">
                        <div class="metric-header">
                            <h4>Conversion Funnel</h4>
                            <span class="metric-trend positive">📈 +5%</span>
                        </div>
                        <div class="metric-details">
                            <div class="detail-row">
                                <span>App Downloads</span>
                                <span>15,125 (30 days)</span>
                            </div>
                            <div class="detail-row">
                                <span>Account Creation</span>
                                <span>4,537 (30%)</span>
                            </div>
                            <div class="detail-row">
                                <span>First Upload</span>
                                <span>2,893 (19.1%)</span>
                            </div>
                            <div class="detail-row">
                                <span>Pro Conversion</span>
                                <span>634 (4.2%)</span>
                            </div>
                        </div>
                        <div class="stat-comment">/* PLACEHOLDER: Track user funnel from app store to subscription */</div>
                    </div>
                    
                    <div class="metric-card">
                        <div class="metric-header">
                            <h4>User Engagement</h4>
                            <span class="metric-trend neutral">📊</span>
                        </div>
                        <div class="metric-details">
                            <div class="detail-row">
                                <span>Daily Active Users</span>
                                <span>423 (33.9%)</span>
                            </div>
                            <div class="detail-row">
                                <span>Weekly Retention</span>
                                <span>67%</span>
                            </div>
                            <div class="detail-row">
                                <span>Monthly Retention</span>
                                <span>45%</span>
                            </div>
                            <div class="detail-row">
                                <span>Avg. Session Duration</span>
                                <span>4m 32s</span>
                            </div>
                        </div>
                        <div class="stat-comment">/* PLACEHOLDER: Mobile app usage analytics */</div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- TIER 1: JOURNEY ANALYTICS -->
        <div id="journeys" class="content-section">
            <div class="content-header">
                <h1 class="content-title">🎯 Journey Analytics</h1>
                <p class="content-subtitle">Track user journey distribution and revenue by path</p>
            </div>
            
            <!-- Journey Distribution -->
            <div class="dashboard-section">
                <h3 class="section-title">User Journey Distribution</h3>
                <div class="journey-stats">
                    <div class="journey-breakdown">
                        <div class="journey-item">
                            <div class="journey-icon">🏥</div>
                            <div class="journey-info">
                                <div class="journey-name">Clinical Focus</div>
                                <div class="journey-stats-row">
                                    <span class="journey-count">312 users (25%)</span>
                                    <span class="journey-revenue">$4,234 MRR</span>
                                </div>
                                <div style="font-size: 0.75rem; color: var(--muted-text); margin-top: 0.25rem;">
                                    Highest LTV: $312 avg • 89% Pro conversion
                                </div>
                            </div>
                            <div class="journey-trend positive">+8%</div>
                        </div>
                        
                        <div class="journey-item">
                            <div class="journey-icon">💪</div>
                            <div class="journey-info">
                                <div class="journey-name">Peak Performance</div>
                                <div class="journey-stats-row">
                                    <span class="journey-count">467 users (37%)</span>
                                    <span class="journey-revenue">$6,891 MRR</span>
                                </div>
                                <div style="font-size: 0.75rem; color: var(--muted-text); margin-top: 0.25rem;">
                                    Highest CalcuPlate: 73% attach • Most active users
                                </div>
                            </div>
                            <div class="journey-trend positive">+15%</div>
                        </div>
                        
                        <div class="journey-item">
                            <div class="journey-icon">✨</div>
                            <div class="journey-info">
                                <div class="journey-name">Best Life Mode</div>
                                <div class="journey-stats-row">
                                    <span class="journey-count">468 users (38%)</span>
                                    <span class="journey-revenue">$5,122 MRR</span>
                                </div>
                                <div style="font-size: 0.75rem; color: var(--muted-text); margin-top: 0.25rem;">
                                    Most growth potential • 34% Pro conversion opportunity
                                </div>
                            </div>
                            <div class="journey-trend positive">+12%</div>
                        </div>
                    </div>
                    <div class="stat-comment">/* PLACEHOLDER: Track journey selection from mobile onboarding */</div>
                </div>
                
                <!-- Journey Insights -->
                <div style="margin-top: var(--spacing-lg); padding: var(--spacing-lg); background: var(--card-bg); border: 1px solid var(--border-color); border-radius: var(--border-radius);">
                    <h4 style="color: var(--heading-color); margin-bottom: var(--spacing-md);">Journey Insights & Opportunities</h4>
                    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: var(--spacing-md);">
                        <div>
                            <strong style="color: var(--green-color);">Clinical Focus</strong>
                            <ul style="margin: var(--spacing-xs) 0; padding-left: var(--spacing-md); font-size: 0.875rem; color: var(--text-color);">
                                <li>Highest conversion and LTV</li>
                                <li>Most likely to share with providers</li>
                                <li>Premium features most valued</li>
                            </ul>
                        </div>
                        <div>
                            <strong style="color: var(--accent-color);">Peak Performance</strong>
                            <ul style="margin: var(--spacing-xs) 0; padding-left: var(--spacing-md); font-size: 0.875rem; color: var(--text-color);">
                                <li>Highest CalcuPlate adoption</li>
                                <li>Most daily active usage</li>
                                <li>Photo upload frequency leaders</li>
                            </ul>
                        </div>
                        <div>
                            <strong style="color: var(--go-color);">Best Life Mode</strong>
                            <ul style="margin: var(--spacing-xs) 0; padding-left: var(--spacing-md); font-size: 0.875rem; color: var(--text-color);">
                                <li>Largest user segment</li>
                                <li>Highest growth rate</li>
                                <li>Conversion optimization target</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- TIER 1: APP STORE METRICS -->
        <div id="appstore" class="content-section">
            <div class="content-header">
                <h1 class="content-title">📱 App Store Performance</h1>
                <p class="content-subtitle">Monitor downloads, ratings, and conversion from app stores</p>
            </div>
            
            <!-- App Store Metrics -->
            <div class="dashboard-section">
                <h3 class="section-title">Downloads & Ratings</h3>
                <div class="stats-grid">
                    <div class="stat-card">
                        <div class="stat-header">
                            <div class="stat-icon">📱</div>
                            <div class="stat-period">iOS</div>
                        </div>
                        <div class="stat-value">8,234</div>
                        <div class="stat-label">Downloads (30 days)</div>
                        <div class="stat-change positive">+15% vs last month</div>
                        <div class="stat-breakdown">
                            <small>4.8⭐ rating • 127 reviews • #23 in Health</small>
                        </div>
                        <div class="stat-comment">/* PLACEHOLDER: App Store Connect API */</div>
                    </div>
                    
                    <div class="stat-card">
                        <div class="stat-header">
                            <div class="stat-icon">🤖</div>
                            <div class="stat-period">Android</div>
                        </div>
                        <div class="stat-value">6,891</div>
                        <div class="stat-label">Downloads (30 days)</div>
                        <div class="stat-change positive">+22% vs last month</div>
                        <div class="stat-breakdown">
                            <small>4.7⭐ rating • 89 reviews • Growing fast</small>
                        </div>
                        <div class="stat-comment">/* PLACEHOLDER: Google Play Console API */</div>
                    </div>
                    
                    <div class="stat-card">
                        <div class="stat-header">
                            <div class="stat-icon">🎯</div>
                            <div class="stat-period">Conversion</div>
                        </div>
                        <div class="stat-value">4.2%</div>
                        <div class="stat-label">Download → Pro Subscription</div>
                        <div class="stat-change positive">+0.8% vs last month</div>
                        <div class="stat-comment">/* PLACEHOLDER: Track app install to first subscription */</div>
                    </div>
                    
                    <div class="stat-card warning">
                        <div class="stat-header">
                            <div class="stat-icon">⏰</div>
                            <div class="stat-period">Retention</div>
                        </div>
                        <div class="stat-value">67%</div>
                        <div class="stat-label">7-Day User Retention</div>
                        <div class="stat-change neutral">-2% vs last month</div>
                        <div class="stat-comment">/* PLACEHOLDER: Mobile app analytics - user return rate */</div>
                    </div>
                </div>
            </div>
            
            <!-- Review Monitoring -->
            <div class="dashboard-section">
                <h3 class="section-title">Review Monitoring & Response</h3>
                <div class="alert-list">
                    <div class="alert-item warning">
                        <div class="alert-icon">⚠️</div>
                        <div class="alert-content">
                            <div class="alert-title">Recent 1-Star iOS Review</div>
                            <div class="alert-desc">"App crashes when uploading photos" - 2 days ago. Needs response and technical investigation.</div>
                        </div>
                        <div class="alert-actions">
                            <button class="btn-outline-small" onclick="viewReview('ios-123')">View Review</button>
                            <button class="btn-primary-small" onclick="respondToReview('ios-123')">Respond</button>
                        </div>
                    </div>
                    
                    <div class="alert-item info">
                        <div class="alert-icon">⭐</div>
                        <div class="alert-content">
                            <div class="alert-title">5-Star Review Opportunity</div>
                            <div class="alert-desc">78 users have been active for 7+ days with high engagement. Consider in-app review prompt.</div>
                        </div>
                        <div class="alert-actions">
                            <button class="btn-primary-small" onclick="triggerReviewPrompt()">Send Review Request</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- TIER 1: CRITICAL ALERTS -->
        <div id="alerts" class="content-section">
            <div class="content-header">
                <h1 class="content-title">🚨 Critical Alerts & Monitoring</h1>
                <p class="content-subtitle">Real-time system health and business issue monitoring</p>
            </div>
            
            <!-- System Status -->
            <div class="dashboard-section">
                <h3 class="section-title">System Health</h3>
                <div class="alerts-container">
                    <div class="alert alert-success">
                        <div class="alert-icon">✅</div>
                        <div class="alert-content">
                            <div class="alert-title">All Systems Operational</div>
                            <div class="alert-description">No critical issues detected. API response times normal, payment processing stable.</div>
                        </div>
                        <div class="alert-time">Last check: 2 min ago</div>
                    </div>
                    
                    <div class="alert alert-warning">
                        <div class="alert-icon">⚠️</div>
                        <div class="alert-content">
                            <div class="alert-title">CalcuPlate API High Usage</div>
                            <div class="alert-description">API usage 40% above normal baseline. Auto-scaling engaged, monitoring costs.</div>
                        </div>
                        <div class="alert-time">47 min ago</div>
                    </div>
                    
                    <div class="alert alert-info">
                        <div class="alert-icon">💡</div>
                        <div class="alert-content">
                            <div class="alert-title">Database Performance Optimized</div>
                            <div class="alert-description">Query optimization completed. Response times improved by 23% for pattern analysis.</div>
                        </div>
                        <div class="alert-time">3 hours ago</div>
                    </div>
                </div>
                <div class="stat-comment">/* PLACEHOLDER: Connect to system monitoring, error tracking, performance metrics */</div>
            </div>
        </div>
        
        <!-- TIER 1: REVENUE BREAKDOWN -->
        <div id="revenue" class="content-section">
            <div class="content-header">
                <h1 class="content-title">📊 Revenue Breakdown & Analysis</h1>
                <p class="content-subtitle">Detailed revenue analysis and optimization opportunities</p>
            </div>
            
            <!-- Revenue Sources -->
            <div class="dashboard-section">
                <h3 class="section-title">Revenue by Source (Last 30 Days)</h3>
                <div class="revenue-breakdown">
                    <div class="revenue-item">
                        <div class="revenue-source">
                            <strong>Pro Monthly Subscriptions</strong>
                            <br><small style="color: var(--muted-text);">423 subscribers × $4.99</small>
                        </div>
                        <div style="display: flex; align-items: center; gap: var(--spacing-sm);">
                            <div class="revenue-amount">$7,243</div>
                            <div class="revenue-percentage">56.4%</div>
                        </div>
                    </div>
                    
                    <div class="revenue-item">
                        <div class="revenue-source">
                            <strong>Pro Annual Subscriptions</strong>
                            <br><small style="color: var(--muted-text);">97 subscribers × $39.99 (monthly equivalent)</small>
                        </div>
                        <div style="display: flex; align-items: center; gap: var(--spacing-sm);">
                            <div class="revenue-amount">$3,891</div>
                            <div class="revenue-percentage">30.3%</div>
                        </div>
                    </div>
                    
                    <div class="revenue-item">
                        <div class="revenue-source">
                            <strong>CalcuPlate Add-on</strong>
                            <br><small style="color: var(--muted-text);">312 users × $2.99 + annual equivalents</small>
                        </div>
                        <div style="display: flex; align-items: center; gap: var(--spacing-sm);">
                            <div class="revenue-amount">$1,713</div>
                            <div class="revenue-percentage">13.3%</div>
                        </div>
                    </div>
                    
                    <div class="stat-comment">/* PLACEHOLDER: Break down by subscription type from payment processor */</div>
                </div>
                
                <!-- Revenue Optimization -->
                <div style="margin-top: var(--spacing-lg); padding: var(--spacing-lg); background: var(--card-bg); border: 1px solid var(--border-color); border-radius: var(--border-radius);">
                    <h4 style="color: var(--heading-color); margin-bottom: var(--spacing-md);">🚀 Revenue Optimization Opportunities</h4>
                    <div class="alert-list">
                        <div class="alert-item info">
                            <div class="alert-icon">📈</div>
                            <div class="alert-content">
                                <div class="alert-title">Annual Plan Conversion</div>
                                <div class="alert-desc">156 monthly subscribers could save with annual plans. Potential +$1,847 MRR if 30% convert.</div>
                            </div>
                            <div class="alert-actions">
                                <button class="btn-primary-small" onclick="createAnnualCampaign()">Launch Campaign</button>
                            </div>
                        </div>
                        
                        <div class="alert-item info">
                            <div class="alert-icon">🍽️</div>
                            <div class="alert-content">
                                <div class="alert-title">CalcuPlate Expansion</div>
                                <div class="alert-desc">322 Pro users haven't tried CalcuPlate. 50% attach rate could add +$481 MRR.</div>
                            </div>
                            <div class="alert-actions">
                                <button class="btn-primary-small" onclick="targetCalcuPlateUsers()">Target Users</button>
                            </div>
                        </div>
                        
                        <div class="alert-item warning">
                            <div class="alert-icon">⚡</div>
                            <div class="alert-content">
                                <div class="alert-title">Free User Activation</div>
                                <div class="alert-desc">613 free users, but only 23% have uploaded photos. Better onboarding could improve conversion.</div>
                            </div>
                            <div class="alert-actions">
                                <button class="btn-outline-small" onclick="analyzeOnboarding()">Analyze Flow</button>
                                <button class="btn-primary-small" onclick="improveOnboarding()">Improve Onboarding</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>
</div>

<script>
// Navigation function
function showSection(sectionName) {
    // Remove active class from all nav items
    document.querySelectorAll('.nav-item').forEach(item => {
        item.classList.remove('active');
    });
    
    // Add active class to clicked nav item
    event.target.classList.add('active');
    
    // Hide all content sections
    document.querySelectorAll('.content-section').forEach(section => {
        section.classList.remove('active');
    });
    
    // Show selected content section
    document.getElementById(sectionName).classList.add('active');
}

// TIER 1: Business Intelligence Functions
// Financial Health Actions
function viewFailedPayments() {
    alert('🔍 PLACEHOLDER: View failed payments dashboard\n\nWill show:\n• Failed payment details\n• Retry attempts and results\n• Customer communication status\n• Recovery recommendations');
}

function fixBillingIssues() {
    alert('🛠️ PLACEHOLDER: Fix billing issues workflow\n\nWill:\n• Retry failed payments\n• Send dunning emails\n• Update payment methods\n• Apply account holds if needed');
}

function createCalcuPlateCamera() {
    alert('📧 PLACEHOLDER: CalcuPlate promotion campaign\n\nWill create:\n• Targeted email to Pro users without CalcuPlate\n• In-app promotional nudges\n• Limited-time discount offer\n• Success tracking metrics');
}

function targetAnnualUpgrade() {
    alert('🎯 PLACEHOLDER: Annual plan upgrade targeting\n\nWill identify:\n• Monthly subscribers with 6+ month tenure\n• High usage/engagement users\n• Create discount incentive campaign\n• Track upgrade conversions');
}

// App Store Actions
function viewReview(reviewId) {
    alert(`📱 PLACEHOLDER: View app store review ${reviewId}\n\nWill show:\n• Full review text and rating\n• User profile info (if available)\n• Response history\n• Technical issue correlation`);
}

function respondToReview(reviewId) {
    alert(`💬 PLACEHOLDER: Respond to review ${reviewId}\n\nWill:\n• Open response composer\n• Suggest template responses\n• Track response analytics\n• Follow up on issue resolution`);
}

function triggerReviewPrompt() {
    alert('⭐ PLACEHOLDER: Trigger in-app review requests\n\nWill:\n• Identify high-engagement users\n• Send push notification for review\n• Track review request success rate\n• Monitor rating improvements');
}

// Revenue Optimization Actions
function createAnnualCampaign() {
    alert('📈 PLACEHOLDER: Create annual plan campaign\n\nWill:\n• Target monthly subscribers\n• Calculate discount incentives\n• Create email/in-app messaging\n• Set up conversion tracking');
}

function targetCalcuPlateUsers() {
    alert('🍽️ PLACEHOLDER: Target CalcuPlate prospects\n\nWill:\n• Identify Pro users without CalcuPlate\n• Send feature demonstration\n• Offer trial period\n• Track add-on conversions');
}

function analyzeOnboarding() {
    alert('🔍 PLACEHOLDER: Analyze onboarding funnel\n\nWill show:\n• Step-by-step drop-off rates\n• Time spent per onboarding step\n• A/B test results\n• Improvement recommendations');
}

function improveOnboarding() {
    alert('⚡ PLACEHOLDER: Improve onboarding flow\n\nWill:\n• Launch A/B tests for new flow\n• Add tutorial videos\n• Simplify initial steps\n• Measure impact on activation');
}

// Load business analytics
document.addEventListener('DOMContentLoaded', function() {
    console.log('🧠 Tier 1 Business Intelligence Dashboard loaded');
    console.log('📊 Features ready: Financial health, user analytics, journey tracking, app store metrics');
    console.log('🚀 Post-launch: Connect to Stripe, App Store Connect, mobile analytics');
});
</script>

<?php include __DIR__ . '/includes/footer-admin.php'; ?>