<?php
// Database configuration
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "aas"; // Corrected database name

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Determine the action based on the query parameter
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_GET['action'])) {
    $action = $_GET['action'];
    if ($action === 'register') {
        registerStudent($conn);
    } else {
        echo json_encode(["status" => "error", "message" => "Invalid endpoint"]);
    }
} elseif ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['action'])) {
    $action = $_GET['action'];
    if ($action === 'getAttendance') {
        fetchAttendanceRecords($conn);
    } else {
        echo json_encode(["status" => "error", "message" => "Invalid endpoint"]);
    }
} else {
    echo json_encode(["status" => "error", "message" => "Invalid endpoint"]);
}


function registerStudent($conn) {
    // Ensure required fields are present
    if (!isset($_POST['name'], $_POST['rollNumber'], $_FILES['image'])) {
        echo json_encode(["status" => "error", "message" => "Missing required fields"]);
        return;
    }

    $name = $_POST['name'];
    $rollNumber = $_POST['rollNumber'];

    // Directory to save uploaded images
    $target_dir = "uploads/";
    $image_name = basename($_FILES['image']['name']);
    $target_file = $target_dir . $image_name;

    // Ensure the uploads directory exists
    if (!file_exists($target_dir)) {
        mkdir($target_dir, 0755, true); // Create the directory if it doesn't exist
    }

    // Move the uploaded file to the target directory
    if (move_uploaded_file($_FILES['image']['tmp_name'], $target_file)) {
        // Store the relative path of the uploaded image in the database
        $stmt = $conn->prepare("INSERT INTO students (name, roll_number, image_path) VALUES (?, ?, ?)");
        $stmt->bind_param("sss", $name, $rollNumber, $target_file);

        if ($stmt->execute()) {
            echo json_encode(["status" => "success", "message" => "Student registered successfully!"]);
        } else {
            echo json_encode(["status" => "error", "message" => "Error: " . $stmt->error]);
        }

        $stmt->close();
    } else {
        echo json_encode(["status" => "error", "message" => "Failed to upload image"]);
    }
}

// Fetch attendance records function
function fetchAttendanceRecords($conn) {
    $result = $conn->query("SELECT name, roll_number FROM students");
    $records = [];
    while ($row = $result->fetch_assoc()) {
        $records[] = $row;
    }
    echo json_encode($records);
}

$conn->close();
