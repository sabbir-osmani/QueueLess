<?php
// PHP 8.1+ makes mysqli throw exceptions by default - we want the old
// behavior (functions return false on error) so our own error checks work
mysqli_report(MYSQLI_REPORT_OFF);

// connecting to our database
$conn = mysqli_connect("localhost", "root", "", "queueless_db");

if (!$conn) {
    die("connection failed: " . mysqli_connect_error());
}
?>
