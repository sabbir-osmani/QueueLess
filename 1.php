<?php
    // ONE-TIME USE ONLY - creates ALL admin accounts at once - delete this file after running
    // Just visit makeAllAdmins.php once in your browser, no URL parameters needed.
    // Edit the $admins list below first if you want different names/emails/passwords.

    require 'includes/db.php';

    $admins = array(
        array("name" => "Rafi",  "email" => "accounts1@queueless.com", "pass" => "pass123", "service" => "accounts"),
        array("name" => "Mim",   "email" => "library1@queueless.com",  "pass" => "pass123", "service" => "library"),
        array("name" => "Karim", "email" => "cse1@queueless.com",      "pass" => "pass123", "service" => "cse"),
        array("name" => "Nova",  "email" => "lab1@queueless.com",      "pass" => "pass123", "service" => "lab")
    );

    echo "<h2>Creating all admin accounts...</h2>";

    foreach ($admins as $a) {
        $name = $a['name'];
        $email = $a['email'];
        $pass = $a['pass'];
        $service = $a['service'];

        // skip if this email already exists, so re-running is safe
        $check = mysqli_query($conn, "SELECT id FROM admins WHERE email = '$email'");
        if (mysqli_num_rows($check) > 0) {
            echo "Already exists, skipped: $email ($service)<br>";
            continue;
        }

        $hashedPass = password_hash($pass, PASSWORD_DEFAULT);
        $query = "INSERT INTO admins (name, email, pass, service) VALUES ('$name', '$email', '$hashedPass', '$service')";

        if (mysqli_query($conn, $query)) {
            echo "Created: $name | $email | password: $pass | service: $service<br>";
        } else {
            echo "Error creating $email: " . mysqli_error($conn) . "<br>";
        }
    }

    echo "<br><strong>All done. Now delete this file (makeAllAdmins.php) immediately.</strong>";
?>