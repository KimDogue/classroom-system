<?php

session_start();

include 'db.php';

$email = trim($_POST['email']);
$password = trim($_POST['password']);

$query = mysqli_query($conn,
"SELECT * FROM professors
WHERE email='$email'
AND password='$password'");

if(mysqli_num_rows($query) > 0){

    $row = mysqli_fetch_assoc($query);

    $_SESSION['name']         = $row['full_name'];
    $_SESSION['professor_id'] = $row['id'];
    $_SESSION['role']         = $row['role']; // ← FIXED: idagdag ang role sa session

    if($row['role'] == 'admin'){
        header("Location: admin.php");
        exit();
    } else {
        header("Location: dashboard.php");
        exit();
    }

} else {

    echo "
    <script>
        alert('Wrong Email or Password');
        window.location='index.php';
    </script>
    ";

}

?>