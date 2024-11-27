<?php
// Database connection (replace with your actual credentials)
$conn = new mysqli("localhost", "root", "", "aas"); // Update 'your_database_name'

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Query to get student details
$sql = "SELECT name, roll_number, image_path FROM students";
$result = $conn->query($sql);

echo '<div class="container mt-4">';
echo '<h2 class="mb-3 text-center">Student Details</h2>';

if ($result->num_rows > 0) {
    echo '<table class="table table-bordered">';
    echo '<thead>';
    echo '<tr>';
    echo '<th>Name</th>';
    echo '<th>Roll Number</th>';
    echo '<th>Image</th>';
    echo '</tr>';
    echo '</thead>';
    echo '<tbody>';

    while($row = $result->fetch_assoc()) {
        echo '<tr>';
        echo '<td>' . htmlspecialchars($row["name"]) . '</td>';
        echo '<td>' . htmlspecialchars($row["roll_number"]) . '</td>';
        
        // Display the image if the path is available
        if (!empty($row["image_path"])) {
            echo '<td><img src="' . htmlspecialchars($row["image_path"]) . '" alt="Student Image" style="width: 100px; height: auto;"></td>';
        } else {
            echo '<td>No image available</td>';
        }

        echo '</tr>';
    }

    echo '</tbody>';
    echo '</table>';
} else {
    echo "<p class='text-center'>No students registered yet.</p>";
}

echo '</div>';

$conn->close();
?>
