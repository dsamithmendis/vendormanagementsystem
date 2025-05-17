<?php session_start(); ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>VENDOR MANAGEMENT SYSTEM - LOGIN</title>
    <link rel="stylesheet" href="frontend/styles.css">
</head>
<body>

<header>
    <h1>VENDOR MANAGEMENT SYSTEM</h1>
</header>

<main>
    <div class="container">
        <h2>LOGIN</h2>
        <?php
        if (isset($_SESSION["login_error"])) {
            echo '<div class="error">' . $_SESSION["login_error"] . '</div>';
            unset($_SESSION["login_error"]);
        }
        ?>
        <form action="login.php" method="POST">
            <div class="form-group">
                <label for="username">USERNAME</label>
                <input type="text" class="form-control" name="username" id="username" placeholder="Enter Name" required>
            </div>

            <div class="form-group">
                <label for="password">PASSWORD</label>
                <input type="password" class="form-control" name="password" id="password" placeholder="Enter Password" maxlength="8" required>
            </div>

            <button type="submit" class="btn btn-primary" name="login">Log in</button>
            <button type="reset" class="btn btn-danger">Reset</button>
            <button type="button" class="btn btn-secondary" onclick="window.location.href='index.php'">Back</button>
        </form>
    </div>
</main>

<footer>
    <p>&copy; 2024 OUSL Student: Mr. D.S.Mendis. All rights reserved.</p>
</footer>

</body>
</html>
