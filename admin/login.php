<?php include __DIR__ . '/includes/header-admin.php'; ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>QuietGo Admin Login</title>
    <link rel="stylesheet" href="/admin/css/admin.css">
</head>
<body>
    <div class="login-container">
        <div class="login-card">
            <h1>QuietGo Admin</h1>
            <form id="loginForm">
                <input type="text" id="username" placeholder="Username" required>
                <input type="password" id="password" placeholder="Password" required>
                <button type="submit">Login</button>
            </form>
        </div>
    </div>
    
    <script>
        document.getElementById('loginForm').addEventListener('submit', function(e) {
            e.preventDefault();
            const username = document.getElementById('username').value;
            const password = document.getElementById('password').value;
            
            if ((username === 'spiderghost' && password === 'TempAdmin2024') || 
                (username === 'admin' && password === 'admin123')) {
                localStorage.setItem('admin_logged_in', 'true');
                window.location.href = '/admin/dashboard.php';
            } else {
                alert('Invalid credentials');
            }
        });
    </script>
</body>
</html>
<?php include __DIR__ . '/includes/footer-admin.php'; ?>