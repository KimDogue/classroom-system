<!DOCTYPE html>
<html>

<head>

<title>Professor Sign Up</title>

<meta name="viewport"
content="width=device-width, initial-scale=1.0">

<link rel="stylesheet"
href="style.css">

</head>

<body>

<div class="login-container">

<h1>Create Account</h1>

<form action="register.php" method="POST">

<input
type="text"
name="full_name"
placeholder="Full Name"
required>

<input
type="email"
name="email"
placeholder="Email"
required>

<input
type="password"
name="password"
placeholder="Password"
required>

<button type="submit">

Sign Up

</button>

</form>

<br>

<center>

<a href="index.php">

Already have account?
Login

</a>

</center>

</div>

</body>
</html>