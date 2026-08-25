<?php
    session_start();
    require '../includes/db.php';

    if (!isset($_SESSION['admin_id'])) {
        header("Location: login.php");
        exit();
    }

    $service = $_SESSION['admin_service']; // locked to admin's own assigned service, ignore any URL tampering

    // only call next if NO ONE is currently being served
    $currentServingStatement = mysqli_prepare($conn, "SELECT id FROM tokens WHERE service = ? AND status = 'serving' LIMIT 1");
    mysqli_stmt_bind_param($currentServingStatement, "s", $service);
    mysqli_stmt_execute($currentServingStatement);
    $currentServingResult = mysqli_stmt_get_result($currentServingStatement);

    if (mysqli_num_rows($currentServingResult) == 0) {
        $nextStatement = mysqli_prepare($conn, "SELECT id FROM tokens WHERE service = ? AND status = 'waiting' ORDER BY id ASC LIMIT 1");
        mysqli_stmt_bind_param($nextStatement, "s", $service);
        mysqli_stmt_execute($nextStatement);
        $nextResult = mysqli_stmt_get_result($nextStatement);

        if (mysqli_num_rows($nextResult) > 0) {
            $nextRow = mysqli_fetch_assoc($nextResult);
            $updateStatement = mysqli_prepare($conn, "UPDATE tokens SET status = 'serving' WHERE id = ?");
            mysqli_stmt_bind_param($updateStatement, "i", $nextRow['id']);
            mysqli_stmt_execute($updateStatement);
        }
    }

    header("Location: dashboard.php");
    exit();
?>
