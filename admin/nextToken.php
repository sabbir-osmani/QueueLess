<?php
    session_start();
    require '../includes/db.php';

    if (!isset($_SESSION['admin_id'])) {
        header("Location: login.php");
        exit();
    }

    $service = $_SESSION['admin_service']; // locked to admin's own assigned service, ignore any URL tampering

    // only call next if NO ONE is currently being served
    $currentServingQuery = "SELECT id FROM tokens WHERE service = '$service' AND status = 'serving' LIMIT 1";
    $currentServingResult = mysqli_query($conn, $currentServingQuery);

    if (mysqli_num_rows($currentServingResult) == 0) {
        $nextQuery = "SELECT id FROM tokens WHERE service = '$service' AND status = 'waiting' ORDER BY id ASC LIMIT 1";
        $nextResult = mysqli_query($conn, $nextQuery);

        if (mysqli_num_rows($nextResult) > 0) {
            $nextRow = mysqli_fetch_assoc($nextResult);
            mysqli_query($conn, "UPDATE tokens SET status = 'serving' WHERE id = " . $nextRow['id']);
        }
    }

    header("Location: dashboard.php");
    exit();
?>