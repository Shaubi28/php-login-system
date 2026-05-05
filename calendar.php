<?php
session_start();
require_once 'config.php';
require_once 'load_settings.php';

if (!isset($_SESSION['logged_in'])) {
    header("Location: index.php");
    exit();
}

$username = $_SESSION['username'];

/* NOTES */
if (!isset($_SESSION['calendar_notes'])) {
    $_SESSION['calendar_notes'] = [];
}

/* DATE */
$today = new DateTime();
$month = (int)($_GET['m'] ?? $today->format('n'));
$year  = (int)($_GET['y'] ?? $today->format('Y'));
$selected = $_GET['selected'] ?? '';

$first = new DateTime("$year-$month-01");
$daysInMonth = $first->format('t');
$startWeekday = $first->format('N');

$prev = (clone $first)->modify('-1 month');
$next = (clone $first)->modify('+1 month');

/* SAVE */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!empty($_POST['note_date']) && !empty($_POST['note_text'])) {
        $_SESSION['calendar_notes'][$_POST['note_date']] = trim($_POST['note_text']);
        header("Location: ?m=$month&y=$year&selected=".$_POST['note_date']);
        exit();
    }
}

$notes = $_SESSION['calendar_notes'];
?>

<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<title>Calendar</title>

<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

<style>
* {
    margin:0;
    padding:0;
    box-sizing:border-box;
    font-family: 'Segoe UI', sans-serif;
}

body {
    background:#e2ebee;
    display:flex;
}

/* ===== SIDEBAR (SAME AS ANALYTICS) ===== */
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
/* CARD */
.card {
    background:white;
    padding:20px;
    border-radius:10px;
    margin-bottom:20px;
}

/* CALENDAR */
.calendar {
    display:grid;
    grid-template-columns:repeat(7,1fr);
    gap:10px;
}

.dayheader {
    text-align:center;
    font-weight:bold;
}

.cell {
    background:#fff;
    padding:10px;
    min-height:80px;
    border-radius:8px;
    transition:0.2s;
}

.cell:hover {
    transform:translateY(-3px);
}

.cell a {
    text-decoration:none;
    color:black;
}

.selected {
    background:#3498db;
    color:white;
}

.selected a {
    color:white;
}

.note-preview {
    font-size:12px;
}

/* FORM */
textarea {
    width:100%;
    height:100px;
    margin-top:10px;
}

/* MOBILE */
@media(max-width:768px){
    .sidebar{
        left:-260px;
        transition:0.3s;
    }

    .sidebar.active{
        left:0;
    }

    .main-content{
        margin-left:0;
    }

    .mobile-toggle{
        display:block;
    }
}
</style>
</head>

<body class="<?= ($dark_mode === 'on') ? 'dark-mode' : '' ?>">

<!-- SIDEBAR -->
<?php include 'sidebar.php'; ?>

<div class="main-content">

    <!-- TOP BAR (NEW LIKE ANALYTICS) -->
    <div class="top-bar">
        <button class="mobile-toggle" id="menuToggle">
            <i class="fas fa-bars"></i>
        </button>
        <div class="page-title">Calendar</div>
    </div>

    <!-- NAV -->
    <div class="card" style="display:flex;justify-content:space-between;">
        <a href="?m=<?=$prev->format('n')?>&y=<?=$prev->format('Y')?>">⬅ Prev</a>
        <h2><?= $first->format('F Y') ?></h2>
        <a href="?m=<?=$next->format('n')?>&y=<?=$next->format('Y')?>">Next ➡</a>
    </div>

    <!-- CALENDAR -->
    <div class="card">
        <div class="calendar">

        <?php foreach(['Mon','Tue','Wed','Thu','Fri','Sat','Sun'] as $d): ?>
            <div class="dayheader"><?=$d?></div>
        <?php endforeach; ?>

        <?php for($i=1;$i<$startWeekday;$i++): ?>
            <div></div>
        <?php endfor; ?>

        <?php for($d=1;$d<=$daysInMonth;$d++):
            $date = sprintf('%04d-%02d-%02d',$year,$month,$d);
            $active = ($selected==$date)?'selected':'';
        ?>

        <div class="cell <?=$active?>">
            <a href="?m=<?=$month?>&y=<?=$year?>&selected=<?=$date?>">
                <?=$d?>
            </a>

            <?php if(isset($notes[$date])): ?>
                <div class="note-preview">
                    <?= htmlspecialchars($notes[$date]) ?>
                </div>
            <?php endif; ?>
        </div>

        <?php endfor; ?>
        </div>
    </div>

    <!-- NOTE -->
    <?php if($selected): ?>
    <div class="card">
        <h3>Selected: <?= $selected ?></h3>
        <form method="post">
            <input type="hidden" name="note_date" value="<?=$selected?>">
            <textarea name="note_text"><?= $notes[$selected] ?? '' ?></textarea>
            <button type="submit">Save</button>
        </form>
    </div>
    <?php endif; ?>

</div>

<script>
document.getElementById('menuToggle').onclick = () => {
    document.getElementById('sidebar').classList.toggle('active');
};
</script>

</body>
</html>