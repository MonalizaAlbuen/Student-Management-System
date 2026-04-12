<?php

header("Content-Type: application/json");

$conn = new mysqli("localhost", "root", "", "student-management-system");

/* CHECK DATABASE CONNECTION */
if ($conn->connect_error) {
    echo json_encode([
        "status" => "error",
        "message" => "DB connection failed: " . $conn->connect_error
    ], JSON_PRETTY_PRINT);
    exit;
}

/* QUERY STUDENTS */
$sql = "SELECT sid, fname, lname FROM student";
$result = $conn->query($sql);

/* CHECK QUERY ERROR */
if (!$result) {
    echo json_encode([
        "status" => "error",
        "message" => "Query failed: " . $conn->error
    ], JSON_PRETTY_PRINT);
    exit;
}

/* BUILD ARRAY */
$students = [];

while ($row = $result->fetch_assoc()) {
    $students[] = [
        "sid" => $row["sid"],
        "name" => $row["fname"] . " " . $row["lname"]
    ];
}

/* OUTPUT JSON */
echo json_encode([
    "status" => "success",
    "data" => $students
], JSON_PRETTY_PRINT);

exit;