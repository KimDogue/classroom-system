<?php

session_start();

include 'db.php';

$room_id = $_POST['room_id'];

$date = $_POST['reserve_date'];

$start = $_POST['start_time'];

$end = $_POST['end_time'];

$professor_id =
$_SESSION['professor_id'];

$name =
$_SESSION['name'];

$check = mysqli_query($conn,

"SELECT * FROM reservations

WHERE classroom_id='$room_id'

AND reserve_date='$date'

AND (

(start_time <= '$start'
AND end_time > '$start')

OR

(start_time < '$end'
AND end_time >= '$end')

)"

);

if(mysqli_num_rows($check) > 0){

    echo "

    <script>

    alert('Room already reserved');

    window.history.back();

    </script>

    ";

}else{

mysqli_query($conn,

"INSERT INTO reservations

(professor_id,
classroom_id,
reserved_by,
reserve_date,
start_time,
end_time)

VALUES

('$professor_id',
'$room_id',
'$name',
'$date',
'$start',
'$end')"

);

mysqli_query($conn,

"UPDATE classrooms

SET status='Occupied'

WHERE id='$room_id'");

header("Location:dashboard.php");

}

?>