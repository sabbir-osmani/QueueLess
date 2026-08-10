<?php
    session_start();
    require '../includes/db.php';

    if (!isset($_SESSION['admin_id'])) {
        header("Location: login.php");
        exit();
    }

    $service = $_SESSION['admin_service'];
    $adminName = $_SESSION['admin_name'];
    $nameMap = array("accounts" => "Accounts Office", "library" => "Library", "cse" => "CSE Department Office", "lab" => "Computer Lab");

    // safety check - this admin account has no service assigned in the database
    if (!$service || !isset($nameMap[$service])) {
        die("Your admin account has no service assigned. Please contact the system administrator to fix this in the admins table.");
    }

    // now serving row
    $servingQuery = "SELECT tokens.*, students.name AS student_name FROM tokens JOIN students ON tokens.student_id = students.id WHERE tokens.service = '$service' AND tokens.status = 'serving' ORDER BY tokens.id ASC LIMIT 1";
    $servingResult = mysqli_query($conn, $servingQuery);
    $servingRow = mysqli_num_rows($servingResult) > 0 ? mysqli_fetch_assoc($servingResult) : null;

    // stats: waiting count, served today, skipped today
    $waitingQuery = "SELECT COUNT(*) AS total FROM tokens WHERE service = '$service' AND status = 'waiting'";
    $waitingRow = mysqli_fetch_assoc(mysqli_query($conn, $waitingQuery));
    $waitingCount = $waitingRow['total'];

    $servedQuery = "SELECT COUNT(*) AS total FROM tokens WHERE service = '$service' AND status = 'completed' AND DATE(created_at) = CURDATE()";
    $servedRow = mysqli_fetch_assoc(mysqli_query($conn, $servedQuery));
    $servedCount = $servedRow['total'];

    $skippedQuery = "SELECT COUNT(*) AS total FROM tokens WHERE service = '$service' AND status = 'skipped' AND DATE(created_at) = CURDATE()";
    $skippedRow = mysqli_fetch_assoc(mysqli_query($conn, $skippedQuery));
    $skippedCount = $skippedRow['total'];

    // waiting list
    $listQuery = "SELECT tokens.*, students.name AS student_name FROM tokens JOIN students ON tokens.student_id = students.id WHERE tokens.service = '$service' AND tokens.status = 'waiting' ORDER BY tokens.id ASC";
    $listResult = mysqli_query($conn, $listQuery);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - QueueLess</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="icon" href="../images/favicon.png" type="image/png">
    <link rel="stylesheet" href="../css/common.css?v=2">
    <link rel="stylesheet" href="../css/admin.css?v=2">
</head>
<body>

    <!-- top navbar -->
    <header class="navbar">
        <a href="dashboard.php" class="nav-left" style="text-decoration:none;">
            <img src="../images/queueless-icon.png" alt="QueueLess Icon" class="logo">
            <img src="../images/queueless-text.png" alt="QueueLess Text" class="logo-text">
        </a>
        <div class="nav-right">
            <span class="admin-badge">Admin</span>
            <a href="login.php" class="btn btn-solid">Logout</a>
        </div>
    </header>

    <!-- service selector -->
    <section class="admin-header">
        <h1>Queue Management</h1>
        <p>Welcome, <?php echo $adminName; ?> &mdash; you manage the <strong><?php echo $nameMap[$service]; ?></strong> queue</p>

        <div class="service-tabs">
            <span class="tab active-tab"><?php echo $nameMap[$service]; ?></span>
        </div>
    </section>

    <!-- current serving + controls -->
    <section class="control-section">

        <div class="serving-card">
            <p class="card-label">Now Serving</p>
            <?php if ($servingRow) { ?>
                <h2 class="big-token" id="servingTokenBox"><?php echo $servingRow['token_no']; ?></h2>
                <p class="card-note" id="servingStudentBox">Student: <?php echo $servingRow['student_name']; ?></p>
            <?php } else { ?>
                <h2 class="big-token" id="servingTokenBox">--</h2>
                <p class="card-note" id="servingStudentBox">No one is being served right now</p>
            <?php } ?>

            <div class="control-buttons" id="controlButtonsBox">
                <?php if ($servingRow) { ?>
                    <a href="completeToken.php?service=<?php echo $service; ?>" class="btn btn-complete">Mark Completed</a>
                    <a href="skipToken.php?service=<?php echo $service; ?>" class="btn btn-danger">Skip</a>
                <?php } else { ?>
                    <a href="nextToken.php?service=<?php echo $service; ?>" class="btn btn-solid">Call Next</a>
                <?php } ?>
            </div>
        </div>

        <div class="stat-mini-grid">
            <div class="stat-mini">
                <p class="stat-number" id="waitingCountBox"><?php echo $waitingCount; ?></p>
                <p class="stat-label">Waiting</p>
            </div>
            <div class="stat-mini">
                <p class="stat-number" id="servedCountBox"><?php echo $servedCount; ?></p>
                <p class="stat-label">Served Today</p>
            </div>
            <div class="stat-mini">
                <p class="stat-number" id="skippedCountBox"><?php echo $skippedCount; ?></p>
                <p class="stat-label">Skipped</p>
            </div>
        </div>

    </section>

    <!-- waiting list -->
    <section class="queue-list-section">
        <div class="queue-list-header">
            <h2><?php echo $nameMap[$service]; ?> - Waiting List</h2>
            <a href="resetQueue.php?service=<?php echo $service; ?>" class="btn btn-outline btn-small">Reset Queue</a>
        </div>

        <div class="table-wrap">
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>Token</th>
                        <th>Student Name</th>
                        <th>Time Taken</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody id="waitingListBody">
                    <?php if (mysqli_num_rows($listResult) > 0) { ?>
                        <?php while ($row = mysqli_fetch_assoc($listResult)) { ?>
                            <tr>
                                <td><?php echo $row['token_no']; ?></td>
                                <td><?php echo $row['student_name']; ?></td>
                                <td><?php echo date("h:i A", strtotime($row['created_at'])); ?></td>
                                <td><span class="status-pill status-waiting">Waiting</span></td>
                            </tr>
                        <?php } ?>
                    <?php } else { ?>
                        <tr>
                            <td colspan="4" style="text-align:center; padding: 30px; color: var(--text-muted);">No one waiting in this queue.</td>
                        </tr>
                    <?php } ?>
                </tbody>
            </table>
        </div>
    </section>

    <!-- footer -->
    <footer class="footer">
        <p>Green University of Bangladesh &mdash; Department of CSE</p>
        <p>QueueLess Project | Web Programming Lab</p>
    </footer>
    <script>
        var currentService = "<?php echo $service; ?>";

        function refreshAdminStatus() {
            var xhr = new XMLHttpRequest();
            xhr.open("GET", "../ajax/adminStatus.php?service=" + currentService, true);

            xhr.onreadystatechange = function() {
                if (xhr.readyState == 4 && xhr.status == 200) {
                    var data = JSON.parse(xhr.responseText);

                    // update now serving card
                    document.getElementById("servingTokenBox").innerHTML = data.servingToken ? data.servingToken : "--";
                    document.getElementById("servingStudentBox").innerHTML = data.servingToken ? ("Student: " + data.servingStudent) : "No one is being served right now";

                    // update control buttons based on state
                    var buttonsBox = document.getElementById("controlButtonsBox");
                    if (data.servingToken) {
                        buttonsBox.innerHTML =
                            '<a href="completeToken.php?service=' + currentService + '" class="btn btn-complete">Mark Completed</a>' +
                            '<a href="skipToken.php?service=' + currentService + '" class="btn btn-danger">Skip</a>';
                    } else {
                        buttonsBox.innerHTML =
                            '<a href="nextToken.php?service=' + currentService + '" class="btn btn-solid">Call Next</a>';
                    }

                    // update stats
                    document.getElementById("waitingCountBox").innerHTML = data.waitingCount;
                    document.getElementById("servedCountBox").innerHTML = data.servedCount;
                    document.getElementById("skippedCountBox").innerHTML = data.skippedCount;

                    // rebuild waiting list table
                    var tbody = document.getElementById("waitingListBody");
                    if (data.waitingList.length > 0) {
                        var rowsHtml = "";
                        for (var i = 0; i < data.waitingList.length; i++) {
                            var item = data.waitingList[i];
                            rowsHtml += "<tr>";
                            rowsHtml += "<td>" + item.token_no + "</td>";
                            rowsHtml += "<td>" + item.student_name + "</td>";
                            rowsHtml += "<td>" + item.time + "</td>";
                            rowsHtml += '<td><span class="status-pill status-waiting">Waiting</span></td>';
                            rowsHtml += "</tr>";
                        }
                        tbody.innerHTML = rowsHtml;
                    } else {
                        tbody.innerHTML = '<tr><td colspan="4" style="text-align:center; padding: 30px; color: var(--text-muted);">No one waiting in this queue.</td></tr>';
                    }
                }
            };

            xhr.send();
        }

        // refresh every 5 seconds
        setInterval(refreshAdminStatus, 5000);
    </script>

</body>
</html>