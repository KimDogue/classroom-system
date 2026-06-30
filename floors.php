<?php

session_start();

include 'db.php';

$building = $_GET['building'];

?>

<!DOCTYPE html>
<html>

<head>

<title>Floors</title>

<link rel="stylesheet"
href="style.css">

</head>

<body>

<div class="topbar">

<h2><?php echo $building; ?></h2>

<a href="dashboard.php">
Back
</a>

</div>

<div class="container">

<div class="building-grid">

<a href="rooms.php?building=<?php echo $building; ?>&floor=1">

<div class="building-card">

<h2>Floor 1</h2>

</div>

</a>

<a href="rooms.php?building=<?php echo $building; ?>&floor=2">

<div class="building-card">

<h2>Floor 2</h2>

</div>

</a>

</div>

</div>

</body>
</html>