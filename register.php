<?php
    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        $name = $_POST["name"];
        $roll_number = $_POST["roll_number"];
        
        // Image upload handling
        $target_dir = "uploads/";
        $image_file = $target_dir . basename($_FILES["image"]["name"]);
        $uploadOk = 1;
        $imageFileType = strtolower(pathinfo($image_file, PATHINFO_EXTENSION));

        // Check if the file is an actual image
        $check = getimagesize($_FILES["image"]["tmp_name"]);
        if ($check === false) {
            echo "File is not an image.";
            $uploadOk = 0;
        }

        // Check file size (optional)
        if ($_FILES["image"]["size"] > 500000) { // Limit to 500KB
            echo "Sorry, your file is too large.";
            $uploadOk = 0;
        }

        // Only allow certain file formats
        if ($imageFileType != "jpg" && $imageFileType != "png" && $imageFileType != "jpeg") {
            echo "Sorry, only JPG, JPEG, & PNG files are allowed.";
           $uploadOk = 0;
       }

        //Attempt to upload file if there are no errors
       if ($uploadOk == 1 && move_uploaded_file($_FILES["image"]["tmp_name"], $image_file)) {
           // Save data in the database
           $conn = new mysqli("localhost", "root", "", "aas");
           
           if ($conn->connect_error) {
               die("Connection failed: " . $conn->connect_error);
           }

           $stmt = $conn->prepare("INSERT INTO students (name, roll_number, image_path) VALUES (?, ?, ?)");
           $stmt->bind_param("sss", $name, $roll_number, $image_file);

           if ($stmt->execute()) {
               echo "Registration successful!";} 
               else {
               echo "Error: " . $stmt->error;
           }

           $stmt->close();
           $conn->close();
        } 
      else {
           echo "Sorry, there was an error uploading your file.";
      }
    }
?>
