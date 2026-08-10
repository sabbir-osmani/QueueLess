<?php
    session_start();
    require 'includes/db.php';

    if (!isset($_SESSION['student_id'])) {
        header("Location: login.php");
        exit();
    }

    $studentId = $_SESSION['student_id'];

    // validate service parameter - must be one of our 4 real services
    $validServices = array("accounts", "library", "cse", "lab");
    if (!isset($_GET['service']) || !in_array($_GET['service'], $validServices)) {
        header("Location: dashboard.php");
        exit();
    }
    $service = $_GET['service'];

    // block duplicate: if student has an active token for ANY service, send them there instead
    $anyActiveQuery = "SELECT * FROM tokens WHERE student_id = $studentId AND status IN ('waiting','serving') ORDER BY id DESC LIMIT 1";
    $anyActiveResult = mysqli_query($conn, $anyActiveQuery);

    if (mysqli_num_rows($anyActiveResult) > 0) {
        $existingToken = mysqli_fetch_assoc($anyActiveResult);
        if ($existingToken['service'] != $service) {
            header("Location: queue.php?service=" . $existingToken['service']);
            exit();
        }
    }

    // service prefix + display name lookup
    $prefixMap = array("accounts" => "A", "library" => "L", "cse" => "C", "lab" => "B");
    $nameMap = array("accounts" => "Accounts Office", "library" => "Library", "cse" => "CSE Department Office", "lab" => "Computer Lab");
    $prefix = $prefixMap[$service];
    $serviceName = $nameMap[$service];

    // check if this student already has an active token for THIS service
    $checkQuery = "SELECT * FROM tokens WHERE student_id = $studentId AND service = '$service' AND status IN ('waiting','serving') ORDER BY id DESC LIMIT 1";
    $checkResult = mysqli_query($conn, $checkQuery);

    if (mysqli_num_rows($checkResult) > 0) {
        // already has one, just show it
        $myToken = mysqli_fetch_assoc($checkResult);
    } else {
        // no active token, generate a new one
        // count how many tokens already taken for this service TODAY
        $countQuery = "SELECT COUNT(*) AS total FROM tokens WHERE service = '$service'";
        $countResult = mysqli_query($conn, $countQuery);
        $countRow = mysqli_fetch_assoc($countResult);
        $nextNumber = $countRow['total'] + 1;

        $tokenNo = $prefix . str_pad($nextNumber, 3, "0", STR_PAD_LEFT);

        $insertQuery = "INSERT INTO tokens (student_id, service, token_no, status, created_at) VALUES ($studentId, '$service', '$tokenNo', 'waiting', NOW())";
        $inserted = mysqli_query($conn, $insertQuery);

        // if two students hit this at the exact same time, the unique key blocks the duplicate - just retry with +1
        if (!$inserted) {
            $nextNumber = $nextNumber + 1;
            $tokenNo = $prefix . str_pad($nextNumber, 3, "0", STR_PAD_LEFT);
            $insertQuery = "INSERT INTO tokens (student_id, service, token_no, status, created_at) VALUES ($studentId, '$service', '$tokenNo', 'waiting', NOW())";
            mysqli_query($conn, $insertQuery);
        }

        $newTokenId = mysqli_insert_id($conn);
        $myTokenQuery = "SELECT * FROM tokens WHERE id = $newTokenId";
        $myTokenResult = mysqli_query($conn, $myTokenQuery);
        $myToken = mysqli_fetch_assoc($myTokenResult);

        // extremely rare: even the retry failed - send them back rather than crash
        if (!$myToken) {
            header("Location: dashboard.php");
            exit();
        }
    }

    // find the token currently being served for this service
    $servingQuery = "SELECT * FROM tokens WHERE service = '$service' AND status = 'serving' ORDER BY id ASC LIMIT 1";
    $servingResult = mysqli_query($conn, $servingQuery);
    $servingRow = mysqli_fetch_assoc($servingResult);
    $nowServingToken = $servingRow ? $servingRow['token_no'] : "None yet";

    // count how many waiting tokens are ahead of mine (created before mine, still waiting)
    $aheadQuery = "SELECT COUNT(*) AS ahead FROM tokens WHERE service = '$service' AND status = 'waiting' AND id < " . $myToken['id'];
    $aheadResult = mysqli_query($conn, $aheadQuery);
    $aheadRow = mysqli_fetch_assoc($aheadResult);
    $peopleAhead = $aheadRow['ahead'];

    // remember this service for next time (30 days)
    setcookie("lastService", $service, time() + (30 * 24 * 60 * 60), "/");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Queue Status - QueueLess</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="icon" href="images/favicon.png" type="image/png">
    <link rel="stylesheet" href="css/common.css?v=2">
    <link rel="stylesheet" href="css/queue.css?v=2">
</head>
<body>

    <!-- top navbar -->
    <header class="navbar">
        <a href="dashboard.php" class="nav-left" style="text-decoration:none;">
            <img src="images/queueless-icon.png" alt="QueueLess Icon" class="logo">
            <img src="images/queueless-text.png" alt="QueueLess Text" class="logo-text">
        </a>
        <div class="nav-right">
            <a href="dashboard.php" class="btn btn-outline">Dashboard</a>
            <a href="logout.php" class="btn btn-solid">Logout</a>
        </div>
    </header>

    <!-- queue status -->
    <section class="queue-section">

        <div class="queue-header">
            <h1><?php echo $serviceName; ?> Queue</h1>
            <p>Live status of your token, updates automatically</p>
        </div>

        <div class="queue-grid">

            <!-- your token -->
            <div class="queue-card highlight-card">
                <p class="card-label">Your Token</p>
                <h2 class="big-token" id="myTokenBox"><?php echo $myToken['token_no']; ?></h2>
                <span class="status-pill status-waiting" id="myStatusBox">
                    <?php echo $myToken['status'] == 'waiting' ? 'Waiting' : 'In Progress'; ?>
                </span>
            </div>

            <!-- currently serving -->
            <div class="queue-card">
                <p class="card-label">Now Serving</p>
                <h2 class="big-token" id="servingBox"><?php echo $nowServingToken; ?></h2>
                <span class="status-pill status-serving">In Progress</span>
            </div>

            <!-- people ahead -->
            <div class="queue-card">
                <p class="card-label">People Ahead of You</p>
                <h2 class="big-number" id="aheadBox"><?php echo $peopleAhead; ?></h2>
                <p class="card-note" id="waitNote">Estimated wait: ~<?php echo $peopleAhead * 5; ?> mins</p>
            </div>

        </div>

        <div class="queue-note">
            <p>This page refreshes automatically every 5 seconds. Please stay nearby when your token number is close.</p>
        </div>

        <a href="dashboard.php" class="btn btn-outline back-btn">Back to Dashboard</a>

    </section>

    <!-- footer -->
    <footer class="footer">
        <p>Green University of Bangladesh &mdash; Department of CSE</p>
        <p>QueueLess Project | Web Programming Lab</p>
    </footer>
    <script>
        function refreshQueueStatus() {
            var xhr = new XMLHttpRequest();
            var url = "ajax/queueStatus.php?service=<?php echo $service; ?>";

            xhr.open("GET", url, true);

            xhr.onreadystatechange = function() {
                if (xhr.readyState == 4 && xhr.status == 200) {
                    var data = JSON.parse(xhr.responseText);

                    document.getElementById("myTokenBox").innerHTML = data.myToken;
                    document.getElementById("servingBox").innerHTML = data.nowServing;
                    document.getElementById("aheadBox").innerHTML = data.peopleAhead;
                    document.getElementById("waitNote").innerHTML = "Estimated wait: ~" + (data.peopleAhead * 5) + " mins";

                    var statusBox = document.getElementById("myStatusBox");
                    if (data.myStatus == "waiting") {
                        statusBox.innerHTML = "Waiting";
                        statusBox.className = "status-pill status-waiting";
                    } else {
                        statusBox.innerHTML = "In Progress";
                        statusBox.className = "status-pill status-serving";
                    }
                }
            };

            xhr.send();
        }

        // refresh every 5 seconds
        setInterval(refreshQueueStatus, 5000);
    </script>

</body>
</html>