<?php
    session_start();

    if (!isset($_SESSION['student_id'])) {
        header("Location: login.php");
        exit();
    }

    $studentName = $_SESSION['student_name'];

    require 'includes/db.php';
    $studentId = $_SESSION['student_id'];

    $activeQuery = "SELECT * FROM tokens WHERE student_id = $studentId AND status IN ('waiting','serving') ORDER BY id DESC LIMIT 1";
    $activeResult = mysqli_query($conn, $activeQuery);
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
        <h1>Welcome back, <?php echo $studentName; ?></h1>
        <p>Here's your queue status and available services</p>
    </section>

    <!-- current token status -->
    <section class="status-section">
        <div class="status-card">
            <p class="status-label">Your Active Token</p>
            <?php if ($activeToken) { ?>
                <h2 class="status-token" id="statusTokenBox"><?php echo $activeToken['token_no']; ?> (<?php echo ucfirst($activeToken['service']); ?>)</h2>
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

            <div class="service-card">
                <h3>Accounts Office</h3>
                <p>Tuition fee, payments and account related queries.</p>
                <span class="token-tag">A001</span>
                <?php if ($lastService == 'accounts') { ?>
                    <span class="last-used-badge">Last used</span>
                <?php } ?>
                <?php if ($activeToken && $activeToken['service'] == 'accounts') { ?>
                    <div id="activeCardActionBox">
                    <a href="queue.php?service=accounts" class="btn btn-outline btn-full-small track-link">Track Queue</a>
                    <?php if ($activeToken['status'] == 'waiting') { ?>
                        <a href="cancelToken.php?id=<?php echo $activeToken['id']; ?>" class="btn btn-danger btn-full-small" onclick="return confirm('Cancel this token?');">Cancel Token</a>
                    <?php } else { ?>
                        <span class="btn btn-disabled btn-full-small">Being Served</span>
                    <?php } ?>
                    </div>
                <?php } elseif ($activeToken) { ?>
                    <span class="btn btn-disabled btn-full-small">Locked</span>
                <?php } else { ?>
                    <a href="queue.php?service=accounts" class="btn btn-solid btn-full-small">Take Token</a>
                <?php } ?>
            </div>

            <div class="service-card">
                <h3>Library</h3>
                <p>Book issue, return and library membership services.</p>
                <span class="token-tag">L001</span>
                <?php if ($lastService == 'library') { ?>
                    <span class="last-used-badge">Last used</span>
                <?php } ?>
                <?php if ($activeToken && $activeToken['service'] == 'library') { ?>
                    <div id="activeCardActionBox">
                    <a href="queue.php?service=library" class="btn btn-outline btn-full-small track-link">Track Queue</a>
                    <?php if ($activeToken['status'] == 'waiting') { ?>
                        <a href="cancelToken.php?id=<?php echo $activeToken['id']; ?>" class="btn btn-danger btn-full-small" onclick="return confirm('Cancel this token?');">Cancel Token</a>
                    <?php } else { ?>
                        <span class="btn btn-disabled btn-full-small">Being Served</span>
                    <?php } ?>
                    </div>
                <?php } elseif ($activeToken) { ?>
                    <span class="btn btn-disabled btn-full-small">Locked</span>
                <?php } else { ?>
                    <a href="queue.php?service=library" class="btn btn-solid btn-full-small">Take Token</a>
                <?php } ?>
            </div>

            <div class="service-card">
                <h3>CSE Department Office</h3>
                <p>Department related forms, signatures and notices.</p>
                <span class="token-tag">C001</span>
                <?php if ($lastService == 'cse') { ?>
                    <span class="last-used-badge">Last used</span>
                <?php } ?>
                <?php if ($activeToken && $activeToken['service'] == 'cse') { ?>
                    <div id="activeCardActionBox">
                    <a href="queue.php?service=cse" class="btn btn-outline btn-full-small track-link">Track Queue</a>
                    <?php if ($activeToken['status'] == 'waiting') { ?>
                        <a href="cancelToken.php?id=<?php echo $activeToken['id']; ?>" class="btn btn-danger btn-full-small" onclick="return confirm('Cancel this token?');">Cancel Token</a>
                    <?php } else { ?>
                        <span class="btn btn-disabled btn-full-small">Being Served</span>
                    <?php } ?>
                    </div>
                <?php } elseif ($activeToken) { ?>
                    <span class="btn btn-disabled btn-full-small">Locked</span>
                <?php } else { ?>
                    <a href="queue.php?service=cse" class="btn btn-solid btn-full-small">Take Token</a>
                <?php } ?>
            </div>

            <div class="service-card">
                <h3>Computer Lab</h3>
                <p>Lab access, equipment and technical support.</p>
                <span class="token-tag">B001</span>
                <?php if ($lastService == 'lab') { ?>
                    <span class="last-used-badge">Last used</span>
                <?php } ?>
                <?php if ($activeToken && $activeToken['service'] == 'lab') { ?>
                    <div id="activeCardActionBox">
                    <a href="queue.php?service=lab" class="btn btn-outline btn-full-small track-link">Track Queue</a>
                    <?php if ($activeToken['status'] == 'waiting') { ?>
                        <a href="cancelToken.php?id=<?php echo $activeToken['id']; ?>" class="btn btn-danger btn-full-small" onclick="return confirm('Cancel this token?');">Cancel Token</a>
                    <?php } else { ?>
                        <span class="btn btn-disabled btn-full-small">Being Served</span>
                    <?php } ?>
                    </div>
                <?php } elseif ($activeToken) { ?>
                    <span class="btn btn-disabled btn-full-small">Locked</span>
                <?php } else { ?>
                    <a href="queue.php?service=lab" class="btn btn-solid btn-full-small">Take Token</a>
                <?php } ?>
            </div>

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