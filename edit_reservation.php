<?php
session_start();

if(!isset($_SESSION['name'])){
    header("Location: index.php");
    exit();
}

include 'db.php';

$professor_id = $_SESSION['professor_id'];
$res_id = isset($_GET['id']) ? $_GET['id'] : '';

if(empty($res_id)){
    header("Location: dashboard.php");
    exit();
}

// --- Security: Make sure this reservation belongs to the logged-in professor ---
$res = mysqli_fetch_assoc(mysqli_query($conn,
    "SELECT reservations.*, classrooms.room_name, classrooms.floor_number
     FROM reservations
     LEFT JOIN classrooms ON reservations.classroom_id = classrooms.id
     WHERE reservations.id = '$res_id'
     AND reservations.professor_id = '$professor_id'"
));

if(!$res){
    echo "<script>alert('Reservation not found or access denied.'); window.location='dashboard.php';</script>";
    exit();
}

$success_msg = '';
$error_msg   = '';

// --- HANDLE UPDATE ---
if(isset($_POST['update'])){
    $new_date  = $_POST['reserve_date'];
    $new_start = $_POST['start_time'];
    $new_end   = $_POST['end_time'];
    $room_id   = $res['classroom_id'];

    // Check for conflict with OTHER reservations on the same room & date (exclude current)
    $conflict = mysqli_query($conn,
        "SELECT * FROM reservations
         WHERE classroom_id = '$room_id'
         AND reserve_date  = '$new_date'
         AND id != '$res_id'
         AND (
             (start_time <= '$new_start' AND end_time > '$new_start')
             OR
             (start_time < '$new_end' AND end_time >= '$new_end')
         )"
    );

    if(mysqli_num_rows($conflict) > 0){
        $error_msg = "This room is already reserved by someone else at that time. Please choose a different time.";
    } elseif($new_start >= $new_end){
        $error_msg = "End time must be later than start time.";
    } else {
        mysqli_query($conn,
            "UPDATE reservations
             SET reserve_date = '$new_date',
                 start_time   = '$new_start',
                 end_time     = '$new_end'
             WHERE id = '$res_id'
             AND professor_id = '$professor_id'"
        );
        $success_msg = "Reservation updated successfully!";

        // Refresh reservation data
        $res = mysqli_fetch_assoc(mysqli_query($conn,
            "SELECT reservations.*, classrooms.room_name, classrooms.floor_number
             FROM reservations
             LEFT JOIN classrooms ON reservations.classroom_id = classrooms.id
             WHERE reservations.id = '$res_id'"
        ));
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Reservation</title>
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
    .topbar-links { display: flex; gap: 10px; }
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
    .hero-badge {
        display: inline-block;
        background: rgba(255,255,255,0.2);
        border: 1px solid rgba(255,255,255,0.3);
        padding: 5px 14px;
        border-radius: 100px;
        font-size: 12px;
        font-weight: 600;
        margin-bottom: 14px;
        position: relative; z-index: 1;
        backdrop-filter: blur(4px);
    }
    .hero h1 { font-size: 26px; font-weight: 800; margin-bottom: 6px; position: relative; z-index: 1; }
    .hero p  { font-size: 14px; opacity: 0.85; position: relative; z-index: 1; }

    /* CONTAINER */
    .container {
        max-width: 780px;
        margin: 0 auto;
        padding: 40px 20px;
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 24px;
        align-items: start;
    }

    /* CARD */
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

    /* ROOM INFO */
    .room-info-item {
        display: flex;
        align-items: center;
        gap: 12px;
        padding: 13px 14px;
        background: #fff0f5;
        border-radius: 12px;
        border: 1px solid #fce7f3;
        margin-bottom: 12px;
    }
    .room-info-item:last-child { margin-bottom: 0; }
    .room-info-icon {
        width: 38px; height: 38px;
        background: linear-gradient(135deg, #fce7f3, #fbcfe8);
        border-radius: 10px;
        display: flex; align-items: center; justify-content: center;
        font-size: 18px;
        flex-shrink: 0;
    }
    .room-info-label { font-size: 11px; font-weight: 700; color: #c084a0; text-transform: uppercase; letter-spacing: 0.7px; }
    .room-info-value { font-size: 14px; font-weight: 700; color: #be185d; }

    /* CURRENT BADGE */
    .current-box {
        background: #fff8fa;
        border: 1px dashed #f9a8d4;
        border-radius: 14px;
        padding: 16px;
        margin-bottom: 20px;
    }
    .current-box .current-label {
        font-size: 11px;
        font-weight: 700;
        color: #c084a0;
        text-transform: uppercase;
        letter-spacing: 0.8px;
        margin-bottom: 10px;
    }
    .current-row {
        display: flex;
        align-items: center;
        gap: 8px;
        font-size: 13px;
        color: #6b5a60;
        margin-bottom: 6px;
    }
    .current-row:last-child { margin-bottom: 0; }
    .current-row b { color: #be185d; }

    /* FORM */
    .form-group { margin-bottom: 16px; }
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
    .time-row { display: grid; grid-template-columns: 1fr 1fr; gap: 12px; }

    /* ALERTS */
    .alert {
        padding: 12px 16px;
        border-radius: 12px;
        font-size: 13px;
        font-weight: 600;
        margin-bottom: 18px;
        display: flex;
        align-items: flex-start;
        gap: 8px;
    }
    .alert-success { background: #dcfce7; color: #16a34a; border: 1px solid #bbf7d0; }
    .alert-error   { background: #ffe4e6; color: #e11d48; border: 1px solid #fecdd3; }

    /* BUTTONS */
    .update-btn {
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
        margin-bottom: 10px;
    }
    .update-btn:hover { transform: translateY(-1px); box-shadow: 0 6px 20px rgba(219,39,119,0.4); }

    .back-link {
        display: block;
        text-align: center;
        color: #c084a0;
        font-size: 13px;
        font-weight: 600;
        text-decoration: none;
        padding: 10px;
        border-radius: 10px;
        transition: all 0.2s;
    }
    .back-link:hover { background: #fff0f5; color: #db2777; }

    @media(max-width: 650px){
        .container { grid-template-columns: 1fr; }
        .card.full-width { grid-column: 1; }
        .topbar { padding: 15px 20px; flex-direction: column; gap: 12px; text-align: center; }
        .topbar-links { flex-wrap: wrap; justify-content: center; }
        .hero { padding: 28px 20px; }
        .time-row { grid-template-columns: 1fr; }
    }
    </style>
</head>
<body>

<!-- TOPBAR -->
<div class="topbar">
    <h2>✏️ Edit Reservation</h2>
    <div class="topbar-links">
        <a href="rooms.php?floor=<?php echo $res['floor_number']; ?>" class="outline">← Back to Rooms</a>
        <a href="dashboard.php">Dashboard</a>
    </div>
</div>

<!-- HERO -->
<div class="hero">
    <div class="hero-badge">📅 Edit Reservation</div>
    <h1><?php echo $res['room_name']; ?> — Floor <?php echo $res['floor_number']; ?></h1>
    <p>Update your reservation date and time below.</p>
</div>

<div class="container">

    <!-- ROOM INFO -->
    <div class="card">
        <div class="card-title">🚪 Room Details</div>

        <div class="room-info-item">
            <div class="room-info-icon">🏫</div>
            <div>
                <div class="room-info-label">Room Name</div>
                <div class="room-info-value"><?php echo $res['room_name']; ?></div>
            </div>
        </div>
        <div class="room-info-item">
            <div class="room-info-icon">🏢</div>
            <div>
                <div class="room-info-label">Floor</div>
                <div class="room-info-value">Floor <?php echo $res['floor_number']; ?></div>
            </div>
        </div>
        <div class="room-info-item">
            <div class="room-info-icon">👤</div>
            <div>
                <div class="room-info-label">Reserved By</div>
                <div class="room-info-value"><?php echo $res['reserved_by']; ?></div>
            </div>
        </div>

        <div style="margin-top:20px;">
            <div class="current-box">
                <div class="current-label">📋 Current Schedule</div>
                <div class="current-row">📅 <span>Date: <b><?php echo $res['reserve_date']; ?></b></span></div>
                <div class="current-row">🕐 <span>Start: <b><?php echo date("h:i A", strtotime($res['start_time'])); ?></b></span></div>
                <div class="current-row">🕑 <span>End: <b><?php echo date("h:i A", strtotime($res['end_time'])); ?></b></span></div>
            </div>
        </div>
    </div>

    <!-- EDIT FORM -->
    <div class="card">
        <div class="card-title">✏️ Update Schedule</div>

        <?php if($success_msg): ?>
        <div class="alert alert-success">✅ <?php echo $success_msg; ?></div>
        <?php endif; ?>

        <?php if($error_msg): ?>
        <div class="alert alert-error">❌ <?php echo $error_msg; ?></div>
        <?php endif; ?>

        <form method="POST">
            <div class="form-group">
                <label class="form-label">📅 New Date</label>
                <input type="date" name="reserve_date" class="form-input"
                       value="<?php echo $res['reserve_date']; ?>" required>
            </div>

            <div class="time-row">
                <div class="form-group">
                    <label class="form-label">🕐 New Start Time</label>
                    <input type="time" name="start_time" class="form-input"
                           value="<?php echo $res['start_time']; ?>" required>
                </div>
                <div class="form-group">
                    <label class="form-label">🕑 New End Time</label>
                    <input type="time" name="end_time" class="form-input"
                           value="<?php echo $res['end_time']; ?>" required>
                </div>
            </div>

            <button type="submit" name="update" class="update-btn">💾 Save Changes</button>
            <a href="rooms.php?floor=<?php echo $res['floor_number']; ?>" class="back-link">Cancel — go back</a>
        </form>
    </div>

</div>
</body>
</html>