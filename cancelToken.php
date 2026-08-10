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
    $query = "UPDATE tokens SET status = 'cancelled' WHERE id = $tokenId AND student_id = $studentId AND status = 'waiting'";
    mysqli_query($conn, $query);

    header("Location: dashboard.php");
    exit();
?>