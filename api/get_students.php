<?php

header("Content-Type: application/json");

$conn = new mysqli("localhost", "root", "", "student-management-system");

if ($conn->connect_error) {
    echo json_encode([
        "status" => "error",
        "message" => "DB connection failed"
    ]);
    exit;
}

$result = $conn->query("SELECT sid, fname, lname FROM student");

$students = [];

while ($row = $result->fetch_assoc()) {
    $students[] = [
        "sid" => $row["sid"],
        "name" => $row["fname"] . " " . $row["lname"]
    ];
}

echo json_encode([
    "status" => "success",
    "data" => $students
]);

exit; // IMPORTANT