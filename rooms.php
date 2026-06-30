<?php
session_start();
include 'db.php';

$floor = $_GET['floor'];
$user_id = $_SESSION['professor_id'];

$rooms = mysqli_query($conn,
"SELECT classrooms.*,
reservations.reserved_by,
reservations.professor_id,
reservations.reserve_date,
reservations.start_time,
reservations.end_time
FROM classrooms
LEFT JOIN reservations ON classrooms.id = reservations.classroom_id
WHERE floor_number='$floor'"
);
?>
<!DOCTYPE html>
<html>
<head>
<title>Floor <?php echo $floor; ?> Rooms</title>
<meta name="viewport" content="width=device-width, initial-scale=1.0">
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

/* HERO */
.hero {
    background: linear-gradient(135deg, #db2777 0%, #be185d 60%, #9d174d 100%);
    color: white;
    padding: 35px 40px;
    position: relative;
    overflow: hidden;
}
.hero::before {
    content: '';
    position: absolute;
    top: -50px; right: -50px;
    width: 200px; height: 200px;
    background: rgba(255,255,255,0.06);
    border-radius: 50%;
}
.hero h1 { font-size: 26px; font-weight: 800; position: relative; z-index: 1; margin-bottom: 6px; }
.hero p { font-size: 14px; opacity: 0.82; position: relative; z-index: 1; }

/* SEARCH */
.search-wrap {
    max-width: 1100px;
    margin: 0 auto;
    padding: 28px 20px 0;
}
.search-box {
    display: flex;
    align-items: center;
    background: white;
    border: 1px solid #fce7f3;
    border-radius: 14px;
    padding: 0 18px;
    box-shadow: 0 2px 10px rgba(219,39,119,0.06);
    gap: 10px;
}
.search-box span { font-size: 18px; }
.search-box input {
    flex: 1;
    border: none;
    outline: none;
    padding: 14px 0;
    font-size: 15px;
    font-family: 'Plus Jakarta Sans', sans-serif;
    color: #3b1a2a;
    background: transparent;
}
.search-box input::placeholder { color: #c084a0; }

/* FILTER TABS */
.filter-wrap {
    max-width: 1100px;
    margin: 0 auto;
    padding: 18px 20px 0;
    display: flex;
    gap: 10px;
    flex-wrap: wrap;
}
.filter-btn {
    padding: 8px 18px;
    border-radius: 100px;
    border: 1px solid #fce7f3;
    background: white;
    font-size: 13px;
    font-weight: 600;
    color: #c084a0;
    cursor: pointer;
    transition: all 0.2s;
    font-family: 'Plus Jakarta Sans', sans-serif;
}
.filter-btn:hover { border-color: #db2777; color: #db2777; }
.filter-btn.active-all { background: #db2777; color: white; border-color: #db2777; }
.filter-btn.active-available { background: #dcfce7; color: #16a34a; border-color: #86efac; }
.filter-btn.active-occupied { background: #ffe4e6; color: #e11d48; border-color: #fca5a5; }

/* CONTAINER */
.container {
    max-width: 1100px;
    margin: 0 auto;
    padding: 24px 20px 40px;
}

/* SECTION LABEL */
.section-label {
    font-size: 12px;
    font-weight: 700;
    color: #c084a0;
    text-transform: uppercase;
    letter-spacing: 1.2px;
    margin-bottom: 16px;
}

/* ROOM GRID */
.room-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
    gap: 20px;
}

/* ROOM CARD */
.room-card {
    background: white;
    border-radius: 20px;
    border: 1px solid #fce7f3;
    box-shadow: 0 2px 12px rgba(219,39,119,0.05);
    transition: all 0.3s ease;
    overflow: hidden;
    position: relative;
}
.room-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 16px 35px rgba(219,39,119,0.13);
    border-color: #f9a8d4;
}

.card-header {
    padding: 20px 22px 16px;
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    border-bottom: 1px solid #fff0f5;
}
.room-name { font-size: 17px; font-weight: 800; color: #be185d; }
.room-floor { font-size: 12px; color: #c084a0; font-weight: 500; margin-top: 3px; }

.card-body { padding: 16px 22px; }
.card-footer { padding: 0 22px 20px; }

/* STATUS BADGE */
.status-badge {
    display: inline-flex;
    align-items: center;
    gap: 5px;
    padding: 5px 12px;
    border-radius: 100px;
    font-size: 12px;
    font-weight: 700;
}
.status-badge.available { background: #dcfce7; color: #16a34a; }
.status-badge.occupied  { background: #ffe4e6; color: #e11d48; }
.status-dot { width: 7px; height: 7px; border-radius: 50%; }
.available .status-dot { background: #16a34a; }
.occupied  .status-dot { background: #e11d48; animation: pulse 1.5s infinite; }
@keyframes pulse {
    0%,100% { opacity: 1; }
    50% { opacity: 0.4; }
}

/* AIRCON TAG */
.aircon-tag {
    display: inline-flex;
    align-items: center;
    gap: 5px;
    background: #e0f2fe;
    color: #0369a1;
    padding: 4px 10px;
    border-radius: 100px;
    font-size: 11px;
    font-weight: 700;
    margin-top: 10px;
}

/* INFO ROW */
.info-row {
    display: flex;
    align-items: center;
    gap: 8px;
    margin-bottom: 8px;
    font-size: 13px;
    color: #6b5a60;
}
.info-row .info-icon { font-size: 15px; flex-shrink: 0; }
.info-row b { color: #be185d; font-weight: 700; }

/* FORM INPUTS */
.reserve-form { display: flex; flex-direction: column; gap: 10px; }
.input-group { display: flex; flex-direction: column; gap: 4px; }
.input-label { font-size: 11px; font-weight: 700; color: #c084a0; text-transform: uppercase; letter-spacing: 0.8px; }
.reserve-form input {
    width: 100%;
    padding: 10px 14px;
    border: 1px solid #fce7f3;
    border-radius: 10px;
    font-size: 14px;
    font-family: 'Plus Jakarta Sans', sans-serif;
    color: #3b1a2a;
    background: #fff8fa;
    transition: all 0.2s;
}
.reserve-form input:focus {
    outline: none;
    border-color: #db2777;
    background: white;
    box-shadow: 0 0 0 3px rgba(219,39,119,0.1);
}
.time-row { display: grid; grid-template-columns: 1fr 1fr; gap: 10px; }

/* BUTTONS */
.reserve-btn {
    width: 100%;
    padding: 12px;
    background: linear-gradient(135deg, #db2777, #be185d);
    color: white;
    border: none;
    border-radius: 12px;
    font-size: 14px;
    font-weight: 700;
    cursor: pointer;
    font-family: 'Plus Jakarta Sans', sans-serif;
    transition: all 0.2s;
    margin-top: 4px;
    box-shadow: 0 4px 14px rgba(219,39,119,0.3);
}
.reserve-btn:hover {
    transform: translateY(-1px);
    box-shadow: 0 6px 20px rgba(219,39,119,0.4);
}

.edit-btn {
    width: 100%;
    padding: 12px;
    border: none;
    border-radius: 12px;
    font-size: 14px;
    font-weight: 700;
    cursor: pointer;
    font-family: 'Plus Jakarta Sans', sans-serif;
    transition: all 0.2s;
    background: #e0f2fe;
    color: #0369a1;
    border: 1px solid #bae6fd;
    margin-bottom: 8px;
}
.edit-btn:hover { background: #bae6fd; color: #0284c7; }

.cancel-btn {
    width: 100%;
    padding: 12px;
    border: none;
    border-radius: 12px;
    font-size: 14px;
    font-weight: 700;
    cursor: pointer;
    font-family: 'Plus Jakarta Sans', sans-serif;
    transition: all 0.2s;
    background: #fff0f5;
    color: #db2777;
    border: 1px solid #fce7f3;
}
.cancel-btn:hover { background: #ffe4e6; color: #e11d48; border-color: #fca5a5; }

.occupied-btn {
    width: 100%;
    padding: 12px;
    background: #f1f5f9;
    color: #94a3b8;
    border: none;
    border-radius: 12px;
    font-size: 14px;
    font-weight: 700;
    cursor: not-allowed;
    font-family: 'Plus Jakarta Sans', sans-serif;
}

/* EMPTY STATE */
.empty-state {
    text-align: center;
    padding: 70px 20px;
    color: #c084a0;
    grid-column: 1 / -1;
}
.empty-state .empty-icon { font-size: 50px; margin-bottom: 14px; }
.empty-state p { font-size: 15px; font-weight: 500; }

@media(max-width: 600px){
    .topbar { padding: 15px 20px; }
    .hero { padding: 25px 20px; }
    .room-grid { grid-template-columns: 1fr; }
    .time-row { grid-template-columns: 1fr; }
}
</style>
</head>
<body>

<!-- TOPBAR -->
<div class="topbar">
    <h2>🚪 Floor <?php echo $floor; ?> Rooms</h2>
    <a href="dashboard.php">← Back</a>
</div>

<!-- HERO -->
<div class="hero">
    <h1>Floor <?php echo $floor; ?> — Main Building</h1>
    <p>Choose an available room to make a reservation.</p>
</div>

<!-- SEARCH -->
<div class="search-wrap">
    <div class="search-box">
        <span>🔍</span>
        <input type="text" id="searchInput" placeholder="Search room name...">
    </div>
</div>

<!-- FILTER TABS -->
<div class="filter-wrap">
    <button class="filter-btn active-all" onclick="filterRooms('all', this)">🏠 All Rooms</button>
    <button class="filter-btn" onclick="filterRooms('available', this)">✅ Available</button>
    <button class="filter-btn" onclick="filterRooms('occupied', this)">🔴 Occupied</button>
</div>

<div class="container">
    <div class="section-label">Rooms on this floor</div>
    <div class="room-grid" id="roomGrid">

    <?php
    $aircon_rooms = array('Room 1', 'Room 3', 'Room 7');
    while($room = mysqli_fetch_assoc($rooms)):
        $status_class = strtolower($room['status']);
    ?>

        <div class="room-card" data-status="<?= $status_class ?>" data-name="<?= strtolower($room['room_name']) ?>">

            <div class="card-header">
                <div>
                    <div class="room-name"><?= $room['room_name'] ?></div>
                    <div class="room-floor">Floor <?= $floor ?></div>
                    <?php if(in_array($room['room_name'], $aircon_rooms)): ?>
                    <span class="aircon-tag">❄️ Air-conditioned</span>
                    <?php endif; ?>
                </div>
                <span class="status-badge <?= $status_class ?>">
                    <span class="status-dot"></span>
                    <?= $room['status'] ?>
                </span>
            </div>

            <div class="card-body">
                <?php if($room['reserved_by']): ?>
                <div class="info-row">
                    <span class="info-icon">👤</span>
                    <span>Reserved by <b><?= $room['reserved_by'] ?></b></span>
                </div>
                <div class="info-row">
                    <span class="info-icon">📅</span>
                    <span>Date: <b><?= $room['reserve_date'] ?></b></span>
                </div>
                <div class="info-row">
                    <span class="info-icon">🕐</span>
                    <span>Time: <b><?= date("h:i A", strtotime($room['start_time'])) ?> – <?= date("h:i A", strtotime($room['end_time'])) ?></b></span>
                </div>
                <?php else: ?>
                <div class="info-row">
                    <span class="info-icon">✅</span>
                    <span>This room is currently <b>available</b>.</span>
                </div>
                <?php endif; ?>
            </div>

            <div class="card-footer">
                <?php if($room['status'] == 'Available'): ?>
                <form action="reserve.php" method="POST" class="reserve-form">
                    <input type="hidden" name="room_id" value="<?= $room['id'] ?>">
                    <div class="input-group">
                        <label class="input-label">📅 Reserve Date</label>
                        <input type="date" name="reserve_date" required>
                    </div>
                    <div class="time-row">
                        <div class="input-group">
                            <label class="input-label">🕐 Start Time</label>
                            <input type="time" name="start_time" required>
                        </div>
                        <div class="input-group">
                            <label class="input-label">🕑 End Time</label>
                            <input type="time" name="end_time" required>
                        </div>
                    </div>
                    <button type="submit" class="reserve-btn">Reserve This Room</button>
                </form>

                <?php elseif($room['professor_id'] == $user_id): ?>
                <?php
                $res_row = mysqli_fetch_assoc(mysqli_query($conn,
                    "SELECT id FROM reservations WHERE classroom_id='".$room['id']."' AND professor_id='$user_id' LIMIT 1"
                ));
                ?>
                <a href="edit_reservation.php?id=<?= $res_row['id'] ?>">
                    <button class="edit-btn">✏️ Edit Reservation</button>
                </a>
                <a href="cancel.php?id=<?= $room['id'] ?>">
                    <button class="cancel-btn">✕ Cancel My Reservation</button>
                </a>

                <?php else: ?>
                <button class="occupied-btn" disabled>🔴 Room is Occupied</button>
                <?php endif; ?>
            </div>

        </div>

    <?php endwhile; ?>

    </div>
</div>

<script>
// Search
document.getElementById('searchInput').addEventListener('keyup', function(){
    const filter = this.value.toLowerCase();
    document.querySelectorAll('.room-card').forEach(card => {
        const name = card.getAttribute('data-name');
        card.style.display = name.includes(filter) ? '' : 'none';
    });
});

// Filter tabs
function filterRooms(type, btn){
    // Reset all buttons
    document.querySelectorAll('.filter-btn').forEach(b => {
        b.className = 'filter-btn';
    });
    // Set active class
    if(type === 'all') btn.classList.add('active-all');
    else if(type === 'available') btn.classList.add('active-available');
    else if(type === 'occupied') btn.classList.add('active-occupied');

    document.querySelectorAll('.room-card').forEach(card => {
        const status = card.getAttribute('data-status');
        if(type === 'all' || status === type){
            card.style.display = '';
        } else {
            card.style.display = 'none';
        }
    });
}
</script>

</body>
</html>