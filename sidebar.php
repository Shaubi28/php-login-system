<?php
// sidebar.php – expects $current_page variable
$current_page = isset($current_page) ? $current_page : '';
$username = isset($_SESSION['username']) ? $_SESSION['username'] : 'Guest';
$menu_items = [
    'dashboard' => ['label' => 'Dashboard', 'icon' => 'tachometer-alt', 'url' => 'dashboard.php'],
    'profile' => ['label' => 'Profile', 'icon' => 'user', 'url' => 'profile.php'],
    'settings' => ['label' => 'Settings', 'icon' => 'cog', 'url' => 'settings.php'],
    'analytics' => ['label' => 'Analytics', 'icon' => 'chart-bar', 'url' => 'analytics.php'],
    'messages' => ['label' => 'Messages', 'icon' => 'envelope', 'url' => 'messages.php'],
    'calendar' => ['label' => 'Calendar', 'icon' => 'calendar', 'url' => 'calendar.php'],
];
?>
<style>
.sidebar {
    width: 260px;
    background: linear-gradient(180deg,#2c3e50,#1a252f);
    color: #fff;
    display: flex;
    flex-direction: column;
    transition: .3s;
    min-height: 100vh;
}
.sidebar-header {
    padding: 20px;
    text-align: center;
    border-bottom: 1px solid rgba(184, 19, 148, 0.1);
}
.sidebar-header h2 {font-size: 1.8rem;}
.sidebar-header p {font-size: .9rem; color:#b0c4ce;}
.sidebar-menu {flex: 1; padding: 20px 0;}
.sidebar-menu ul {list-style: none; margin: 0; padding: 0;}
.sidebar-menu a {display: flex; align-items: center; padding: 12px 20px; color: #ecf0f1; text-decoration:none; transition:.3s; border-left: 4px solid transparent;}
.sidebar-menu a i {width: 30px; margin-right: 10px;}
.sidebar-menu a:hover {background: rgba(175, 60, 94, 0.1); border-left-color: #3498db;}
.sidebar-menu a.active {background: rgba(23, 141, 177, 0.2); border-left-color: #3498db; font-weight: 500;}
.sidebar-footer {padding: 20px; border-top: 1px solid rgba(80, 15, 55, 0.1);}
.user-info {display:flex; align-items:center; margin-bottom:15px;}
.user-avatar {width:40px; height:40px; border-radius:50%; background:#3498db; display:flex; align-items:center; justify-content:center; margin-right:10px; font-weight:bold;}
.user-details .user-name {font-weight:600;}
.user-role {font-size:.8rem; color:#b0c4ce;}
.logout-btn {display:block; width:100%; padding:10px; background:rgba(231,76,60,.2); border:1px solid rgba(231,76,60,.5); border-radius:5px; text-align:center; color:#fff; text-decoration:none;}
.logout-btn:hover {background:#e74c3c;}
@media(max-width:768px){
    .sidebar{position:fixed; bottom:0; width:100%; display:none;}
    .sidebar.active{display:flex;}
}
</style>
<div class="sidebar" id="sidebar">
    <div class="sidebar-header">
        <h2>MyApp</h2>
        <p>Dashboard</p>
    </div>

    <div class="sidebar-menu">
        <ul>
            <?php foreach ($menu_items as $key => $item): ?>
                <li>
                    <a href="<?php echo $item['url']; ?>" class="<?php echo ($current_page === $key) ? 'active' : ''; ?>">
                        <i class="fas fa-<?php echo $item['icon']; ?>"></i>
                        <span><?php echo $item['label']; ?></span>
                    </a>
                </li>
            <?php endforeach; ?>
        </ul>
    </div>

    <div class="sidebar-footer">
        <div class="user-info">
            <div class="user-avatar">
                <?php echo strtoupper(substr($username, 0, 1)); ?>
            </div>
            <div class="user-details">
                <div class="user-name"><?php echo htmlspecialchars($username); ?></div>
                <div class="user-role">Administrator</div>
            </div>
        </div>
        <a href="logout.php" class="logout-btn">
            <i class="fas fa-sign-out-alt"></i> Logout
        </a>
    </div>
</div>