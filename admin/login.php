<?php
    session_start();
    require '../includes/db.php';

    $errorMsg = "";

    if (isset($_POST['email'])) {
        $email = $_POST['email'];
        $pass = $_POST['pass'];

        $query = "SELECT * FROM admins WHERE email = '$email'";
        $result = mysqli_query($conn, $query);

        if (mysqli_num_rows($result) > 0) {
            $row = mysqli_fetch_assoc($result);

            if (password_verify($pass, $row['pass'])) {
                $_SESSION['admin_id'] = $row['id'];
                $_SESSION['admin_name'] = $row['name'];
                $_SESSION['admin_service'] = $row['service'];

                header("Location: dashboard.php");
                exit();
            } else {
                $errorMsg = "Wrong password";
            }
        } else {
            $errorMsg = "No admin account found with this email";
        }
    }
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login - QueueLess</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="icon" href="../images/favicon.png" type="image/png">
    <link rel="stylesheet" href="../css/common.css">
    <link rel="stylesheet" href="../css/auth.css">
</head>
<body>

    <!-- top navbar -->
    <header class="navbar">
        <a href="../index.php" class="nav-left" style="text-decoration:none;">
            <img src="../images/queueless-icon.png" alt="QueueLess Icon" class="logo">
            <img src="../images/queueless-text.png" alt="QueueLess Text" class="logo-text">
        </a>
        <div class="nav-right">
            <a href="../index.php" class="btn btn-outline">Back to Home</a>
        </div>
    </header>

    <!-- admin login card -->
    <section class="auth-section">
        <div class="auth-card">
            <h1>Admin Login</h1>
            <p class="auth-sub">Staff access only, manage queues here</p>

            <?php if ($errorMsg != "") { ?>
                <p class="form-error"><?php echo $errorMsg; ?></p>
            <?php } ?>

            <form action="" method="POST">

                <div class="form-group">
                    <label for="email">Admin Email</label>
                    <input type="email" id="email" name="email" placeholder="admin@queueless.com" required>
                </div>

                <div class="form-group">
                    <label for="pass">Password</label>
                    <input type="password" id="pass" name="pass" placeholder="Enter admin password" required>
                </div>

                <button type="submit" class="btn btn-accent btn-full">Login</button>

            </form>

            <p class="auth-footer-text">Not an admin? <a href="../login.php">Student login</a></p>
        </div>
    </section>

    <!-- footer -->
    <footer class="footer">
        <p>Green University of Bangladesh &mdash; Department of CSE</p>
        <p>QueueLess Project | Web Programming Lab</p>
    </footer>

</body>
</html>