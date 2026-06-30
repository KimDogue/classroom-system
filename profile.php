<?php
session_start();

if(!isset($_SESSION['name'])){
    header("Location: index.php");
    exit();
}

include 'db.php';

$professor_id = $_SESSION['professor_id'];

// --- AVATAR HELPER ---
function getInitials($name){
    $words = explode(' ', trim($name));
    $initials = '';
    foreach($words as $w){
        if(!empty($w)) $initials .= strtoupper($w[0]);
        if(strlen($initials) == 2) break;
    }
    return $initials ?: '?';
}

function getAvatarColor($name){
    $colors = [
        ['#db2777','#9d174d'],
        ['#7c3aed','#4c1d95'],
        ['#0369a1','#0c4a6e'],
        ['#059669','#064e3b'],
        ['#d97706','#78350f'],
        ['#dc2626','#7f1d1d'],
        ['#0891b2','#164e63'],
    ];
    $idx = abs(crc32($name)) % count($colors);
    return $colors[$idx];
}

// --- HANDLE UPDATE ---
$success_msg = '';
$error_msg = '';

if(isset($_POST['update'])){
    $full_name = trim($_POST['full_name']);
    $email     = trim($_POST['email']);
    $new_pass  = trim($_POST['new_password']);
    $confirm   = trim($_POST['confirm_password']);

    // Check if email already used by another user
    $check = mysqli_query($conn, "SELECT id FROM professors WHERE email='$email' AND id != '$professor_id'");
    if(mysqli_num_rows($check) > 0){
        $error_msg = "Email is already used by another account.";
    } elseif(!empty($new_pass) && $new_pass !== $confirm){
        $error_msg = "New passwords do not match.";
    } else {
        if(!empty($new_pass)){
            mysqli_query($conn, "UPDATE professors SET full_name='$full_name', email='$email', password='$new_pass' WHERE id='$professor_id'");
        } else {
            mysqli_query($conn, "UPDATE professors SET full_name='$full_name', email='$email' WHERE id='$professor_id'");
        }
        $_SESSION['name'] = $full_name;
        $success_msg = "Profile updated successfully!";
    }
}

// --- FETCH CURRENT DATA ---
$prof = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM professors WHERE id='$professor_id'"));

$initials = getInitials($prof['full_name']);
$avatarColors = getAvatarColor($prof['full_name']);

// --- FETCH RESERVATION HISTORY ---
$history = mysqli_query($conn,
    "SELECT reservations.*, classrooms.room_name
     FROM reservations
     LEFT JOIN classrooms ON reservations.classroom_id = classrooms.id
     WHERE reservations.professor_id = '$professor_id'
     ORDER BY reserve_date DESC"
);
$res_count = mysqli_num_rows($history);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Profile</title>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
    * { margin: 0; padding: 0; box-sizing: border-box; }

    body {
        font-family: 'Plus Jakarta Sans', sans-serif;
        background: #fff5f7;
        color: #3b1a2a;
        min-height: 100vh;
    }

    /* TOPBAR */
    .topbar {
        background: #db2777;
        color: white;
        padding: 18px 40px;
        display: flex;
        justify-content: space-between;
        align-items: center;
        box-shadow: 0 4px 20px rgba(219,39,119,0.25);
        position: sticky;
        top: 0;
        z-index: 100;
    }
    .topbar h2 { font-size: 20px; font-weight: 800; }
    .topbar-links { display: flex; gap: 12px; }
    .topbar a {
        text-decoration: none;
        color: #db2777;
        background: white;
        padding: 9px 22px;
        border-radius: 10px;
        font-weight: 700;
        font-size: 13px;
        transition: all 0.2s;
    }
    .topbar a:hover { background: #fce7f3; transform: translateY(-1px); }
    .topbar a.outline {
        background: transparent;
        color: white;
        border: 1px solid rgba(255,255,255,0.5);
    }
    .topbar a.outline:hover { background: rgba(255,255,255,0.15); }

    /* HERO */
    .hero {
        background: linear-gradient(135deg, #db2777 0%, #be185d 60%, #9d174d 100%);
        color: white;
        padding: 45px 40px;
        position: relative;
        overflow: hidden;
    }
    .hero::before {
        content: '';
        position: absolute;
        top: -60px; right: -60px;
        width: 280px; height: 280px;
        background: rgba(255,255,255,0.06);
        border-radius: 50%;
    }
    .hero-inner { position: relative; z-index: 1; display: flex; align-items: center; gap: 24px; }
    .avatar {
        width: 72px; height: 72px;
        border: 2px solid rgba(255,255,255,0.4);
        border-radius: 20px;
        display: flex; align-items: center; justify-content: center;
        font-size: 26px;
        font-weight: 800;
        color: white;
        flex-shrink: 0;
        backdrop-filter: blur(4px);
        letter-spacing: 1px;
        box-shadow: 0 4px 15px rgba(0,0,0,0.15);
    }
    .hero h1 { font-size: 26px; font-weight: 800; margin-bottom: 4px; }
    .hero p { font-size: 14px; opacity: 0.85; }
    .hero .role-badge {
        display: inline-block;
        background: rgba(255,255,255,0.2);
        border: 1px solid rgba(255,255,255,0.3);
        padding: 4px 12px;
        border-radius: 100px;
        font-size: 12px;
        font-weight: 600;
        margin-top: 6px;
        backdrop-filter: blur(4px);
    }

    /* CONTAINER */
    .container {
        max-width: 900px;
        margin: 0 auto;
        padding: 40px 20px;
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 24px;
    }

    /* CARDS */
    .card {
        background: white;
        border-radius: 20px;
        border: 1px solid #fce7f3;
        box-shadow: 0 2px 12px rgba(219,39,119,0.05);
        padding: 28px;
    }
    .card.full-width { grid-column: 1 / -1; }

    .card-title {
        font-size: 16px;
        font-weight: 800;
        color: #be185d;
        margin-bottom: 20px;
        display: flex;
        align-items: center;
        gap: 8px;
    }

    /* FORM */
    .form-group { margin-bottom: 18px; }
    .form-label {
        display: block;
        font-size: 11px;
        font-weight: 700;
        color: #c084a0;
        text-transform: uppercase;
        letter-spacing: 0.8px;
        margin-bottom: 6px;
    }
    .form-input {
        width: 100%;
        padding: 12px 14px;
        border: 1px solid #fce7f3;
        border-radius: 12px;
        font-size: 14px;
        font-family: 'Plus Jakarta Sans', sans-serif;
        color: #3b1a2a;
        background: #fff8fa;
        transition: all 0.2s;
    }
    .form-input:focus {
        outline: none;
        border-color: #db2777;
        background: white;
        box-shadow: 0 0 0 3px rgba(219,39,119,0.1);
    }
    .divider {
        border: none;
        border-top: 1px solid #fce7f3;
        margin: 20px 0;
    }
    .save-btn {
        width: 100%;
        padding: 13px;
        background: linear-gradient(135deg, #db2777, #be185d);
        color: white;
        border: none;
        border-radius: 12px;
        font-size: 14px;
        font-weight: 700;
        cursor: pointer;
        font-family: 'Plus Jakarta Sans', sans-serif;
        transition: all 0.2s;
        box-shadow: 0 4px 14px rgba(219,39,119,0.3);
    }
    .save-btn:hover { transform: translateY(-1px); box-shadow: 0 6px 20px rgba(219,39,119,0.4); }

    /* ALERTS */
    .alert {
        padding: 12px 16px;
        border-radius: 12px;
        font-size: 13px;
        font-weight: 600;
        margin-bottom: 18px;
    }
    .alert-success { background: #dcfce7; color: #16a34a; border: 1px solid #bbf7d0; }
    .alert-error   { background: #ffe4e6; color: #e11d48; border: 1px solid #fecdd3; }

    /* STAT CARDS */
    .stat-row {
        display: flex;
        flex-direction: column;
        gap: 14px;
    }
    .stat-item {
        display: flex;
        align-items: center;
        gap: 14px;
        padding: 14px;
        background: #fff0f5;
        border-radius: 12px;
        border: 1px solid #fce7f3;
    }
    .stat-icon-box {
        width: 42px; height: 42px;
        background: linear-gradient(135deg, #fce7f3, #fbcfe8);
        border-radius: 10px;
        display: flex; align-items: center; justify-content: center;
        font-size: 20px;
        flex-shrink: 0;
    }
    .stat-item .stat-label { font-size: 12px; color: #c084a0; font-weight: 600; }
    .stat-item .stat-value { font-size: 16px; font-weight: 800; color: #be185d; }

    /* HISTORY TABLE */
    .history-table { width: 100%; border-collapse: collapse; font-size: 13px; }
    .history-table th {
        text-align: left;
        padding: 10px 14px;
        background: #fff0f5;
        color: #c084a0;
        font-size: 11px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.8px;
    }
    .history-table th:first-child { border-radius: 10px 0 0 10px; }
    .history-table th:last-child  { border-radius: 0 10px 10px 0; }
    .history-table td {
        padding: 12px 14px;
        border-bottom: 1px solid #fff0f5;
        color: #4a2535;
    }
    .history-table tr:last-child td { border-bottom: none; }
    .history-table tr:hover td { background: #fff8fa; }
    .history-table b { color: #be185d; }

    .empty-history {
        text-align: center;
        padding: 40px;
        color: #c084a0;
        font-size: 14px;
    }
    .empty-history .empty-icon { font-size: 36px; margin-bottom: 10px; }

    @media(max-width: 700px){
        .container { grid-template-columns: 1fr; }
        .card.full-width { grid-column: 1; }
        .topbar { padding: 15px 20px; flex-direction: column; gap: 12px; text-align: center; }
        .hero { padding: 28px 20px; }
        .hero-inner { flex-direction: column; text-align: center; }
    }
    </style>
</head>
<body>

<!-- TOPBAR -->
<div class="topbar">
    <h2>👤 My Profile</h2>
    <div class="topbar-links">
        <a href="dashboard.php" class="outline">← Dashboard</a>
        <a href="logout.php">Logout</a>
    </div>
</div>

<!-- HERO -->
<div class="hero">
    <div class="hero-inner">
        <div class="avatar" style="background: linear-gradient(135deg, rgba(255,255,255,0.25), rgba(255,255,255,0.1));">
            <?php echo $initials; ?>
        </div>
        <div>
            <h1><?php echo $prof['full_name']; ?></h1>
            <p><?php echo $prof['email']; ?></p>
            <span class="role-badge"><?php echo ucfirst($prof['role']); ?></span>
        </div>
    </div>
</div>

<div class="container">

    <!-- EDIT PROFILE FORM -->
    <div class="card">
        <div class="card-title">✏️ Edit Profile</div>

        <?php if($success_msg): ?>
        <div class="alert alert-success">✅ <?php echo $success_msg; ?></div>
        <?php endif; ?>

        <?php if($error_msg): ?>
        <div class="alert alert-error">❌ <?php echo $error_msg; ?></div>
        <?php endif; ?>

        <form method="POST">
            <div class="form-group">
                <label class="form-label">Full Name</label>
                <input type="text" name="full_name" class="form-input"
                       value="<?php echo $prof['full_name']; ?>" required>
            </div>
            <div class="form-group">
                <label class="form-label">Email Address</label>
                <input type="email" name="email" class="form-input"
                       value="<?php echo $prof['email']; ?>" required>
            </div>

            <hr class="divider">

            <div class="form-group">
                <label class="form-label">New Password <span style="color:#c084a0;font-weight:400;">(leave blank to keep current)</span></label>
                <input type="password" name="new_password" class="form-input" placeholder="Enter new password">
            </div>
            <div class="form-group">
                <label class="form-label">Confirm New Password</label>
                <input type="password" name="confirm_password" class="form-input" placeholder="Confirm new password">
            </div>

            <button type="submit" name="update" class="save-btn">💾 Save Changes</button>
        </form>
    </div>

    <!-- ACCOUNT SUMMARY -->
    <div class="card">
        <div class="card-title">📊 Account Summary</div>
        <div class="stat-row">
            <div class="stat-item">
                <div class="stat-icon-box" style="background: linear-gradient(135deg, <?php echo $avatarColors[0]; ?>, <?php echo $avatarColors[1]; ?>); color: white; font-size: 14px; font-weight: 800; letter-spacing: 0.5px;">
                    <?php echo $initials; ?>
                </div>
                <div>
                    <div class="stat-label">Full Name</div>
                    <div class="stat-value"><?php echo $prof['full_name']; ?></div>
                </div>
            </div>
            <div class="stat-item">
                <div class="stat-icon-box">📧</div>
                <div>
                    <div class="stat-label">Email</div>
                    <div class="stat-value" style="font-size:13px;"><?php echo $prof['email']; ?></div>
                </div>
            </div>
            <div class="stat-item">
                <div class="stat-icon-box">🎓</div>
                <div>
                    <div class="stat-label">Role</div>
                    <div class="stat-value"><?php echo ucfirst($prof['role']); ?></div>
                </div>
            </div>
            <div class="stat-item">
                <div class="stat-icon-box">📅</div>
                <div>
                    <div class="stat-label">Total Reservations</div>
                    <div class="stat-value"><?php echo $res_count; ?> Reservation<?php echo $res_count != 1 ? 's' : ''; ?></div>
                </div>
            </div>
            <div class="stat-item">
                <div class="stat-icon-box">🗓️</div>
                <div>
                    <div class="stat-label">Member Since</div>
                    <div class="stat-value"><?php echo date("M d, Y", strtotime($prof['created_at'])); ?></div>
                </div>
            </div>
        </div>
    </div>

    <!-- RESERVATION HISTORY -->
    <div class="card full-width">
        <div class="card-title">📋 My Reservation History</div>

        <?php if($res_count > 0): ?>
        <div style="overflow-x:auto;">
            <table class="history-table">
                <thead>
                    <tr>
                        <th>Room</th>
                        <th>Date</th>
                        <th>Start Time</th>
                        <th>End Time</th>
                    </tr>
                </thead>
                <tbody>
                <?php
                mysqli_data_seek($history, 0);
                while($r = mysqli_fetch_assoc($history)): ?>
                    <tr>
                        <td><b><?php echo $r['room_name']; ?></b></td>
                        <td><?php echo $r['reserve_date']; ?></td>
                        <td><?php echo date("h:i A", strtotime($r['start_time'])); ?></td>
                        <td><?php echo date("h:i A", strtotime($r['end_time'])); ?></td>
                    </tr>
                <?php endwhile; ?>
                </tbody>
            </table>
        </div>
        <?php else: ?>
        <div class="empty-history">
            <div class="empty-icon">📭</div>
            <p>No reservation history yet.</p>
        </div>
        <?php endif; ?>
    </div>

</div>
</body>
</html>