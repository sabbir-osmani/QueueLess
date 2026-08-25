<?php
    session_start();
    require '../includes/db.php';

    if (!isset($_SESSION['admin_id'])) {
        header("Location: login.php");
        exit();
    }

    $service = $_SESSION['admin_service']; // locked to admin's own assigned service, ignore any URL tampering

    $statement = mysqli_prepare($conn, "UPDATE tokens SET status = 'completed' WHERE service = ? AND status = 'serving'");
    mysqli_stmt_bind_param($statement, "s", $service);
    mysqli_stmt_execute($statement);

    header("Location: dashboard.php");
    exit();
?>
