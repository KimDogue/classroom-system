<?php

include 'db.php';

$full_name = $_POST['full_name'];
$email = $_POST['email'];
$password = $_POST['password'];

$check =
mysqli_query($conn,
"SELECT * FROM professors
WHERE email='$email'");

if(mysqli_num_rows($check) > 0){

    echo "Email already exists.";

}else{

    mysqli_query($conn,

    "INSERT INTO professors
    (full_name,email,password)

    VALUES

    ('$full_name','$email','$password')"

    );

    echo "

    <script>

    alert('Registration Successful');

    window.location='index.php';

    </script>

    ";

}

?>