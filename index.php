<?php
    require 'includes/services.php'; // gives us the $services list
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>QueueLess - Green University of Bangladesh</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="css/common.css">
    <link rel="stylesheet" href="css/home.css">
    <link rel="icon" href="images/favicon.png" type="image/png">
</head>
<body>

    <!-- top navbar -->
    <header class="navbar">
        <div class="nav-left">
            <img src="images/queueless-icon.png" alt="QueueLess Icon" class="logo">
            <img src="images/queueless-text.png" alt="QueueLess Text" class="logo-text">
        </div>
        <div class="nav-right">
            <a href="login.php" class="btn btn-outline">Login</a>
            <a href="register.php" class="btn btn-solid">Register</a>
        </div>
    </header>

    <!-- hero section -->
    <section class="hero">
        <h1>Skip the Line, Not the Service</h1>
        <p>A digital queue system for Green University of Bangladesh — take a token online and track your position in real time, no more standing in long lines.</p>
        <a href="register.php" class="btn btn-accent">Get Started</a>
    </section>

    <!-- services preview -->
    <section class="services">
        <h2>Our Services</h2>
        <p class="section-sub">Pick a service below and get your digital token in seconds</p>
        <div class="service-grid">

            <?php foreach ($services as $service) { ?>
                <div class="service-card">
                    <h3><?php echo htmlspecialchars($service['name']); ?></h3>
                    <p><?php echo htmlspecialchars($service['description']); ?></p>
                    <span class="token-tag"><?php echo $service['prefix']; ?>001</span>
                </div>
            <?php } ?>

        </div>
    </section>

    <!-- how it works -->
    <section class="how-it-works">
        <h2>How It Works</h2>
        <div class="steps">

            <div class="step">
                <div class="step-number">1</div>
                <h3>Register</h3>
                <p>Create your student account in a few seconds.</p>
            </div>

            <div class="step">
                <div class="step-number">2</div>
                <h3>Select Service</h3>
                <p>Choose which office or service you need to visit.</p>
            </div>

            <div class="step">
                <div class="step-number">3</div>
                <h3>Get Token & Track</h3>
                <p>Receive your token number and watch the queue live.</p>
            </div>

        </div>
    </section>

    <!-- footer -->
    <footer class="footer">
        <p>Green University of Bangladesh &mdash; Department of CSE</p>
        <p>QueueLess Project | Web Programming Lab</p>
    </footer>

</body>
</html>
