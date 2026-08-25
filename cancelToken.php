<?php
    session_start();
    require 'includes/db.php';

    if (!isset($_SESSION['student_id'])) {
        header("Location: login.php");
        exit();
    }

    $studentId = $_SESSION['student_id'];

    if (!isset($_GET['id'])) {
        header("Location: dashboard.php");
        exit();
    }
    $tokenId = (int) $_GET['id'];

    // only allow cancelling YOUR OWN token, and only if it's still waiting (not already being served)
    $statement = mysqli_prepare($conn, "UPDATE tokens SET status = 'cancelled' WHERE id = ? AND student_id = ? AND status = 'waiting'");
    mysqli_stmt_bind_param($statement, "ii", $tokenId, $studentId);
    mysqli_stmt_execute($statement);

    header("Location: dashboard.php");
    exit();
?>
