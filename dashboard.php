<?php
    session_start();

    if (!isset($_SESSION['student_id'])) {
        header("Location: login.php");
        exit();
    }

    $studentName = $_SESSION['student_name'];

    require 'includes/db.php';
    require 'includes/services.php'; // gives us the $services list
    $studentId = $_SESSION['student_id'];

    // does this student already have a token that is waiting or being served?
    $activeStatement = mysqli_prepare($conn, "SELECT * FROM tokens WHERE student_id = ? AND status IN ('waiting','serving') ORDER BY id DESC LIMIT 1");
    mysqli_stmt_bind_param($activeStatement, "i", $studentId);
    mysqli_stmt_execute($activeStatement);
    $activeResult = mysqli_stmt_get_result($activeStatement);
    $activeToken = mysqli_num_rows($activeResult) > 0 ? mysqli_fetch_assoc($activeResult) : null;

    // check cookie for last selected service
    $lastService = isset($_COOKIE['lastService']) ? $_COOKIE['lastService'] : "";
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - QueueLess</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="icon" href="images/favicon.png" type="image/png">
    <link rel="stylesheet" href="css/common.css">
    <link rel="stylesheet" href="css/dashboard.css">
</head>
<body>

    <!-- top navbar -->
    <header class="navbar">
        <a href="dashboard.php" class="nav-left" style="text-decoration:none;">
            <img src="images/queueless-icon.png" alt="QueueLess Icon" class="logo">
            <img src="images/queueless-text.png" alt="QueueLess Text" class="logo-text">
        </a>
        <div class="nav-right">
            <a href="history.php" class="btn btn-outline">History</a>
            <a href="logout.php" class="btn btn-solid">Logout</a>
        </div>
    </header>

    <!-- welcome bar -->
    <section class="welcome-bar">
        <h1>Welcome back, <?php echo htmlspecialchars($studentName); ?></h1>
        <p>Here's your queue status and available services</p>
    </section>

    <!-- current token status -->
    <section class="status-section">
        <div class="status-card">
            <p class="status-label">Your Active Token</p>
            <?php if ($activeToken) { ?>
                <h2 class="status-token" id="statusTokenBox"><?php echo htmlspecialchars($activeToken['token_no']); ?> (<?php echo ucfirst($activeToken['service']); ?>)</h2>
                <p class="status-sub" id="statusSubBox">Status: <?php echo ucfirst($activeToken['status']); ?></p>
                <a href="queue.php?service=<?php echo $activeToken['service']; ?>" class="btn btn-solid track-btn">Track Live Queue</a>
            <?php } else { ?>
                <h2 class="status-token" id="statusTokenBox">No active token</h2>
                <p class="status-sub" id="statusSubBox">Select a service below to take a new token</p>
            <?php } ?>
        </div>
    </section>

    <!-- service selection -->
    <section class="services">
        <h2>Select a Service</h2>
        <p class="section-sub">Choose where you need to go, we'll handle the queue</p>

        <div class="service-grid">

            <?php foreach ($services as $service) {
                $serviceCode = $service['code'];
            ?>
            <div class="service-card">
                <h3><?php echo htmlspecialchars($service['name']); ?></h3>
                <p><?php echo htmlspecialchars($service['description']); ?></p>
                <span class="token-tag"><?php echo $service['prefix']; ?>001</span>

                <?php if ($lastService == $serviceCode) { ?>
                    <span class="last-used-badge">Last used</span>
                <?php } ?>

                <?php if ($activeToken && $activeToken['service'] == $serviceCode) { ?>
                    <!-- this is the service the student's active token belongs to -->
                    <div id="activeCardActionBox">
                        <a href="queue.php?service=<?php echo $serviceCode; ?>" class="btn btn-outline btn-full-small track-link">Track Queue</a>
                        <?php if ($activeToken['status'] == 'waiting') { ?>
                            <a href="cancelToken.php?id=<?php echo $activeToken['id']; ?>" class="btn btn-danger btn-full-small" onclick="return confirm('Cancel this token?');">Cancel Token</a>
                        <?php } else { ?>
                            <span class="btn btn-disabled btn-full-small">Being Served</span>
                        <?php } ?>
                    </div>
                <?php } elseif ($activeToken) { ?>
                    <!-- student already has an active token for a DIFFERENT service, so this one is locked -->
                    <span class="btn btn-disabled btn-full-small">Locked</span>
                <?php } else { ?>
                    <!-- no active token anywhere, so the student can take one here -->
                    <a href="queue.php?service=<?php echo $serviceCode; ?>" class="btn btn-solid btn-full-small">Take Token</a>
                <?php } ?>
            </div>
            <?php } ?>

        </div>
    </section>

    <!-- footer -->
    <footer class="footer">
        <p>Green University of Bangladesh &mdash; Department of CSE</p>
        <p>QueueLess Project | Web Programming Lab</p>
    </footer>
    <?php if ($activeToken) { ?>
    <script>
        var activeService = "<?php echo $activeToken['service']; ?>";

        function refreshDashboardStatus() {
            var xhr = new XMLHttpRequest();
            xhr.open("GET", "ajax/queueStatus.php?service=" + activeService, true);

            xhr.onreadystatechange = function() {
                if (xhr.readyState == 4 && xhr.status == 200) {
                    var data = JSON.parse(xhr.responseText);
                    if (data.error || !data.myToken) {
                        // token no longer active (completed/cancelled) - reload to reflect fresh state
                        location.reload();
                        return;
                    }

                    document.getElementById("statusTokenBox").innerHTML = data.myToken + " (" + activeService.charAt(0).toUpperCase() + activeService.slice(1) + ")";
                    document.getElementById("statusSubBox").innerHTML = "Status: " + (data.myStatus.charAt(0).toUpperCase() + data.myStatus.slice(1)) + " | People ahead: " + data.peopleAhead;

                    var actionBox = document.getElementById("activeCardActionBox");
                    if (actionBox) {
                        var trackLink = '<a href="queue.php?service=' + activeService + '" class="btn btn-outline btn-full-small track-link">Track Queue</a>';
                        if (data.myStatus == "waiting") {
                            actionBox.innerHTML = trackLink + '<a href="cancelToken.php?id=' + data.myTokenId + '" class="btn btn-danger btn-full-small" onclick="return confirm(\'Cancel this token?\');">Cancel Token</a>';
                        } else {
                            actionBox.innerHTML = trackLink + '<span class="btn btn-disabled btn-full-small">Being Served</span>';
                        }
                    }
                }
            };

            xhr.send();
        }

        setInterval(refreshDashboardStatus, 5000);
    </script>
    <?php } ?>

</body>
</html>
