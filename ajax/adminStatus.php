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
    $servingStatement = mysqli_prepare($conn, "SELECT tokens.*, students.name AS student_name FROM tokens JOIN students ON tokens.student_id = students.id WHERE tokens.service = ? AND tokens.status = 'serving' ORDER BY tokens.id ASC LIMIT 1");
    mysqli_stmt_bind_param($servingStatement, "s", $service);
    mysqli_stmt_execute($servingStatement);
    $servingResult = mysqli_stmt_get_result($servingStatement);
    $servingRow = mysqli_num_rows($servingResult) > 0 ? mysqli_fetch_assoc($servingResult) : null;

    // stats
    $waitingStatement = mysqli_prepare($conn, "SELECT COUNT(*) AS total FROM tokens WHERE service = ? AND status = 'waiting'");
    mysqli_stmt_bind_param($waitingStatement, "s", $service);
    mysqli_stmt_execute($waitingStatement);
    $waitingRow = mysqli_fetch_assoc(mysqli_stmt_get_result($waitingStatement));

    $servedStatement = mysqli_prepare($conn, "SELECT COUNT(*) AS total FROM tokens WHERE service = ? AND status = 'completed' AND DATE(created_at) = CURDATE()");
    mysqli_stmt_bind_param($servedStatement, "s", $service);
    mysqli_stmt_execute($servedStatement);
    $servedRow = mysqli_fetch_assoc(mysqli_stmt_get_result($servedStatement));

    $skippedStatement = mysqli_prepare($conn, "SELECT COUNT(*) AS total FROM tokens WHERE service = ? AND status = 'skipped' AND DATE(created_at) = CURDATE()");
    mysqli_stmt_bind_param($skippedStatement, "s", $service);
    mysqli_stmt_execute($skippedStatement);
    $skippedRow = mysqli_fetch_assoc(mysqli_stmt_get_result($skippedStatement));

    // waiting list rows
    $listStatement = mysqli_prepare($conn, "SELECT tokens.*, students.name AS student_name FROM tokens JOIN students ON tokens.student_id = students.id WHERE tokens.service = ? AND tokens.status = 'waiting' ORDER BY tokens.id ASC");
    mysqli_stmt_bind_param($listStatement, "s", $service);
    mysqli_stmt_execute($listStatement);
    $listResult = mysqli_stmt_get_result($listStatement);

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
