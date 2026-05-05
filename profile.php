<?php
session_start();
require_once 'config.php';

if (!isset($_SESSION['logged_in'])) {
    header("Location: index.php");
    exit();
}

$username = $_SESSION['username'];
$current_page = basename($_SERVER['PHP_SELF'], '.php');


// FETCH USER FIRST
$stmt = $conn->prepare("SELECT * FROM users WHERE username=?");
$stmt->bind_param("s",$username);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();
if(!$user) {
    echo '<div style="color:red; text-align:center; margin-top:40px;">User not found.</div>';
    exit();
}

// Load settings for this user (email only)
$emailKey = $username.'_email';
$userSettings = [
    'email' => isset($user['email']) ? $user['email'] : '',
];
$stmt = $conn->prepare("SELECT setting_key, setting_value FROM settings WHERE setting_key=? AND setting_value IS NOT NULL");
$stmt->bind_param("s", $emailKey);
$stmt->execute();
$result = $stmt->get_result();
while($row = $result->fetch_assoc()) {
    if(strpos($row['setting_key'], '_email') !== false) $userSettings['email'] = $row['setting_value'];
}

$profile = (!empty($user['profile_pic'])) ? "uploads/".$user['profile_pic'] : "uploads/default.png";

/* UPDATE PROFILE */
if(isset($_POST['update_profile'])){
    $error = null;
    // Sanitize inputs
    $email = trim(htmlspecialchars($_POST['email']));

    $uploadDir = "uploads/";
    if(!is_dir($uploadDir)){
        mkdir($uploadDir,0777,true);
    }

    $profile_pic = isset($user['profile_pic']) ? $user['profile_pic'] : null;
    $updatePic = false;
    $allowedTypes = ['image/jpeg','image/png','image/gif','image/webp'];

    if(!empty($_FILES['profile_pic']['name'])){
        $fileType = mime_content_type($_FILES['profile_pic']['tmp_name']);
        if(in_array($fileType, $allowedTypes)){
            $profile_pic = time()."_".basename($_FILES['profile_pic']['name']);
            $target = $uploadDir.$profile_pic;
            if(move_uploaded_file($_FILES['profile_pic']['tmp_name'],$target)){
                $updatePic = true;
                if(!empty($user['profile_pic']) && file_exists($uploadDir.$user['profile_pic']) && $user['profile_pic'] !== 'default.png'){
                    @unlink($uploadDir.$user['profile_pic']);
                }
            } else {
                $error = "Failed to upload image.";
            }
        } else {
            $error = "Invalid image type. Allowed: JPG, PNG, GIF, WEBP.";
        }
    }

    if(empty($error)){
        if($updatePic){
            $stmt = $conn->prepare("UPDATE users SET email=?, profile_pic=? WHERE username=?");
            $stmt->bind_param("sss", $email, $profile_pic, $username);
        } else {
            $stmt = $conn->prepare("UPDATE users SET email=? WHERE username=?");
            $stmt->bind_param("ss", $email, $username);
        }
        $ok = $stmt->execute();
        // Update settings table for email
        $stmt2 = $conn->prepare("INSERT INTO settings (setting_key, setting_value) VALUES (?, ?) ON DUPLICATE KEY UPDATE setting_value=?");
        $stmt2->bind_param("sss", $emailKey, $email, $email);
        $stmt2->execute();
        if($ok){
            header("Location: profile.php");
            exit();
        } else {
            $error = "Failed to update profile.";
        }
    }
}
?>

<!DOCTYPE html>
<html>
<head>

<title>User Profile</title>

<meta name="viewport" content="width=device-width, initial-scale=1.0">

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
.main-content{
flex:1;
padding:20px;
}

.container{
max-width:900px;
margin:40px auto;
padding:20px;
}

.card{
background:white;
border-radius:12px;
box-shadow:0 5px 20px rgba(17, 27, 179, 0.1);
overflow:hidden;
}

.profile-header{
background:linear-gradient(135deg,#667eea,#764ba2);
padding:40px;
text-align:center;
color:white;
position:relative;
}

.profile-avatar{
width:130px;
height:130px;
border-radius:50%;
border:5px solid white;
object-fit:cover;
margin-bottom:15px;
}

.dashboard-btn{
position:absolute;
top:20px;
left:20px;
background:white;
color:#333;
padding:8px 15px;
border-radius:6px;
text-decoration:none;
font-weight:600;
}

.dashboard-btn:hover{
background:#f2f2f2;
}

.profile-body{
padding:30px;
}

.section-title{
font-size:18px;
font-weight:600;
margin-bottom:15px;
border-bottom:2px solid #e8eee8;
padding-bottom:5px;
}

.info-grid{
display:grid;
grid-template-columns:1fr 1fr;
gap:15px;
margin-bottom:25px;
}

.info-box{
background:#f7f9fc;
padding:15px;
border-radius:8px;
}

.label{
font-size:13px;
color:#888;
}

.value{
font-weight:600;
margin-top:3px;
}

input{
width:100%;
padding:10px;
border:1px solid #816969;
border-radius:5px;
margin-bottom:15px;
}

.save-btn{
background:#27ae60;
border:none;
color:white;
padding:10px 20px;
border-radius:5px;
cursor:pointer;
}

.save-btn:hover{
background:#219150;
}

.preview{
margin-bottom:10px;
width:100px;
border-radius:50%;
}

@media(max-width:700px){

.info-grid{
grid-template-columns:1fr;
}

.dashboard-btn{
position:static;
display:inline-block;
margin-bottom:10px;
}

}

</style>

</head>

<body>

<?php include 'sidebar.php'; ?>

<div class="main-content">

<div class="container">

<div class="card">

<div class="profile-header">

<a href="dashboard.php" class="dashboard-btn">
<i class="fas fa-arrow-left"></i> Dashboard
</a>

<img id="preview" class="profile-avatar" src="<?php echo $profile; ?>">

<h2>
    <?php if(isset($user['fullname']) && $user['fullname'] !== ''): ?>
        <?php echo htmlspecialchars($user['fullname']); ?>
    <?php else: ?>
        <span style="color:#888;font-style:italic;">No name provided</span>
    <?php endif; ?>
</h2>

<p>
    @<?php if(isset($user['username']) && $user['username'] !== ''): ?>
        <?php echo htmlspecialchars($user['username']); ?>
    <?php else: ?>
        <span style="color:#888;font-style:italic;">No username</span>
    <?php endif; ?>
</p>

</div>

<div class="profile-body">

<div class="section-title">Account Information</div>

<div class="info-grid">

<div class="info-box">
<div class="label">Username</div>
<div class="value"><?php echo isset($user['username']) ? htmlspecialchars($user['username']) : '<span style="color:#888;font-style:italic;">N/A</span>'; ?></div>
</div>

<div class="info-box">
<div clasALTER TABLE users ADD COLUMN profile_pic VARCHAR(255) DEFAULT NULL;s="label">Role</div>
<div class="value"><?php echo isset($user['role']) ? htmlspecialchars($user['role']) : '<span style="color:#888;font-style:italic;">N/A</span>'; ?></div>
</div>

<div class="info-box">
<div class="label">Status</div>
<div class="value"><?php echo isset($user['status']) ? htmlspecialchars($user['status']) : '<span style="color:#888;font-style:italic;">N/A</span>'; ?></div>
</div>

<div class="info-box">
<div class="label">Member Since</div>
<div class="value"><?php echo (isset($user['created_at']) && $user['created_at']) ? date("F Y",strtotime($user['created_at'])) : '<span style="color:#888;font-style:italic;">N/A</span>'; ?></div>
</div>

</div>

<div class="section-title">Contact Information</div>

<div class="info-grid">

<div class="info-box">
<div class="label">Email</div>
<div class="value"><?php echo isset($user['email']) ? htmlspecialchars($user['email']) : '<span style="color:#888;font-style:italic;">N/A</span>'; ?></div>
</div>

<div class="info-box">
<div class="label">Phone</div>
<div class="value"><?php echo isset($user['phone']) ? htmlspecialchars($user['phone']) : '<span style="color:#888;font-style:italic;">N/A</span>'; ?></div>
</div>

</div>

<div class="section-title">Edit Profile</div>


<?php if(!empty($error)): ?>
    <div style="color:red; margin-bottom:10px;"> <?php echo $error; ?> </div>
<?php endif; ?>

<form method="POST" enctype="multipart/form-data">



    <label>Email</label>
    <input type="email" name="email" value="<?php echo htmlspecialchars($userSettings['email']); ?>">

    <label>Change Profile Picture</label>
    <img id="imgPreview" class="preview" src="<?php echo $profile; ?>" alt="Profile Picture Preview">
    <input type="file" id="profile_pic" name="profile_pic" accept="image/*" onchange="previewImage(event)">
    <span id="fileName" style="display:block; margin-bottom:10px; color:#555;"></span>


    <label for="profile_pic">Change Profile Picture</label>
    <img id="imgPreview" class="preview" src="<?php echo $profile; ?>" alt="Profile Picture Preview">
    <input type="file" id="profile_pic" name="profile_pic" accept="image/*" onchange="previewImage(event)">
    <span id="fileName" style="display:block; margin-bottom:10px; color:#555;"></span>

    <button class="save-btn" name="update_profile">
        <i class="fa fa-save"></i> Save Changes
    </button>
</form>

</div>

</div>

</div>

<script>
function previewImage(event) {
    const input = event.target;
    const fileNameSpan = document.getElementById('fileName');
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        reader.onload = function(e) {
            document.getElementById('imgPreview').src = e.target.result;
        }
        reader.readAsDataURL(input.files[0]);
        fileNameSpan.textContent = 'Selected file: ' + input.files[0].name;
    } else {
        document.getElementById('imgPreview').src = '<?php echo $profile; ?>';
        fileNameSpan.textContent = '';
    }
}
</script>

</body>
</html>