<?php

session_start();

include 'db.php';

$id = $_GET['id'];

$user_id =
$_SESSION['professor_id'];

$check = mysqli_query($conn,

"SELECT * FROM reservations

WHERE classroom_id='$id'

AND professor_id='$user_id'");

if(mysqli_num_rows($check) > 0){

    mysqli_query($conn,

    "DELETE FROM reservations

    WHERE classroom_id='$id'

    AND professor_id='$user_id'");

    mysqli_query($conn,

    "UPDATE classrooms

    SET status='Available'

    WHERE id='$id'");

}

header("Location:dashboard.php");

?>