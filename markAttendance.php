<?php
// Database configuration
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "aas"; // Database name

// Create a database connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die(json_encode(["status" => "error", "message" => "Database connection failed: " . $conn->connect_error]));
}

// Check if the request method is POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get the JSON data from the request body
    $input = json_decode(file_get_contents("php://input"), true);

    if (!isset($input['name'], $input['rollNumber'], $input['photo'])) {
        echo json_encode(["status" => "error", "message" => "Missing required fields"]);
        exit();
    }

    $name = $input['name'];
    $rollNumber = $input['rollNumber'];
    $photoData = $input['photo']; // Base64 encoded image

    // Decode the base64 image and save it as a file
    $photoDecoded = base64_decode(preg_replace('#^data:image/\w+;base64,#i', '', $photoData));
    $imageFileName = "uploads/" . uniqid() . ".png"; // Save in 'uploads' directory

    if (!file_put_contents($imageFileName, $photoDecoded)) {
        echo json_encode(["status" => "error", "message" => "Failed to save the photo"]);
        exit();
    }

    // Check if the student exists in the database
    $stmt = $conn->prepare("SELECT id FROM students WHERE name = ? AND roll_number = ?");
    $stmt->bind_param("ss", $name, $rollNumber);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 0) {
        echo json_encode(["status" => "error", "message" => "Student not found"]);
        $stmt->close();
        exit();
    }

    // Mark attendance (e.g., store attendance record in the database)
    $studentId = $result->fetch_assoc()['id'];
    $stmt->close();

    $stmt = $conn->prepare("INSERT INTO attendance (student_id, status, timestamp) VALUES (?, 'Present', NOW())");
    $stmt->bind_param("i", $studentId);

    if ($stmt->execute()) {
        echo json_encode(["status" => "success", "message" => "Attendance marked successfully"]);
    } else {
        echo json_encode(["status" => "error", "message" => "Failed to mark attendance"]);
    }

    $stmt->close();
} else {
    echo json_encode(["status" => "error", "message" => "Invalid request method"]);
}

$conn->close();
?>
