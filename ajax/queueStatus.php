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
    $myQuery = "SELECT * FROM tokens WHERE student_id = $studentId AND service = '$service' AND status IN ('waiting','serving') ORDER BY id DESC LIMIT 1";
    $myResult = mysqli_query($conn, $myQuery);
    $myToken = mysqli_fetch_assoc($myResult);

    // token is no longer active (admin completed/skipped it) - tell the frontend and stop here
    if (!$myToken) {
        echo json_encode(array("myToken" => null));
        exit();
    }

    // now serving
    $servingQuery = "SELECT token_no FROM tokens WHERE service = '$service' AND status = 'serving' ORDER BY id ASC LIMIT 1";
    $servingResult = mysqli_query($conn, $servingQuery);
    $servingRow = mysqli_fetch_assoc($servingResult);
    $nowServing = $servingRow ? $servingRow['token_no'] : "None yet";

    // people ahead
    $aheadQuery = "SELECT COUNT(*) AS ahead FROM tokens WHERE service = '$service' AND status = 'waiting' AND id < " . $myToken['id'];
    $aheadResult = mysqli_query($conn, $aheadQuery);
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