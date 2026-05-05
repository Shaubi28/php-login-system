<?php
session_start();
require_once 'config.php';
require_once 'load_settings.php';
$current_page = basename($_SERVER['PHP_SELF'], '.php');
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header("Location: index.php");
    exit();
}

$username = $_SESSION['username'];
$user_id = $_SESSION['user_id'];

// Get real statistics
$total_users = $conn->query("SELECT COUNT(*) as count FROM users")->fetch_assoc()['count'];
$total_messages = $conn->query("SELECT COUNT(*) as count FROM messages")->fetch_assoc()['count'];
$user_messages = $conn->query("SELECT COUNT(*) as count FROM messages WHERE sender_id = $user_id")->fetch_assoc()['count'];
$recent_events = $conn->query("SELECT COUNT(*) as count FROM analytics_events WHERE created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)")->fetch_assoc()['count'];

// Get recent messages for activity feed
$recent_messages = $conn->query("
    SELECT m.message, m.created_at, u.username as sender_name 
    FROM messages m 
    JOIN users u ON m.sender_id = u.id 
    WHERE m.receiver_id = $user_id OR m.sender_id = $user_id 
    ORDER BY m.created_at DESC 
    LIMIT 5
");
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Sidebar Menu</title>
    <!-- Font Awesome for icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        body {
            background-color: #e2ebee;
            display: flex;
            height: 100vh;
            overflow: hidden;
        }

        /* ===== SIDEBAR STYLES ===== */
        .sidebar {
            width: 260px;
            background: linear-gradient(180deg, #042d5f 0%, #82b1dd 100%);
            color: #fff;
            display: flex;
            flex-direction: column;
            transition: all 0.3s ease;
            box-shadow: 2px 0 10px rgba(103, 190, 164, 0.1);
            overflow-y: auto;
        }

        .sidebar-header {
            padding: 20px;
            text-align: center;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        }

        .sidebar-header h2 {
            font-size: 1.8rem;
            margin-bottom: 5px;
            color: #e8ebdf;
        }

        .sidebar-header p {
            color: #b0c4ce;
            font-size: 0.9rem;
        }

        .sidebar-menu {
            flex: 1;
            padding: 20px 0;
        }

        .sidebar-menu ul {
            list-style: none;
        }

        .sidebar-menu li {
            margin-bottom: 5px;
        }

        .sidebar-menu a {
            display: flex;
            align-items: center;
            padding: 12px 20px;
            color: #ecf0f1;
            text-decoration: none;
            font-size: 1rem;
            transition: all 0.3s;
            border-left: 4px solid transparent;
        }

        .sidebar-menu a i {
            width: 30px;
            font-size: 1.1rem;
            margin-right: 10px;
        }

        .sidebar-menu a:hover {
            background: rgba(255, 255, 255, 0.1);
            border-left-color: #3498db;
        }

        .sidebar-menu a.active {
            background: rgba(52, 152, 219, 0.2);
            border-left-color: #3498db;
            font-weight: 500;
        }

        .sidebar-footer {
            padding: 20px;
            border-top: 1px solid rgba(255, 255, 255, 0.1);
        }

        .user-info {
            display: flex;
            align-items: center;
            margin-bottom: 15px;
        }

        .user-avatar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: #3498db;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-right: 10px;
            font-weight: bold;
            text-transform: uppercase;
        }

        .user-details {
            flex: 1;
        }

        .user-name {
            font-weight: 600;
            font-size: 0.95rem;
        }

        .user-role {
            color: #b0c4ce;
            font-size: 0.8rem;
        }

        .logout-btn {
            display: block;
            width: 100%;
            padding: 10px;
            background: rgba(231, 76, 60, 0.2);
            color: #fff;
            border: 1px solid rgba(231, 76, 60, 0.5);
            border-radius: 5px;
            text-align: center;
            text-decoration: none;
            font-weight: 500;
            transition: all 0.3s;
        }

        .logout-btn:hover {
            background: #e74c3c;
            border-color: #e74c3c;
        }

        /* ===== MAIN CONTENT STYLES ===== */
        .main-content {
            flex: 1;
            overflow-y: auto;
            padding: 20px;
            background: #f4f7fa;
        }

        .top-bar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            background: white;
            padding: 15px 25px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
            margin-bottom: 25px;
        }

        .page-title {
            font-size: 1.5rem;
            color: #2c3e50;
            font-weight: 600;
        }

        .mobile-toggle {
            display: none;
            background: none;
            border: none;
            font-size: 1.5rem;
            color: #2c3e50;
            cursor: pointer;
        }

        /* Cards */
        .welcome-card {
            background: white;
            padding: 25px;
            border-radius: 10px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.05);
            margin-bottom: 25px;
        }

        .welcome-card h1 {
            color: #2c3e50;
            margin-bottom: 10px;
            font-size: 1.8rem;
        }

        .welcome-card p {
            color: #666;
            line-height: 1.6;
        }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
            gap: 25px;
            margin-top: 25px;
        }

        .stat-card {
            background: white;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.05);
            display: flex;
            align-items: center;
            transition: transform 0.3s;
        }

        .stat-card:hover {
            transform: translateY(-5px);
        }

        .stat-icon {
            width: 60px;
            height: 60px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-right: 15px;
            color: white;
            font-size: 1.8rem;
        }

        .stat-content {
            flex: 1;
        }

        .stat-content h3 {
            color: #666;
            font-size: 0.9rem;
            font-weight: 500;
            margin-bottom: 5px;
        }

        .stat-number {
            font-size: 1.8rem;
            font-weight: 700;
            color: #2c3e50;
            margin-bottom: 5px;
        }

        .stat-label {
            color: #999;
            font-size: 0.8rem;
        }

        /* Responsive */
        @media (max-width: 768px) {
            body {
                flex-direction: column;
                overflow: auto;
            }

            .sidebar {
                width: 100%;
                height: auto;
                position: fixed;
                bottom: 0;
                left: 0;
                z-index: 1000;
                display: none;
            }

            .sidebar.active {
                display: flex;
            }

            .main-content {
                margin-bottom: 60px;
            }

            .mobile-toggle {
                display: block;
            }

            .sidebar-header,
            .sidebar-footer {
                padding: 15px;
            }
        }

        /* Activity Section Styles */
        .activity-section {
            background: white;
            padding: 25px;
            border-radius: 10px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.05);
            margin-top: 25px;
        }

        .activity-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }

        .activity-header h2 {
            color: #2c3e50;
            font-size: 1.4rem;
            font-weight: 600;
        }

        .view-all-link {
            color: #3498db;
            text-decoration: none;
            font-size: 0.9rem;
            font-weight: 500;
        }

        .view-all-link:hover {
            text-decoration: underline;
        }

        .activity-list {
            display: flex;
            flex-direction: column;
            gap: 15px;
        }

        .activity-item {
            display: flex;
            align-items: center;
            padding: 15px;
            background: #f8f9fa;
            border-radius: 8px;
            transition: background 0.3s;
        }

        .activity-item:hover {
            background: #e9ecef;
        }

        .activity-icon {
            width: 40px;
            height: 40px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            margin-right: 15px;
            flex-shrink: 0;
        }

        .activity-content {
            flex: 1;
        }

        .activity-text {
            color: #2c3e50;
            font-size: 0.95rem;
            margin-bottom: 5px;
        }

        .activity-time {
            color: #666;
            font-size: 0.8rem;
        }

        .no-activity {
            text-align: center;
            padding: 40px 20px;
            color: #666;
        }

        .no-activity i {
            font-size: 3rem;
            margin-bottom: 15px;
            opacity: 0.5;
        }

        /* Quick Actions Styles */
        .quick-actions {
            background: white;
            padding: 25px;
            border-radius: 10px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.05);
            margin-top: 25px;
        }

        .quick-actions h2 {
            color: #2c3e50;
            font-size: 1.4rem;
            font-weight: 600;
            margin-bottom: 20px;
        }

        .actions-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
        }

        .action-card {
            display: flex;
            align-items: center;
            padding: 20px;
            background: #f8f9fa;
            border-radius: 8px;
            text-decoration: none;
            color: inherit;
            transition: all 0.3s;
            border: 1px solid transparent;
        }

        .action-card:hover {
            background: white;
            border-color: #3498db;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
            transform: translateY(-2px);
        }

        .action-icon {
            width: 50px;
            height: 50px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            margin-right: 15px;
            flex-shrink: 0;
        }

        .action-content h3 {
            color: #2c3e50;
            font-size: 1rem;
            font-weight: 600;
            margin-bottom: 5px;
        }

        .action-content p {
            color: #666;
            font-size: 0.85rem;
            margin: 0;
        }
        /* ================= DARK MODE ================= */

body.dark-mode {
    background: #0f172a;
    color: #e2e8f0;
}

/* MAIN CONTENT */
body.dark-mode .main-content {
    background: #0f172a;
}

/* TOP BAR */
body.dark-mode .top-bar {
    background: #1e293b;
    color: #e2e8f0;
}

/* CARDS */
body.dark-mode .content-card {
    background: #1e293b;
    color: #e2e8f0;
}

/* TABLE */
body.dark-mode table {
    color: #e2e8f0;
}

body.dark-mode th {
    background: #334155;
}

body.dark-mode td {
    border-bottom: 1px solid #334155;
}

/* INPUTS */
body.dark-mode input,
body.dark-mode textarea {
    background: #0f172a;
    color: #f1f5f9;
    border: 1px solid #475569;
}

/* LABEL */
body.dark-mode label {
    color: #cbd5f5;
}

/* BUTTON */
body.dark-mode .btn {
    background: #2563eb;
}

/* QUICK ACTIONS */
body.dark-mode .quick-actions {
    background: #1e293b;
}

body.dark-mode .action-card {
    background: #0f172a;
    border: 1px solid #334155;
}

body.dark-mode .action-card:hover {
    background: #1e293b;
}
    </style>
</head>

<body class="<?= ($dark_mode === 'on') ? 'dark-mode' : '' ?>">
    <!-- SIDEBAR -->
    <?php include 'sidebar.php'; ?>
    <div class="main-content">
        <div class="top-bar">
            <button class="mobile-toggle" id="menuToggle">
                <i class="fas fa-bars"></i>
            </button>
            <div class="page-title">Dashboard</div>
            <div style="width: 30px;"></div> <!-- Spacer for alignment -->
        </div>

        <div class="welcome-card">
            <h1>Welcome back, <?php echo htmlspecialchars($username); ?>! 👋</h1>
            <p>You have successfully logged in to the system. This is your personal dashboard where you can manage your
                account and access various features.</p>
        </div>

        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-icon">
                    <i class="fas fa-users"></i>
                </div>
                <div class="stat-content">
                    <h3>Total Users</h3>
                    <div class="stat-number"><?php echo number_format($total_users); ?></div>
                    <div class="stat-label">Registered users</div>
                </div>
            </div>

            <div class="stat-card">
                <div class="stat-icon">
                    <i class="fas fa-envelope"></i>
                </div>
                <div class="stat-content">
                    <h3>Total Messages</h3>
                    <div class="stat-number"><?php echo number_format($total_messages); ?></div>
                    <div class="stat-label">Across all users</div>
                </div>
            </div>

            <div class="stat-card">
                <div class="stat-icon">
                    <i class="fas fa-clock"></i>
                </div>
                <div class="stat-content">
                    <h3>Account Status</h3>
                    <div class="stat-number" style="color: #27ae60;">Active</div>
                    <div class="stat-label">Member since <?php echo date('M Y', strtotime($_SESSION['created_at'] ?? '2024-01-01')); ?></div>
                </div>
            </div>

            <div class="stat-card">
                <div class="stat-icon">
                    <i class="fas fa-paper-plane"></i>
                </div>
                <div class="stat-content">
                    <h3>Your Messages</h3>
                    <div class="stat-number"><?php echo number_format($user_messages); ?></div>
                    <div class="stat-label">Messages sent</div>
                </div>
            </div>
        </div>

        <!-- Recent Activity Section -->
        <div class="activity-section">
            <div class="activity-header">
                <h2>Recent Activity</h2>
                <a href="messages.php" class="view-all-link">View All Messages</a>
            </div>
            <div class="activity-list">
                <?php if ($recent_messages->num_rows > 0): ?>
                    <?php while($msg = $recent_messages->fetch_assoc()): ?>
                        <div class="activity-item">
                            <div class="activity-icon">
                                <i class="fas fa-envelope"></i>
                            </div>
                            <div class="activity-content">
                                <div class="activity-text">
                                    <strong><?php echo htmlspecialchars($msg['sender_name']); ?></strong> sent a message
                                </div>
                                <div class="activity-time">
                                    <?php echo date('M j, g:i A', strtotime($msg['created_at'])); ?>
                                </div>
                            </div>
                        </div>
                    <?php endwhile; ?>
                <?php else: ?>
                    <div class="no-activity">
                        <i class="fas fa-inbox"></i>
                        <p>No recent messages</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>

      
        <div class="quick-actions">
            <h2>Quick Actions</h2>
            <div class="actions-grid">
                <a href="messages.php" class="action-card">
                    <div class="action-icon">
                        <i class="fas fa-envelope"></i>
                    </div>
                    <div class="action-content">
                        <h3>Send Message</h3>
                        <p>Start a conversation</p>
                    </div>
                </a>
                <a href="profile.php" class="action-card">
                    <div class="action-icon">
                        <i class="fas fa-user"></i>
                    </div>
                    <div class="action-content">
                        <h3>View Profile</h3>
                        <p>Manage your account</p>
                    </div>
                </a>
                <a href="settings.php" class="action-card">
                    <div class="action-icon">
                        <i class="fas fa-cog"></i>
                    </div>
                    <div class="action-content">
                        <h3>Settings</h3>
                        <p>Customize preferences</p>
                    </div>
                </a>
                <a href="analytics.php" class="action-card">
                    <div class="action-icon">
                        <i class="fas fa-chart-bar"></i>
                    </div>
                    <div class="action-content">
                        <h3>Analytics</h3>
                        <p>View system stats</p>
                    </div>
                </a>
            </div>
        </div>
    </div>

    <script>

        // Mobile sidebar toggle
        const menuToggle = document.getElementById('menuToggle');
        const sidebar = document.getElementById('sidebar');

        menuToggle.addEventListener('click', function () {
            sidebar.classList.toggle('active');
        });

        // Close sidebar when clicking outside on mobile (optional)
        document.addEventListener('click', function (event) {
            if (window.innerWidth <= 768) {
                if (!sidebar.contains(event.target) && !menuToggle.contains(event.target)) {
                    sidebar.classList.remove('active');
                }
            }
        });
    </script>
</body>

</html>
