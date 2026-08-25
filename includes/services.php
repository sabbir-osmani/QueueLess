<?php
// This file just holds the list of the 4 services in one place,
// so every page uses the exact same names and token prefixes.

// The short code used in the database and in URLs (?service=accounts)
// The full name shown to the user
// The letter used at the start of the token number (A001, L001, etc.)
$services = array(
    array("code" => "accounts", "name" => "Accounts Office",       "prefix" => "A", "description" => "Tuition fee, payments and account related queries."),
    array("code" => "library",  "name" => "Library",                "prefix" => "L", "description" => "Book issue, return and library membership services."),
    array("code" => "cse",      "name" => "CSE Department Office",  "prefix" => "C", "description" => "Department related forms, signatures and notices."),
    array("code" => "lab",      "name" => "Computer Lab",           "prefix" => "B", "description" => "Lab access, equipment and technical support."),
);

// Same information, but as simple lookup lists ($serviceNames['library'] etc.)
// This is just for convenience when we already know the service code.
$serviceNames = array(
    "accounts" => "Accounts Office",
    "library"  => "Library",
    "cse"      => "CSE Department Office",
    "lab"      => "Computer Lab",
);

$servicePrefixes = array(
    "accounts" => "A",
    "library"  => "L",
    "cse"      => "C",
    "lab"      => "B",
);
?>
