<?php
    session_start();
    require 'includes/db.php';

    $errorMsg = "";

    if (isset($_POST['name'])) {
        $name = $_POST['name'];
        $email = $_POST['email'];
        $pass = $_POST['pass'];
        $confirmPass = $_POST['confirmPass'];

        if ($pass !== $confirmPass) {
            $errorMsg = "Passwords do not match";
        } elseif (strlen($pass) < 6) {
            $errorMsg = "Password must be at least 6 characters";
        } else {
            // check if email already used
            $checkQuery = "SELECT id FROM students WHERE email = '$email'";
            $checkResult = mysqli_query($conn, $checkQuery);

            if (mysqli_num_rows($checkResult) > 0) {
                $errorMsg = "This email is already registered";
            } else {
                $hashedPass = password_hash($pass, PASSWORD_DEFAULT);
                $insertQuery = "INSERT INTO students (name, email, pass, created_at) VALUES ('$name', '$email', '$hashedPass', NOW())";
                mysqli_query($conn, $insertQuery);

                $newId = mysqli_insert_id($conn);
                $_SESSION['student_id'] = $newId;
                $_SESSION['student_name'] = $name;

                header("Location: dashboard.php");
                exit();
            }
        }
    }
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - QueueLess</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="icon" href="images/favicon.png" type="image/png">
    <link rel="stylesheet" href="css/common.css">
    <link rel="stylesheet" href="css/auth.css">
</head>
<body>

    <!-- top navbar (same as home page) -->
    <header class="navbar">
        <a href="index.php" class="nav-left" style="text-decoration:none;">
            <img src="images/queueless-icon.png" alt="QueueLess Icon" class="logo">
            <img src="images/queueless-text.png" alt="QueueLess Text" class="logo-text">
        </a>
        <div class="nav-right">
            <a href="login.php" class="btn btn-outline">Login</a>
        </div>
    </header>

    <!-- register card -->
    <section class="auth-section">
        <div class="auth-card">
            <h1>Create Your Account</h1>
            <p class="auth-sub">Register to start taking digital tokens</p>

            <?php if ($errorMsg != "") { ?>
                <p class="form-error"><?php echo $errorMsg; ?></p>
            <?php } ?>

            <form action="" method="POST">

                <div class="form-group">
                    <label for="name">Full Name</label>
                    <input type="text" id="name" name="name" placeholder="Enter your full name" required>
                </div>

                <div class="form-group">
                    <label for="email">Email Address</label>
                    <input type="email" id="email" name="email" placeholder="you@example.com" required>
                </div>

                <div class="form-group">
                    <label for="pass">Password</label>
                    <input type="password" id="pass" name="pass" placeholder="At least 6 characters" required>
                </div>

                <div class="form-group">
                    <label for="confirmPass">Confirm Password</label>
                    <input type="password" id="confirmPass" name="confirmPass" placeholder="Re-enter your password" required>
                </div>

                <button type="submit" class="btn btn-accent btn-full">Register</button>

            </form>

            <p class="auth-footer-text">Already have an account? <a href="login.php">Login here</a></p>
        </div>
    </section>

    <!-- footer -->
    <footer class="footer">
        <p>Green University of Bangladesh &mdash; Department of CSE</p>
        <p>QueueLess Project | Web Programming Lab</p>
    </footer>

</body>
</html>