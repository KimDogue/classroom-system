<?php
session_start();

if(!isset($_SESSION['name'])){
    header("Location:index.php");
    exit();
}

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

$initials = getInitials($_SESSION['name']);
$avatarColors = getAvatarColor($_SESSION['name']);
?>
<!DOCTYPE html>
<html>
<head>
<title>Dashboard</title>
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
<style>
* { margin: 0; padding: 0; box-sizing: border-box; }

@keyframes bgFloat {
    0%   { background-position: 0% 50%; }
    50%  { background-position: 100% 50%; }
    100% { background-position: 0% 50%; }
}
@keyframes floatBubble {
    0%,100% { transform: translateY(0) rotate(0deg); opacity: 0.35; }
    50%      { transform: translateY(-30px) rotate(8deg); opacity: 0.55; }
}
@keyframes floatBubble2 {
    0%,100% { transform: translateY(0) rotate(0deg); opacity: 0.2; }
    50%      { transform: translateY(25px) rotate(-6deg); opacity: 0.4; }
}
@keyframes pulse-ring {
    0%   { transform: scale(0.95); box-shadow: 0 0 0 0 rgba(219,39,119,0.35); }
    70%  { transform: scale(1);    box-shadow: 0 0 0 10px rgba(219,39,119,0); }
    100% { transform: scale(0.95); box-shadow: 0 0 0 0 rgba(219,39,119,0); }
}
@keyframes shimmer {
    0%   { background-position: -200% center; }
    100% { background-position: 200% center; }
}

body {
    font-family: 'Plus Jakarta Sans', sans-serif;
    background: linear-gradient(-45deg, #fff0f6, #fce7f3, #f5d0e8, #ffe4f0, #fdf2f8);
    background-size: 400% 400%;
    animation: bgFloat 12s ease infinite;
    color: #3b1a2a;
    min-height: 100vh;
    position: relative;
}

/* FLOATING BG BLOBS */
body::before {
    content: '';
    position: fixed;
    top: 10%; left: -80px;
    width: 320px; height: 320px;
    background: radial-gradient(circle, rgba(219,39,119,0.12), transparent 70%);
    border-radius: 50%;
    animation: floatBubble 8s ease-in-out infinite;
    pointer-events: none;
    z-index: 0;
}
body::after {
    content: '';
    position: fixed;
    bottom: 15%; right: -60px;
    width: 260px; height: 260px;
    background: radial-gradient(circle, rgba(244,114,182,0.14), transparent 70%);
    border-radius: 50%;
    animation: floatBubble2 10s ease-in-out infinite;
    pointer-events: none;
    z-index: 0;
}

/* TOPBAR */
.topbar {
    background: rgba(219,39,119,0.96);
    backdrop-filter: blur(12px);
    color: white;
    padding: 16px 40px;
    display: flex;
    justify-content: space-between;
    align-items: center;
    box-shadow: 0 4px 24px rgba(219,39,119,0.3);
    position: sticky;
    top: 0;
    z-index: 100;
    border-bottom: 1px solid rgba(255,255,255,0.15);
}
.topbar-left { display: flex; flex-direction: column; gap: 2px; }
.topbar h2 { font-size: 20px; font-weight: 800; letter-spacing: 0.3px; }
.topbar .subtitle { font-size: 13px; opacity: 0.75; font-weight: 400; }
.topbar-links { display: flex; gap: 10px; align-items: center; }
.topbar a {
    text-decoration: none;
    color: #db2777;
    background: white;
    padding: 9px 20px;
    border-radius: 10px;
    font-weight: 700;
    font-size: 13px;
    transition: all 0.2s;
    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
}
.topbar a:hover { background: #fce7f3; transform: translateY(-2px); box-shadow: 0 4px 14px rgba(0,0,0,0.15); }
.topbar a.outline {
    background: rgba(255,255,255,0.15);
    color: white;
    border: 1px solid rgba(255,255,255,0.4);
    box-shadow: none;
}
.topbar a.outline:hover { background: rgba(255,255,255,0.28); transform: translateY(-2px); }

/* HERO SECTION */
.hero {
    background-image:
        linear-gradient(135deg,
            rgba(236,72,153,0.82) 0%,
            rgba(219,39,119,0.78) 30%,
            rgba(190,24,93,0.72) 60%,
            rgba(157,23,77,0.88) 100%),
        url('building.png');
    background-size: cover;
    background-position: center 40%;
    color: white;
    padding: 65px 40px;
    position: relative;
    overflow: hidden;
}
/* Subtle shimmer overlay */
.hero::before {
    content: '';
    position: absolute;
    inset: 0;
    background: linear-gradient(
        180deg,
        rgba(255,255,255,0.04) 0%,
        transparent 40%,
        rgba(0,0,0,0.15) 100%
    );
    z-index: 0;
    pointer-events: none;
}
.hero-blob1 {
    position: absolute; top: -70px; right: -70px;
    width: 340px; height: 340px;
    background: rgba(255,255,255,0.07);
    border-radius: 50%;
    animation: floatBubble 7s ease-in-out infinite;
}
.hero-blob2 {
    position: absolute; bottom: -80px; left: 20%;
    width: 240px; height: 240px;
    background: rgba(255,255,255,0.04);
    border-radius: 50%;
    animation: floatBubble2 9s ease-in-out infinite;
}
.hero-blob3 {
    position: absolute; top: 20px; left: 40%;
    width: 140px; height: 140px;
    background: rgba(255,255,255,0.04);
    border-radius: 50%;
    animation: floatBubble 11s ease-in-out infinite;
}
/* Bottom fade into page */
.hero-fade {
    position: absolute;
    bottom: 0; left: 0; right: 0;
    height: 60px;
    background: linear-gradient(to bottom, transparent, rgba(157,23,77,0.35));
    pointer-events: none;
    z-index: 0;
}
.hero h1 { font-size: 34px; font-weight: 800; margin-bottom: 8px; position: relative; z-index: 1; text-shadow: 0 2px 10px rgba(0,0,0,0.1); }
.hero p  { font-size: 15px; opacity: 0.88; position: relative; z-index: 1; }
.hero-badge {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    background: rgba(255,255,255,0.2);
    border: 1px solid rgba(255,255,255,0.35);
    padding: 6px 16px;
    border-radius: 100px;
    font-size: 12px;
    font-weight: 700;
    margin-bottom: 14px;
    position: relative; z-index: 1;
    backdrop-filter: blur(6px);
    letter-spacing: 0.3px;
}

/* CONTAINER */
.container {
    max-width: 1100px;
    margin: 0 auto;
    padding: 40px 20px;
    position: relative;
    z-index: 1;
}

/* SECTION TITLE */
.section-title {
    font-size: 13px;
    font-weight: 700;
    color: #be185d;
    text-transform: uppercase;
    letter-spacing: 1.4px;
    margin-bottom: 16px;
    display: flex;
    align-items: center;
    gap: 8px;
}
.section-title::after {
    content: '';
    flex: 1;
    height: 1px;
    background: linear-gradient(90deg, #fbcfe8, transparent);
}

/* BUILDING GRID */
.building-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
    gap: 20px;
    margin-bottom: 44px;
}
.building-grid a { text-decoration: none; color: inherit; }

.building-card {
    background: rgba(255,255,255,0.85);
    backdrop-filter: blur(8px);
    padding: 32px 24px;
    border-radius: 24px;
    text-align: center;
    box-shadow: 0 4px 20px rgba(219,39,119,0.08), 0 1px 4px rgba(219,39,119,0.05);
    transition: all 0.35s cubic-bezier(0.34,1.56,0.64,1);
    border: 1px solid rgba(252,231,243,0.8);
    position: relative;
    overflow: hidden;
}
.building-card::before {
    content: '';
    position: absolute;
    top: 0; left: 0; right: 0;
    height: 3px;
    background: linear-gradient(90deg, #ec4899, #db2777, #f472b6);
    opacity: 0;
    transition: opacity 0.3s;
}
.building-card::after {
    content: '';
    position: absolute;
    inset: 0;
    background: linear-gradient(135deg, rgba(219,39,119,0.04), transparent);
    opacity: 0;
    transition: opacity 0.3s;
    border-radius: 24px;
}
.building-card:hover {
    transform: translateY(-10px) scale(1.02);
    box-shadow: 0 24px 50px rgba(219,39,119,0.18), 0 4px 12px rgba(219,39,119,0.1);
    border-color: rgba(219,39,119,0.3);
    background: white;
}
.building-card:hover::before { opacity: 1; }
.building-card:hover::after  { opacity: 1; }

.floor-icon {
    width: 66px; height: 66px;
    background: linear-gradient(135deg, #fce7f3, #fbcfe8);
    border-radius: 18px;
    display: flex; align-items: center; justify-content: center;
    font-size: 28px;
    margin: 0 auto 18px;
    transition: all 0.35s cubic-bezier(0.34,1.56,0.64,1);
    box-shadow: 0 4px 12px rgba(219,39,119,0.1);
    position: relative; z-index: 1;
}
.building-card:hover .floor-icon {
    background: linear-gradient(135deg, #ec4899, #9d174d);
    transform: scale(1.15) rotate(-4deg);
    box-shadow: 0 8px 20px rgba(219,39,119,0.3);
}

.building-card h2 {
    font-size: 19px;
    font-weight: 800;
    color: #be185d;
    margin-bottom: 5px;
    position: relative; z-index: 1;
}
.building-card p {
    font-size: 13px;
    color: #c084a0;
    font-weight: 500;
    position: relative; z-index: 1;
}
.building-card .room-count {
    display: inline-block;
    background: linear-gradient(135deg, #fce7f3, #fbcfe8);
    color: #db2777;
    padding: 5px 14px;
    border-radius: 100px;
    font-size: 12px;
    font-weight: 700;
    margin-top: 12px;
    position: relative; z-index: 1;
    transition: all 0.2s;
}
.building-card:hover .room-count {
    background: linear-gradient(135deg, #db2777, #be185d);
    color: white;
}

/* INFO CARDS */
.info-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 16px;
    margin-bottom: 44px;
}
.info-card {
    background: rgba(255,255,255,0.85);
    backdrop-filter: blur(8px);
    border-radius: 18px;
    padding: 22px;
    border: 1px solid rgba(252,231,243,0.8);
    display: flex;
    align-items: center;
    gap: 16px;
    box-shadow: 0 4px 16px rgba(219,39,119,0.07);
    transition: all 0.25s;
}
.info-card:hover { transform: translateY(-4px); box-shadow: 0 10px 28px rgba(219,39,119,0.13); }
.info-card .info-icon {
    width: 50px; height: 50px;
    background: linear-gradient(135deg, #fce7f3, #fbcfe8);
    border-radius: 14px;
    display: flex; align-items: center; justify-content: center;
    font-size: 24px;
    flex-shrink: 0;
    box-shadow: 0 3px 10px rgba(219,39,119,0.1);
}
.info-card .info-text .label { font-size: 11px; color: #c084a0; font-weight: 700; text-transform: uppercase; letter-spacing: 0.6px; }
.info-card .info-text .value { font-size: 17px; font-weight: 800; color: #be185d; margin-top: 3px; }

/* TIP BOX */
.tip-box {
    background: linear-gradient(135deg, rgba(252,231,243,0.9), rgba(255,240,245,0.9));
    backdrop-filter: blur(8px);
    border: 1px solid rgba(251,207,232,0.8);
    border-radius: 18px;
    padding: 22px 26px;
    display: flex;
    align-items: flex-start;
    gap: 14px;
    margin-bottom: 10px;
    box-shadow: 0 4px 16px rgba(219,39,119,0.06);
}
.tip-box .tip-icon { font-size: 24px; flex-shrink: 0; margin-top: 1px; }
.tip-box .tip-title { font-size: 14px; font-weight: 800; color: #be185d; margin-bottom: 5px; }
.tip-box .tip-text { font-size: 13px; color: #9d6478; line-height: 1.7; }

/* AVATAR */
.avatar-circle {
    width: 46px; height: 46px;
    border-radius: 14px;
    display: flex; align-items: center; justify-content: center;
    font-size: 17px;
    font-weight: 800;
    color: white;
    flex-shrink: 0;
    border: 2px solid rgba(255,255,255,0.5);
    letter-spacing: 0.5px;
    box-shadow: 0 3px 10px rgba(0,0,0,0.2);
    animation: pulse-ring 2.5s ease-in-out infinite;
}
.hero-avatar {
    width: 76px; height: 76px;
    border-radius: 22px;
    display: flex; align-items: center; justify-content: center;
    font-size: 28px;
    font-weight: 800;
    color: white;
    flex-shrink: 0;
    border: 3px solid rgba(255,255,255,0.5);
    backdrop-filter: blur(8px);
    letter-spacing: 1px;
    box-shadow: 0 8px 24px rgba(0,0,0,0.2);
    background: rgba(255,255,255,0.2) !important;
}
.hero-inner { display: flex; align-items: center; gap: 24px; position: relative; z-index: 1; }
.hero-text .hero-badge { margin-bottom: 10px; }
.hero-text h1 { margin-bottom: 6px; }

/* QUICK LINKS */
.quick-link-card {
    background: rgba(255,255,255,0.85);
    backdrop-filter: blur(8px);
    border: 1px solid rgba(252,231,243,0.8);
    border-radius: 16px;
    padding: 18px 24px;
    display: flex;
    align-items: center;
    gap: 12px;
    box-shadow: 0 4px 14px rgba(219,39,119,0.07);
    transition: all 0.3s cubic-bezier(0.34,1.56,0.64,1);
    cursor: pointer;
}
.quick-link-card:hover {
    border-color: #db2777;
    transform: translateY(-5px) scale(1.02);
    box-shadow: 0 14px 30px rgba(219,39,119,0.16);
    background: white;
}
.quick-link-card .ql-icon { font-size: 22px; }
.quick-link-card .ql-title { font-size: 13px; font-weight: 800; color: #be185d; }
.quick-link-card .ql-sub   { font-size: 11px; color: #c084a0; margin-top: 2px; }

@media(max-width: 600px){
    .topbar { padding: 15px 20px; flex-direction: column; gap: 12px; text-align: center; }
    .topbar-links { flex-wrap: wrap; justify-content: center; }
    .hero { padding: 40px 20px; background-position: center 30%; }
    .hero h1 { font-size: 26px; }
    .container { padding: 25px 15px; }
    .building-grid { grid-template-columns: repeat(2, 1fr); }
}
</style>
</head>
<body>

<!-- TOPBAR -->
<div class="topbar">
    <div class="topbar-left">
        <h2>🏫 Classroom System</h2>
        <span class="subtitle">Main Building</span>
    </div>
    <div class="topbar-links">
        <div class="avatar-circle" style="background: linear-gradient(135deg, <?php echo $avatarColors[0]; ?>, <?php echo $avatarColors[1]; ?>);">
            <?php echo $initials; ?>
        </div>
        <a href="profile.php" class="outline">👤 Profile</a>
        <a href="contact.php" class="outline">💬 Support</a>
        <a href="logout.php">Logout</a>
    </div>
</div>

<!-- HERO -->
<div class="hero">
    <div class="hero-blob1"></div>
    <div class="hero-blob2"></div>
    <div class="hero-blob3"></div>
    <div class="hero-fade"></div>
    <div class="hero-inner">
        <div class="hero-avatar">
            <?php echo $initials; ?>
        </div>
        <div class="hero-text">
            <div class="hero-badge">👨‍🏫 Professor Portal</div>
            <h1>Good day, <?php echo $_SESSION['name']; ?>!</h1>
            <p>Select a floor below to view and reserve available classrooms.</p>
        </div>
    </div>
</div>

<div class="container">

    <!-- QUICK INFO -->
    <div class="info-grid">
        <div class="info-card">
            <div class="info-icon">🏢</div>
            <div class="info-text">
                <div class="label">Building</div>
                <div class="value">Main Building</div>
            </div>
        </div>
        <div class="info-card">
            <div class="info-icon">🚪</div>
            <div class="info-text">
                <div class="label">Total Floors</div>
                <div class="value">4 Floors</div>
            </div>
        </div>
        <div class="info-card">
            <div class="info-icon">📋</div>
            <div class="info-text">
                <div class="label">Total Rooms</div>
                <div class="value">40 Rooms</div>
            </div>
        </div>
    </div>

    <!-- FLOOR SELECTION -->
    <div class="section-title">Select a Floor</div>
    <div class="building-grid">

        <a href="rooms.php?floor=1">
            <div class="building-card">
                <div class="floor-icon">1️⃣</div>
                <h2>Floor 1</h2>
                <p>Ground Level</p>
                <span class="room-count">10 Rooms</span>
            </div>
        </a>

        <a href="rooms.php?floor=2">
            <div class="building-card">
                <div class="floor-icon">2️⃣</div>
                <h2>Floor 2</h2>
                <p>Second Level</p>
                <span class="room-count">10 Rooms</span>
            </div>
        </a>

        <a href="rooms.php?floor=3">
            <div class="building-card">
                <div class="floor-icon">3️⃣</div>
                <h2>Floor 3</h2>
                <p>Third Level</p>
                <span class="room-count">10 Rooms</span>
            </div>
        </a>

        <a href="rooms.php?floor=4">
            <div class="building-card">
                <div class="floor-icon">4️⃣</div>
                <h2>Floor 4</h2>
                <p>Fourth Level</p>
                <span class="room-count">10 Rooms</span>
            </div>
        </a>

    </div>

    <!-- QUICK LINKS -->
    <div class="section-title">Quick Links</div>
    <div style="display:flex; gap:14px; flex-wrap:wrap; margin-bottom:44px;">
        <a href="profile.php" style="text-decoration:none;">
            <div class="quick-link-card">
                <span class="ql-icon">👤</span>
                <div>
                    <div class="ql-title">My Profile</div>
                    <div class="ql-sub">View & edit account</div>
                </div>
            </div>
        </a>
        <a href="contact.php" style="text-decoration:none;">
            <div class="quick-link-card">
                <span class="ql-icon">💬</span>
                <div>
                    <div class="ql-title">Contact & Support</div>
                    <div class="ql-sub">Send a message to admin</div>
                </div>
            </div>
        </a>
    </div>

    <!-- TIP -->
    <div class="tip-box">
        <div class="tip-icon">💡</div>
        <div>
            <div class="tip-title">How to Reserve</div>
            <div class="tip-text">Click a floor to see all rooms. Choose an <b>Available</b> room, set your date and time, then click <b>Reserve</b>. You can cancel your own reservations anytime.</div>
        </div>
    </div>

</div>
</body>
</html>