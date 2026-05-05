<?php
require_once 'config.php';
require_once 'load_settings.php';
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header("Location: index.php?error=Please login first");
    exit();
}
$current_page = 'analytics';
$message = $error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['add'])) {
        $name = trim($_POST['event_name']);
        $data = trim($_POST['event_data']);
        if (!empty($name)) {
            $stmt = $conn->prepare("INSERT INTO analytics_events (event_name, event_data) VALUES (?, ?)");
            $stmt->bind_param("ss", $name, $data);
            $stmt->execute() ? $message = "Event logged." : $error = "Error.";
            $stmt->close();
        } else
            $error = "Event name required.";
    } elseif (isset($_POST['delete'])) {
        $id = intval($_POST['id']);
        $stmt = $conn->prepare("DELETE FROM analytics_events WHERE id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute() ? $message = "Deleted." : $error = "Error.";
        $stmt->close();
    }
}
$events = $conn->query("SELECT * FROM analytics_events ORDER BY created_at DESC");
?>
<!DOCTYPE html>
<html>

<head>
    <title>Analytics</title>
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
            margin-bottom: 25px;
        }

        .page-title {
            font-size: 1.5rem;
            color: #2c3e50;
        }

        .content-card {
            background: white;
            padding: 25px;
            border-radius: 10px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.05);
            margin-bottom: 25px;
        }

        .form-group {
            margin-bottom: 1rem;
        }

        label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: 600;
            color: #555;
        }

        input[type="text"],
        textarea {
            width: 100%;
            padding: 0.75rem;
            border: 1px solid #ddd;
            border-radius: 5px;
        }

        .btn {
            background: #3498db;
            color: white;
            border: none;
            padding: 0.5rem 1rem;
            border-radius: 5px;
            cursor: pointer;
            margin-right: 0.5rem;
        }

        .btn-danger {
            background: #e74c3c;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th,
        td {
            padding: 0.75rem;
            text-align: left;
            border-bottom: 1px solid #eee;
        }

        th {
            background: #f8f9fa;
        }

        .message {
            padding: 0.75rem;
            border-radius: 5px;
            margin-bottom: 1rem;
        }

        .success {
            background: #d4edda;
            color: #155724;
        }

        .error {
            background: #f8d7da;
            color: #721c24;
        }

        .mobile-toggle {
            display: none;
            background: none;
            border: none;
            font-size: 1.5rem;
            cursor: pointer;
        }

        @media (max-width:768px) {
            .sidebar {
                position: fixed;
                left: -260px;
                top: 0;
                bottom: 75px;
                z-index: 1000;
                transition: left 0.3s;
            }

            .sidebar.active {
                left: 0;
            }

            .mobile-toggle {
                display: block;
            }
        
        }
 .quick-actions {
            background: white;
            padding: center     ;
            border-radius: 5px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.05);
            margin-top: 10px;
        }

        .quick-actions h2 {
            color: #2c3e50;
            font-size: 1.4rem;
            font-weight: 600;
            margin-bottom: 20px;
        }

        .actions-grid {
           grid-auto-columns: 50px
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
    <?php include 'sidebar.php'; ?>
    <div class="main-content">
        <div class="top-bar">
            <button class="mobile-toggle" id="menuToggle"><i class="fas fa-bars"></i></button>
            <div class="page-title">Analytics</div>
        </div>
        <?php if ($message)
            echo "<div class='message success'>$message</div>"; ?>
        <?php if ($error)
            echo "<div class='message error'>$error</div>"; ?>
        <div class="content-card">
            <h3>Log Event</h3>
            <form method="POST">
                <input type="text" name="event_name" placeholder="Event name" required>
                <textarea name="event_data" placeholder="Event data (optional)"></textarea>
                <button type="submit" name="add" class="btn">Log</button>
            </form>
        </div>


        <div class="content-card">
            <table>
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Data</th>
                        <th>Time</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($e = $events->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($e['event_name']); ?></td>
                            <td><?php echo htmlspecialchars($e['event_data']); ?></td>
                            <td><?php echo $e['created_at']; ?></td>
                            <td>
                                <form method="POST" onsubmit="return confirm('Delete?');">
                                    <input type="hidden" name="id" value="<?php echo $e['id']; ?>">
                                    <button type="submit" name="delete" class="btn btn-danger">Delete</button>
                                </form>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
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
        document.getElementById('menuToggle').onclick = () => document.getElementById('sidebar').classList.toggle('active');
    </script>
</body>

</html>