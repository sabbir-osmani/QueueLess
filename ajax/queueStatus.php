<?php
    session_start();
    require '../includes/db.php';

    header('Content-Type: application/json');

    if (!isset($_SESSION['student_id'])) {
        echo json_encode(array("error" => "not logged in"));
        exit();
    }

    $studentId = $_SESSION['student_id'];
    $service = $_GET['service'];

    // my token
    $myStatement = mysqli_prepare($conn, "SELECT * FROM tokens WHERE student_id = ? AND service = ? AND status IN ('waiting','serving') ORDER BY id DESC LIMIT 1");
    mysqli_stmt_bind_param($myStatement, "is", $studentId, $service);
    mysqli_stmt_execute($myStatement);
    $myResult = mysqli_stmt_get_result($myStatement);
    $myToken = mysqli_fetch_assoc($myResult);

    // token is no longer active (admin completed/skipped it) - tell the frontend and stop here
    if (!$myToken) {
        echo json_encode(array("myToken" => null));
        exit();
    }

    // now serving
    $servingStatement = mysqli_prepare($conn, "SELECT token_no FROM tokens WHERE service = ? AND status = 'serving' ORDER BY id ASC LIMIT 1");
    mysqli_stmt_bind_param($servingStatement, "s", $service);
    mysqli_stmt_execute($servingStatement);
    $servingResult = mysqli_stmt_get_result($servingStatement);
    $servingRow = mysqli_fetch_assoc($servingResult);
    $nowServing = $servingRow ? $servingRow['token_no'] : "None yet";

    // people ahead
    $aheadStatement = mysqli_prepare($conn, "SELECT COUNT(*) AS ahead FROM tokens WHERE service = ? AND status = 'waiting' AND id < ?");
    mysqli_stmt_bind_param($aheadStatement, "si", $service, $myToken['id']);
    mysqli_stmt_execute($aheadStatement);
    $aheadResult = mysqli_stmt_get_result($aheadStatement);
    $aheadRow = mysqli_fetch_assoc($aheadResult);

    $response = array(
        "myTokenId" => $myToken['id'],
        "myToken" => $myToken['token_no'],
        "myStatus" => $myToken['status'],
        "nowServing" => $nowServing,
        "peopleAhead" => $aheadRow['ahead']
    );

    echo json_encode($response);
?>
