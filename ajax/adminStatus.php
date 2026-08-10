<?php
    session_start();
    require '../includes/db.php';

    header('Content-Type: application/json');

    if (!isset($_SESSION['admin_id'])) {
        echo json_encode(array("error" => "not logged in"));
        exit();
    }

    $service = $_SESSION['admin_service']; // locked to admin's own assigned service, ignore any URL tampering

    // now serving
    $servingQuery = "SELECT tokens.*, students.name AS student_name FROM tokens JOIN students ON tokens.student_id = students.id WHERE tokens.service = '$service' AND tokens.status = 'serving' ORDER BY tokens.id ASC LIMIT 1";
    $servingResult = mysqli_query($conn, $servingQuery);
    $servingRow = mysqli_num_rows($servingResult) > 0 ? mysqli_fetch_assoc($servingResult) : null;

    // stats
    $waitingRow = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) AS total FROM tokens WHERE service = '$service' AND status = 'waiting'"));
    $servedRow = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) AS total FROM tokens WHERE service = '$service' AND status = 'completed' AND DATE(created_at) = CURDATE()"));
    $skippedRow = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) AS total FROM tokens WHERE service = '$service' AND status = 'skipped' AND DATE(created_at) = CURDATE()"));

    // waiting list rows
    $listQuery = "SELECT tokens.*, students.name AS student_name FROM tokens JOIN students ON tokens.student_id = students.id WHERE tokens.service = '$service' AND tokens.status = 'waiting' ORDER BY tokens.id ASC";
    $listResult = mysqli_query($conn, $listQuery);

    $waitingList = array();
    while ($row = mysqli_fetch_assoc($listResult)) {
        $waitingList[] = array(
            "token_no" => $row['token_no'],
            "student_name" => $row['student_name'],
            "time" => date("h:i A", strtotime($row['created_at']))
        );
    }

    $response = array(
        "servingToken" => $servingRow ? $servingRow['token_no'] : null,
        "servingStudent" => $servingRow ? $servingRow['student_name'] : null,
        "waitingCount" => $waitingRow['total'],
        "servedCount" => $servedRow['total'],
        "skippedCount" => $skippedRow['total'],
        "waitingList" => $waitingList
    );

    echo json_encode($response);
?>