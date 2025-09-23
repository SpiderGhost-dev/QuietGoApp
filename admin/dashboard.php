<?php 
require_once __DIR__ . '/includes/admin-auth.php';
$adminUser = require_admin_login();
include __DIR__ . '/includes/header-admin.php'; 
?>
<div class="admin-layout">
    <!-- Sidebar Navigation -->
    <aside class="admin-sidebar">
        <div class="sidebar-section">
            <div class="sidebar-title">Overview</div>
            <a href="#analytics" class="nav-item active" onclick="showSection('analytics')">
                <span class="nav-item-icon">📊</span>
                Analytics Dashboard
            </a>
            <a href="#users" class="nav-item" onclick="showSection('users')">
                <span class="nav-item-icon">👥</span>
                User Management
            </a>
        </div>
        
        <div class="sidebar-section">
            <div class="sidebar-title">Content</div>
            <a href="#content" class="nav-item" onclick="showSection('content')">
                <span class="nav-item-icon">📝</span>
                Content Management
            </a>
        </div>
        
        <div class="sidebar-section">
            <div class="sidebar-title">System</div>
            <a href="#settings" class="nav-item" onclick="showSection('settings')">
                <span class="nav-item-icon">⚙️</span>
                System Settings
            </a>
        </div>
    </aside>
    
    <!-- Main Content -->
    <main class="admin-content">
        <!-- Analytics Dashboard -->
        <div id="analytics" class="content-section active">
            <div class="content-header">
                <h1 class="content-title">Analytics Dashboard</h1>
                <p class="content-subtitle">Monitor your <span class="quietgo-brand"><span class="quiet">Quiet</span><span class="go">Go</span></span> platform performance and user engagement</p>
            </div>
            
            <div class="stats-grid">
                <div class="stat-card featured">
                    <div class="stat-header">
                        <div class="stat-icon">👥</div>
                    </div>
                    <div class="stat-value">247</div>
                    <div class="stat-label">Total Users</div>
                    <div class="stat-change positive">+12% this month</div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-header">
                        <div class="stat-icon">📈</div>
                    </div>
                    <div class="stat-value">23</div>
                    <div class="stat-label">New Users (30 days)</div>
                    <div class="stat-change positive">+8% vs last month</div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-header">
                        <div class="stat-icon">📋</div>
                    </div>
                    <div class="stat-value">1,543</div>
                    <div class="stat-label">Total Health Logs</div>
                    <div class="stat-change positive">+25% this month</div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-header">
                        <div class="stat-icon">📁</div>
                    </div>
                    <div class="stat-value">89</div>
                    <div class="stat-label">File Uploads</div>
                    <div class="stat-change positive">+5% this week</div>
                </div>
            </div>
            
            <div class="data-section">
                <div class="section-header">
                    <h3 class="section-title">Recent User Activity</h3>
                </div>
                <div class="loading-state">
                    Real-time analytics data will be displayed here when connected to your backend systems.
                </div>
            </div>
        </div>
        
        <!-- User Management -->
        <div id="users" class="content-section">
            <div class="content-header">
                <h1 class="content-title">User Management</h1>
                <p class="content-subtitle">Manage <span class="quietgo-brand"><span class="quiet">Quiet</span><span class="go">Go</span></span> user accounts and subscriptions</p>
            </div>
            
            <div class="data-section">
                <div class="section-header">
                    <h3 class="section-title">All Users</h3>
                </div>
                <div class="section-content">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>Email</th>
                                <th>Name</th>
                                <th>Subscription</th>
                                <th>Meal AI</th>
                                <th>Created</th>
                                <th>Last Active</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td>user1@example.com</td>
                                <td>John Doe</td>
                                <td><span class="status-badge status-pro">Pro</span></td>
                                <td>✅</td>
                                <td>Jan 15, 2024</td>
                                <td>2 hours ago</td>
                            </tr>
                            <tr>
                                <td>user2@example.com</td>
                                <td>Jane Smith</td>
                                <td><span class="status-badge status-free">Free</span></td>
                                <td>❌</td>
                                <td>Feb 20, 2024</td>
                                <td>1 day ago</td>
                            </tr>
                            <tr>
                                <td>user3@example.com</td>
                                <td>Mike Johnson</td>
                                <td><span class="status-badge status-pro">Pro</span></td>
                                <td>✅</td>
                                <td>Mar 10, 2024</td>
                                <td>5 minutes ago</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        
        <!-- Content Management -->
        <div id="content" class="content-section">
            <div class="content-header">
                <h1 class="content-title">Content Management</h1>
                <p class="content-subtitle">Manage <span class="quietgo-brand"><span class="quiet">Quiet</span><span class="go">Go</span></span> website content and configuration</p>
            </div>
            
            <div class="data-section">
                <div class="section-header">
                    <h3 class="section-title">Site Configuration</h3>
                </div>
                <div class="management-grid">
                    <button class="management-btn" onclick="manageHomepage()">
                        <span class="management-btn-icon">🏠</span>
                        Homepage Content
                    </button>
                    <button class="management-btn" onclick="manageFeatures()">
                        <span class="management-btn-icon">✨</span>
                        Features Section
                    </button>
                    <button class="management-btn" onclick="manageTestimonials()">
                        <span class="management-btn-icon">💬</span>
                        Testimonials
                    </button>
                    <button class="management-btn" onclick="manageFAQ()">
                        <span class="management-btn-icon">❓</span>
                        FAQ Section
                    </button>
                </div>
            </div>
        </div>
        
        <!-- System Settings -->
        <div id="settings" class="content-section">
            <div class="content-header">
                <h1 class="content-title">System Settings</h1>
                <p class="content-subtitle">Configure <span class="quietgo-brand"><span class="quiet">Quiet</span><span class="go">Go</span></span> system settings and administration</p>
            </div>
            
            <div class="data-section">
                <div class="section-header">
                    <h3 class="section-title">Admin Accounts</h3>
                </div>
                <div class="section-content">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>Username</th>
                                <th>Email</th>
                                <th>Name</th>
                                <th>Status</th>
                                <th>Last Login</th>
                                <th>Created</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td>david</td>
                                <td>david@quietgo.app</td>
                                <td>David M. Baker</td>
                                <td><span class="status-badge status-active">Active</span></td>
                                <td>Now</td>
                                <td>Jan 1, 2024</td>
                            </tr>
                            <tr>
                                <td>savannah</td>
                                <td>savannah@quietgo.app</td>
                                <td>Savannah J. Baker</td>
                                <td><span class="status-badge status-active">Active</span></td>
                                <td>--</td>
                                <td>Jan 1, 2024</td>
                            </tr>
                            <tr>
                                <td>stone</td>
                                <td>stone@quietgo.app</td>
                                <td>O. Stone Baker</td>
                                <td><span class="status-badge status-active">Active</span></td>
                                <td>--</td>
                                <td>Jan 1, 2024</td>
                            </tr>
                            <tr>
                                <td>admin</td>
                                <td>admin@quietgo.app</td>
                                <td>System Admin</td>
                                <td><span class="status-badge status-active">Active</span></td>
                                <td>--</td>
                                <td>Jan 1, 2024</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
            
            <div class="data-section">
                <div class="section-header">
                    <h3 class="section-title">System Configuration</h3>
                </div>
                <div class="management-grid">
                    <button class="management-btn" onclick="manageDatabase()">
                        <span class="management-btn-icon">🗄️</span>
                        Database Status
                    </button>
                    <button class="management-btn" onclick="manageBackups()">
                        <span class="management-btn-icon">💾</span>
                        Backup System
                    </button>
                    <button class="management-btn" onclick="manageLogs()">
                        <span class="management-btn-icon">📋</span>
                        System Logs
                    </button>
                    <button class="management-btn" onclick="manageAPI()">
                        <span class="management-btn-icon">🔌</span>
                        API Settings
                    </button>
                </div>
            </div>
        </div>
    </main>
</div>

<script>
    // Check authentication on page load
    document.addEventListener('DOMContentLoaded', function() {
        const isLoggedIn = localStorage.getItem('admin_logged_in');
        if (isLoggedIn !== 'true') {
            window.location.href = '/admin/login.php';
            return;
        }
    });

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

    // Content Management Functions
    function manageHomepage() {
        alert('Homepage content management will open here. This would connect to your content management system.');
    }

    function manageFeatures() {
        alert('Features section management will open here. Edit feature descriptions and benefits.');
    }

    function manageTestimonials() {
        alert('Testimonials management will open here. Add, edit, or remove user testimonials.');
    }

    function manageFAQ() {
        alert('FAQ management will open here. Manage frequently asked questions and answers.');
    }

    // System Settings Functions
    function manageDatabase() {
        alert('Database management will open here. Monitor database health and performance.');
    }

    function manageBackups() {
        alert('Backup system will open here. Schedule and manage data backups.');
    }

    function manageLogs() {
        alert('System logs will open here. View application logs and error reports.');
    }

    function manageAPI() {
        alert('API settings will open here. Manage API keys and integration settings.');
    }
</script>
<?php include __DIR__ . '/includes/footer-admin.php'; ?>