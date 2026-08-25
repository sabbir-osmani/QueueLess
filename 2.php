<?php
    // ONE-TIME USE ONLY - creates sample test data - delete after running
    require 'includes/db.php';

    echo "<h2>Seeding test data...</h2>";

    // ---------- sample students ----------
    $students = array(
        array("Rakib Hasan", "rakib@test.com"),
        array("Nusrat Jahan", "nusrat@test.com"),
        array("Tanvir Ahmed", "tanvir@test.com"),
        array("Sadia Islam", "sadia@test.com")
    );

    $studentIds = array();

    foreach ($students as $s) {
        $name = $s[0];
        $email = $s[1];
        $hashedPass = password_hash("test123", PASSWORD_DEFAULT);

        // skip if already exists
        $check = mysqli_query($conn, "SELECT id FROM students WHERE email = '$email'");
        if (mysqli_num_rows($check) > 0) {
            $row = mysqli_fetch_assoc($check);
            $studentIds[] = $row['id'];
            echo "Student already exists: $email<br>";
            continue;
        }

        mysqli_query($conn, "INSERT INTO students (name, email, pass, created_at) VALUES ('$name', '$email', '$hashedPass', NOW())");
        $studentIds[] = mysqli_insert_id($conn);
        echo "Created student: $email (password: test123)<br>";
    }

    // ---------- sample tokens (mix of waiting/completed/skipped, across services) ----------
    $sampleTokens = array(
        array($studentIds[0], "accounts", "A001", "completed"),
        array($studentIds[1], "accounts", "A002", "completed"),
        array($studentIds[2], "accounts", "A003", "serving"),
        array($studentIds[3], "accounts", "A004", "waiting"),
        array($studentIds[0], "library", "L001", "completed"),
        array($studentIds[1], "library", "L002", "skipped"),
        array($studentIds[2], "cse", "C001", "waiting")
    );

    foreach ($sampleTokens as $t) {
        $sid = $t[0];
        $service = $t[1];
        $tokenNo = $t[2];
        $status = $t[3];

        mysqli_query($conn, "INSERT INTO tokens (student_id, service, token_no, status, created_at) VALUES ($sid, '$service', '$tokenNo', '$status', NOW())");
    }

    echo "<br>Created " . count($sampleTokens) . " sample tokens across accounts/library/cse.<br>";
    echo "<br><strong>Now delete this file (seedData.php) immediately.</strong>";
?>