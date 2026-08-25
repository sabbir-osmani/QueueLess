<?php
    session_start();
    require 'includes/db.php';
    require 'includes/services.php'; // gives us $serviceNames

    if (!isset($_SESSION['student_id'])) {
        header("Location: login.php");
        exit();
    }

    $studentId = $_SESSION['student_id'];

    $historyStatement = mysqli_prepare($conn, "SELECT * FROM tokens WHERE student_id = ? ORDER BY created_at DESC");
    mysqli_stmt_bind_param($historyStatement, "i", $studentId);
    mysqli_stmt_execute($historyStatement);
    $historyResult = mysqli_stmt_get_result($historyStatement);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>History - QueueLess</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="icon" href="images/favicon.png" type="image/png">
    <link rel="stylesheet" href="css/common.css">
    <link rel="stylesheet" href="css/history.css">
    <link rel="icon" href="images/favicon.png" type="image/png">
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

    <!-- history section -->
    <section class="history-section">

        <div class="history-header">
            <h1>Your Token History</h1>
            <p>All the tokens you have taken so far</p>
        </div>

        <div class="table-wrap">
            <table class="history-table">
                <thead>
                    <tr>
                        <th>Token</th>
                        <th>Service</th>
                        <th>Date</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (mysqli_num_rows($historyResult) > 0) { ?>
                        <?php while ($row = mysqli_fetch_assoc($historyResult)) { ?>
                            <tr>
                                <td><?php echo htmlspecialchars($row['token_no']); ?></td>
                                <td><?php echo htmlspecialchars($serviceNames[$row['service']]); ?></td>
                                <td><?php echo date("d M Y", strtotime($row['created_at'])); ?></td>
                                <td>
                                    <?php if ($row['status'] == 'completed') { ?>
                                        <span class="status-pill status-completed">Completed</span>
                                    <?php } elseif ($row['status'] == 'skipped') { ?>
                                        <span class="status-pill status-skipped">Skipped</span>
                                    <?php } elseif ($row['status'] == 'cancelled') { ?>
                                        <span class="status-pill status-cancelled">Cancelled</span>
                                    <?php } else { ?>
                                        <span class="status-pill status-completed" style="background-color: rgba(244,163,0,0.12); color: #d98f00;"><?php echo ucfirst($row['status']); ?></span>
                                    <?php } ?>
                                </td>
                            </tr>
                        <?php } ?>
                    <?php } else { ?>
                        <tr>
                            <td colspan="4" style="text-align:center; padding: 30px; color: var(--text-muted);">You have no token history yet.</td>
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

</body>
</html>
