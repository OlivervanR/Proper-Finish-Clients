<?php 
session_start();
$errors = array();
include "../../includes/header.php";

if(isset($_POST['submit'])) {
    $correctPassword = 'drumsander'; // Change this to your desired password

    // The entered password
    $enteredPassword = $_POST['password'];

    // Check if the entered password is correct
    if ($enteredPassword == $correctPassword) {
        $_SESSION['loggedin'] = true;
        header('Location:main.php');
        exit();
    }
    else {
        $errors['password'] = true;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="./styles/main.css?">
    <title>Login</title>
</head>
<body>
    <h2>Login</h2>
    <form method="post">
        <label for="password">Password:</label>
        <input type="password" id="password" name="password" required>
        <span class="error <?= !isset($errors['password']) ? 'hidden' : '' ?>">That password is incorrect.</span>
        <button type="submit" name="submit">Login</button>
    </form>
</body>
</html>