<?php
    session_start();
    require '../includes/db.php';

    if (!isset($_SESSION['admin_id'])) {
        header("Location: login.php");
        exit();
    }

    $service = $_SESSION['admin_service']; // locked to admin's own assigned service, ignore any URL tampering

    // clears the queue - marks everyone still waiting/serving as completed
    $query = "UPDATE tokens SET status = 'completed' WHERE service = '$service' AND status IN ('waiting','serving')";
    mysqli_query($conn, $query);

    header("Location: dashboard.php");
    exit();
?>