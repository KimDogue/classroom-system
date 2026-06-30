<?php
session_start();
include 'db.php';

$email = trim($_POST['email']);
$password = trim($_POST['password']);

if(!empty($email) && !empty($password)){

    $query = mysqli_query($conn, "SELECT * FROM professors WHERE email='$email' AND password='$password'");

    if (!$query) {
        die("Database Query Failed: " . mysqli_error($conn));
    }

    if(mysqli_num_rows($query) > 0){
        $row = mysqli_fetch_assoc($query);

        // I-save lahat ng session data kasama ang ROLE
        $_SESSION['name']         = $row['full_name'];
        $_SESSION['professor_id'] = $row['id'];
        $_SESSION['role']         = $row['role']; // ← ITO ANG KULANG DATI

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
            alert('Wrong Email or Password. Please try again.');
            window.location='index.php';
        </script>
        ";
    }

} else {
    echo "
    <script>
        alert('Please fill up all fields.');
        window.location='index.php';
    </script>
    ";
}
?>