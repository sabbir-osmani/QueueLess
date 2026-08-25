<?php
    session_start();
    require 'includes/db.php';

    $errorMsg = "";

    if (isset($_POST['email'])) {
        $email = $_POST['email'];
        $pass = $_POST['pass'];

        // Prepared statement: the ? is a placeholder, and bind_param
        // safely inserts the email value instead of pasting it into the SQL text.
        $statement = mysqli_prepare($conn, "SELECT * FROM students WHERE email = ?");
        mysqli_stmt_bind_param($statement, "s", $email);
        mysqli_stmt_execute($statement);
        $result = mysqli_stmt_get_result($statement);

        if (mysqli_num_rows($result) > 0) {
            $row = mysqli_fetch_assoc($result);

            if (password_verify($pass, $row['pass'])) {
                $_SESSION['student_id'] = $row['id'];
                $_SESSION['student_name'] = $row['name'];

                header("Location: dashboard.php");
                exit();
            } else {
                $errorMsg = "Wrong password";
            }
        } else {
            $errorMsg = "No account found with this email";
        }
    }
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - QueueLess</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="css/common.css">
    <link rel="stylesheet" href="css/auth.css">
    <link rel="icon" href="images/favicon.png" type="image/png">
</head>
<body>

    <!-- top navbar (same as home page) -->
    <header class="navbar">
        <a href="index.php" class="nav-left" style="text-decoration:none;">
            <img src="images/queueless-icon.png" alt="QueueLess Icon" class="logo">
            <img src="images/queueless-text.png" alt="QueueLess Text" class="logo-text">
        </a>
        <div class="nav-right">
            <a href="register.php" class="btn btn-outline">Register</a>
        </div>
    </header>

    <!-- login card -->
    <section class="auth-section">
        <div class="auth-card">
            <h1>Welcome Back</h1>
            <p class="auth-sub">Login to check your queue status</p>

            <?php if ($errorMsg != "") { ?>
                <p class="form-error"><?php echo htmlspecialchars($errorMsg); ?></p>
            <?php } ?>

            <form action="" method="POST">

                <div class="form-group">
                    <label for="email">Email Address</label>
                    <input type="email" id="email" name="email" placeholder="you@example.com" required>
                </div>

                <div class="form-group">
                    <label for="pass">Password</label>
                    <input type="password" id="pass" name="pass" placeholder="Enter your password" required>
                </div>

                <button type="submit" class="btn btn-accent btn-full">Login</button>

            </form>

            <p class="auth-footer-text">Don't have an account? <a href="register.php">Register here</a></p>
        </div>
    </section>

    <!-- footer -->
    <footer class="footer">
        <p>Green University of Bangladesh &mdash; Department of CSE</p>
        <p>QueueLess Project | Web Programming Lab</p>
    </footer>

</body>
</html>
