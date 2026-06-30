<?php
session_start();

if(!isset($_SESSION['role']) || strtolower(trim($_SESSION['role'])) != 'admin'){
    header("Location: index.php");
    exit();
}

include 'db.php';

// --- DELETE PROFESSOR ---
if(isset($_GET['delete_prof'])){
    $del_id = $_GET['delete_prof'];
    mysqli_query($conn, "DELETE FROM professors WHERE id='$del_id'");
    header("Location: admin.php?tab=professors");
    exit();
}

// --- DELETE RESERVATION ---
if(isset($_GET['delete_res'])){
    $del_res = $_GET['delete_res'];
    $res_row = mysqli_fetch_assoc(mysqli_query($conn, "SELECT classroom_id FROM reservations WHERE id='$del_res'"));
    mysqli_query($conn, "DELETE FROM reservations WHERE id='$del_res'");
    if($res_row){
        mysqli_query($conn, "UPDATE classrooms SET status='Available' WHERE id='".$res_row['classroom_id']."'");
    }
    header("Location: admin.php?tab=reservations");
    exit();
}

// --- FETCH DATA ---
$professors   = mysqli_query($conn, "SELECT * FROM professors ORDER BY created_at DESC");
$classrooms   = mysqli_query($conn, "SELECT * FROM classrooms ORDER BY floor_number ASC");
$reservations = mysqli_query($conn, "SELECT reservations.*, classrooms.room_name FROM reservations LEFT JOIN classrooms ON reservations.classroom_id = classrooms.id ORDER BY reserve_date DESC");

// --- COUNT ---
$total_profs = mysqli_num_rows(mysqli_query($conn, "SELECT id FROM professors WHERE role='professor'"));
$total_rooms = mysqli_num_rows(mysqli_query($conn, "SELECT id FROM classrooms"));
$total_res   = mysqli_num_rows(mysqli_query($conn, "SELECT id FROM reservations"));
$occupied    = mysqli_num_rows(mysqli_query($conn, "SELECT id FROM classrooms WHERE status='Occupied'"));

$active_tab = isset($_GET['tab']) ? $_GET['tab'] : 'overview';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }

        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            background: #fff5f7;
            color: #3b1a2a;
            min-height: 100vh;
        }

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
        .topbar span { font-size: 13px; opacity: 0.85; margin-left: 10px; font-weight: 400; }
        .topbar a {
            text-decoration: none;
            color: #db2777;
            background: white;
            padding: 9px 20px;
            border-radius: 8px;
            font-weight: 700;
            font-size: 13px;
            transition: all 0.2s;
        }
        .topbar a:hover { background: #fce7f3; }

        .layout { display: flex; min-height: calc(100vh - 62px); }

        .sidebar {
            width: 220px;
            background: white;
            border-right: 1px solid #fce7f3;
            padding: 30px 0;
            flex-shrink: 0;
        }
        .sidebar a {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 13px 28px;
            text-decoration: none;
            color: #9d6478;
            font-weight: 600;
            font-size: 14px;
            transition: all 0.2s;
            border-left: 3px solid transparent;
        }
        .sidebar a:hover { background: #fff0f5; color: #db2777; }
        .sidebar a.active { background: #fff0f5; color: #db2777; border-left-color: #db2777; }
        .sidebar .icon { font-size: 18px; }

        .main { flex: 1; padding: 35px 40px; overflow-x: auto; }

        .page-title {
            font-size: 24px;
            font-weight: 800;
            color: #be185d;
            margin-bottom: 25px;
        }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
            gap: 20px;
            margin-bottom: 35px;
        }
        .stat-card {
            background: white;
            border-radius: 16px;
            padding: 24px 20px;
            border: 1px solid #fce7f3;
            box-shadow: 0 2px 10px rgba(219,39,119,0.04);
            transition: transform 0.2s;
        }
        .stat-card:hover { transform: translateY(-3px); }
        .stat-card .stat-icon { font-size: 30px; margin-bottom: 10px; }
        .stat-card .stat-num { font-size: 32px; font-weight: 800; color: #db2777; }
        .stat-card .stat-label { font-size: 13px; color: #9d6478; font-weight: 500; margin-top: 4px; }

        .table-wrap {
            background: white;
            border-radius: 16px;
            border: 1px solid #fce7f3;
            overflow: hidden;
            box-shadow: 0 2px 10px rgba(219,39,119,0.04);
        }
        .table-wrap table { width: 100%; border-collapse: collapse; font-size: 14px; }
        .table-wrap thead { background: #db2777; color: white; }
        .table-wrap th { padding: 14px 18px; text-align: left; font-weight: 700; font-size: 13px; }
        .table-wrap td { padding: 13px 18px; border-bottom: 1px solid #fff0f5; color: #4a2535; }
        .table-wrap tr:last-child td { border-bottom: none; }
        .table-wrap tr:hover td { background: #fff8fa; }

        .badge { display: inline-block; padding: 4px 12px; border-radius: 100px; font-size: 12px; font-weight: 700; }
        .badge-professor { background: #e0f2fe; color: #0369a1; }
        .badge-admin { background: #fce7f3; color: #db2777; }
        .badge-available { background: #dcfce7; color: #16a34a; }
        .badge-occupied { background: #ffe4e6; color: #e11d48; }

        .btn-delete {
            background: #ffe4e6;
            color: #e11d48;
            border: none;
            padding: 7px 14px;
            border-radius: 8px;
            font-size: 12px;
            font-weight: 700;
            cursor: pointer;
            text-decoration: none;
            display: inline-block;
            transition: all 0.2s;
        }
        .btn-delete:hover { background: #fecdd3; }

        .section-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 18px; }
        .empty { text-align: center; padding: 50px; color: #c084a0; font-size: 15px; }

        @media(max-width: 768px){
            .sidebar { display: none; }
            .main { padding: 20px 15px; }
            .topbar { padding: 15px 20px; }
        }
    </style>
</head>
<body>

<div class="topbar">
    <div>
        <h2>🏫 Admin Dashboard <span>Classroom Reservation System</span></h2>
    </div>
    <a href="logout.php">Logout</a>
</div>

<div class="layout">

    <div class="sidebar">
        <a href="admin.php?tab=overview" class="<?= $active_tab=='overview' ? 'active' : '' ?>">
            <span class="icon">📊</span> Overview
        </a>
        <a href="admin.php?tab=professors" class="<?= $active_tab=='professors' ? 'active' : '' ?>">
            <span class="icon">👨‍🏫</span> Professors
        </a>
        <a href="admin.php?tab=classrooms" class="<?= $active_tab=='classrooms' ? 'active' : '' ?>">
            <span class="icon">🚪</span> Classrooms
        </a>
        <a href="admin.php?tab=reservations" class="<?= $active_tab=='reservations' ? 'active' : '' ?>">
            <span class="icon">📅</span> Reservations
        </a>
    </div>

    <div class="main">

        <?php if($active_tab == 'overview'): ?>

        <div class="page-title">Welcome back, <?= $_SESSION['name'] ?>! 👋</div>

        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-icon">👨‍🏫</div>
                <div class="stat-num"><?= $total_profs ?></div>
                <div class="stat-label">Total Professors</div>
            </div>
            <div class="stat-card">
                <div class="stat-icon">🚪</div>
                <div class="stat-num"><?= $total_rooms ?></div>
                <div class="stat-label">Total Classrooms</div>
            </div>
            <div class="stat-card">
                <div class="stat-icon">📅</div>
                <div class="stat-num"><?= $total_res ?></div>
                <div class="stat-label">Total Reservations</div>
            </div>
            <div class="stat-card">
                <div class="stat-icon">🔴</div>
                <div class="stat-num"><?= $occupied ?></div>
                <div class="stat-label">Occupied Rooms</div>
            </div>
        </div>

        <div class="section-header">
            <div class="page-title" style="margin-bottom:0; font-size:18px;">Recent Reservations</div>
            <a href="admin.php?tab=reservations" style="color:#db2777; font-size:13px; font-weight:700; text-decoration:none;">View All →</a>
        </div>
        <div class="table-wrap">
            <table>
                <thead>
                    <tr>
                        <th>Reserved By</th>
                        <th>Room</th>
                        <th>Date</th>
                        <th>Time</th>
                    </tr>
                </thead>
                <tbody>
                <?php
                $recent = mysqli_query($conn, "SELECT reservations.*, classrooms.room_name FROM reservations LEFT JOIN classrooms ON reservations.classroom_id = classrooms.id ORDER BY reserve_date DESC LIMIT 5");
                if(mysqli_num_rows($recent) > 0):
                    while($r = mysqli_fetch_assoc($recent)): ?>
                    <tr>
                        <td><b><?= $r['reserved_by'] ?></b></td>
                        <td><?= $r['room_name'] ?></td>
                        <td><?= $r['reserve_date'] ?></td>
                        <td><?= date("h:i A", strtotime($r['start_time'])) ?> - <?= date("h:i A", strtotime($r['end_time'])) ?></td>
                    </tr>
                <?php endwhile; else: ?>
                    <tr><td colspan="4" class="empty">No reservations yet.</td></tr>
                <?php endif; ?>
                </tbody>
            </table>
        </div>

        <?php elseif($active_tab == 'professors'): ?>

        <div class="page-title">👨‍🏫 Professors</div>
        <div class="table-wrap">
            <table>
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Full Name</th>
                        <th>Email</th>
                        <th>Role</th>
                        <th>Date Registered</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                <?php
                mysqli_data_seek($professors, 0);
                if(mysqli_num_rows($professors) > 0):
                    while($p = mysqli_fetch_assoc($professors)): ?>
                    <tr>
                        <td><?= $p['id'] ?></td>
                        <td><b><?= $p['full_name'] ?></b></td>
                        <td><?= $p['email'] ?></td>
                        <td><span class="badge badge-<?= $p['role'] ?>"><?= ucfirst($p['role']) ?></span></td>
                        <td><?= date("M d, Y", strtotime($p['created_at'])) ?></td>
                        <td>
                            <?php if($p['role'] != 'admin'): ?>
                            <a href="admin.php?delete_prof=<?= $p['id'] ?>&tab=professors"
                               class="btn-delete"
                               onclick="return confirm('Delete <?= $p['full_name'] ?>?')">
                               🗑 Delete
                            </a>
                            <?php else: ?>
                            <span style="color:#c084a0; font-size:12px;">—</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endwhile; else: ?>
                    <tr><td colspan="6" class="empty">No professors found.</td></tr>
                <?php endif; ?>
                </tbody>
            </table>
        </div>

        <?php elseif($active_tab == 'classrooms'): ?>

        <div class="page-title">🚪 Classrooms</div>
        <div class="table-wrap">
            <table>
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Room Name</th>
                        <th>Floor</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                <?php
                mysqli_data_seek($classrooms, 0);
                if(mysqli_num_rows($classrooms) > 0):
                    while($c = mysqli_fetch_assoc($classrooms)): ?>
                    <tr>
                        <td><?= $c['id'] ?></td>
                        <td><b><?= $c['room_name'] ?></b></td>
                        <td>Floor <?= $c['floor_number'] ?></td>
                        <td>
                            <span class="badge badge-<?= strtolower($c['status']) ?>">
                                <?= $c['status'] ?>
                            </span>
                        </td>
                    </tr>
                <?php endwhile; else: ?>
                    <tr><td colspan="4" class="empty">No classrooms found.</td></tr>
                <?php endif; ?>
                </tbody>
            </table>
        </div>

        <?php elseif($active_tab == 'reservations'): ?>

        <div class="page-title">📅 Reservations</div>
        <div class="table-wrap">
            <table>
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Reserved By</th>
                        <th>Room</th>
                        <th>Date</th>
                        <th>Start Time</th>
                        <th>End Time</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                <?php
                mysqli_data_seek($reservations, 0);
                if(mysqli_num_rows($reservations) > 0):
                    while($r = mysqli_fetch_assoc($reservations)): ?>
                    <tr>
                        <td><?= $r['id'] ?></td>
                        <td><b><?= $r['reserved_by'] ?></b></td>
                        <td><?= $r['room_name'] ?></td>
                        <td><?= $r['reserve_date'] ?></td>
                        <td><?= date("h:i A", strtotime($r['start_time'])) ?></td>
                        <td><?= date("h:i A", strtotime($r['end_time'])) ?></td>
                        <td>
                            <a href="admin.php?delete_res=<?= $r['id'] ?>&tab=reservations"
                               class="btn-delete"
                               onclick="return confirm('Cancel this reservation?')">
                               🗑 Cancel
                            </a>
                        </td>
                    </tr>
                <?php endwhile; else: ?>
                    <tr><td colspan="7" class="empty">No reservations found.</td></tr>
                <?php endif; ?>
                </tbody>
            </table>
        </div>

        <?php endif; ?>

    </div>
</div>

</body>
</html>