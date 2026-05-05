<?php
session_start();
require_once 'config.php';

// LOGIN CHECK
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header("Location: index.php");
    exit();
}

$current_page = 'settings';
$user_id = $_SESSION['user_id'];

// CREATE TABLE
$conn->query("CREATE TABLE IF NOT EXISTS settings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    setting_key VARCHAR(50) UNIQUE NOT NULL,
    setting_value TEXT,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
)");

// DEFAULTS
$userSettings = [
    'email' => '',
    'phone' => '',
    'dark_mode' => 'off'
];

// KEYS
$emailKey = $user_id.'_email';
$phoneKey = $user_id.'_phone';
$darkKey  = $user_id.'_dark_mode';

// LOAD
$stmt = $conn->prepare("SELECT setting_key, setting_value FROM settings WHERE setting_key IN (?, ?, ?)");
$stmt->bind_param("sss", $emailKey, $phoneKey, $darkKey);
$stmt->execute();
$res = $stmt->get_result();

while($row = $res->fetch_assoc()){
    if(strpos($row['setting_key'], '_email') !== false) $userSettings['email'] = $row['setting_value'];
    if(strpos($row['setting_key'], '_phone') !== false) $userSettings['phone'] = $row['setting_value'];
    if(strpos($row['setting_key'], '_dark_mode') !== false) $userSettings['dark_mode'] = $row['setting_value'];
}

// SAVE
$msg = '';
if(isset($_POST['save_settings'])){
    $email = filter_var(trim($_POST['email']), FILTER_VALIDATE_EMAIL) ? trim($_POST['email']) : '';
    $phone = preg_replace('/[^0-9]/', '', $_POST['phone']);
    $dark  = isset($_POST['dark_mode']) ? 'on' : 'off';

    $stmt = $conn->prepare("
        INSERT INTO settings (setting_key, setting_value)
        VALUES (?, ?)
        ON DUPLICATE KEY UPDATE setting_value=VALUES(setting_value)
    ");

    foreach ([[$emailKey,$email],[$phoneKey,$phone],[$darkKey,$dark]] as $set){
        $stmt->bind_param("ss",$set[0],$set[1]);
        $stmt->execute();
    }

    $userSettings = ['email'=>$email,'phone'=>$phone,'dark_mode'=>$dark];
    $msg = "Settings saved!";
}
?>

<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Settings</title>

<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

<style>
*{margin:0;padding:0;box-sizing:border-box;font-family:Segoe UI;}
body{display:flex;height:100vh;background:#e2ebee;transition:0.3s}
  
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

body{display:flex;min-height:100vh;background:#e2ebee;}
/* MAIN */
.main-content{flex:1;padding:20px;overflow:auto}
.top-bar{background:#fff;padding:15px;border-radius:10px;margin-bottom:20px}

/* CARD */
.card{
    background:#fff;
    padding:25px;
    border-radius:12px;
    max-width:600px;
    box-shadow:0 5px 15px rgba(0,0,0,0.05);
}

/* FORM */
label{display:block;margin-top:15px;margin-bottom:5px}
input{
    width:100%;
    padding:10px;
    border-radius:6px;
    border:1px solid #ccc;
}

/* BUTTON */
button{
    margin-top:15px;
    padding:10px 15px;
    background:#3b82f6;
    color:#fff;
    border:none;
    border-radius:6px;
    cursor:pointer;
}

/* TOGGLE SWITCH */
.switch{
    position:relative;
    display:inline-block;
    width:50px;
    height:25px;
}
.switch input{display:none}
.slider{
    position:absolute;
    cursor:pointer;
    background:#ccc;
    border-radius:25px;
    top:0;left:0;right:0;bottom:0;
    transition:.3s;
}
.slider:before{
    content:"";
    position:absolute;
    height:18px;width:18px;
    left:4px;bottom:3.5px;
    background:white;
    border-radius:50%;
    transition:.3s;
}
input:checked + .slider{
    background:#3b82f6;
}
input:checked + .slider:before{
    transform:translateX(24px);
}

/* DARK MODE */
body.dark-mode{background:#0f172a;color:#e2e8f0}

body.dark-mode .card{background:#1e293b}
body.dark-mode .top-bar{background:#1e293b}

body.dark-mode input{
    background:#0f172a;
    color:#f1f5f9;
    border:1px solid #475569;
}

body.dark-mode label{color:#cbd5f5}

body.dark-mode button{background:#2563eb}

</style>
</head>

<body class="<?= ($userSettings['dark_mode']==='on')?'dark-mode':'' ?>">

<?php include 'sidebar.php'; ?>

<div class="main-content">

<div class="top-bar">
    <h2>Settings</h2>
</div>

<div class="card">

<?php if($msg): ?>
<p style="color:lime;"><?= $msg ?></p>
<?php endif; ?>

<form method="POST">

<label>Email</label>
<input type="email" name="email" value="<?= htmlspecialchars($userSettings['email']) ?>">

<label>Phone</label>
<input type="text" name="phone" value="<?= htmlspecialchars($userSettings['phone']) ?>">

<label style="margin-top:20px;">Dark Mode</label>
<label class="switch">
    <input type="checkbox" id="dark_mode" name="dark_mode"
    <?= ($userSettings['dark_mode']==='on')?'checked':'' ?>>
    <span class="slider"></span>
</label>

<br>
<button type="submit" name="save_settings">Save</button>

</form>
</div>
</div>

<script>
const toggle = document.getElementById('dark_mode');

toggle.addEventListener('change', function(){
    document.body.classList.toggle('dark-mode', this.checked);
});
</script>

</body>
</html>