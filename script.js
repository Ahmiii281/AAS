let videoStream;

// Start the camera and display video
function startAttendance() {
    const video = document.getElementById('video');
    const overlay = document.getElementById('overlay');
    const status = document.getElementById('status');
    const context = overlay.getContext('2d');

    // Access the camera
    navigator.mediaDevices.getUserMedia({ video: true })
        .then(stream => {
            videoStream = stream;
            video.srcObject = stream;
            status.textContent = "Align your face within the oval area";

            video.onloadedmetadata = () => {
                drawOvalOverlay();
            };
        })
        .catch(error => {
            console.error("Error accessing camera: ", error);
            status.textContent = "Error accessing camera";
        });

    // Draw an oval overlay on the canvas
    function drawOvalOverlay() {
        const centerX = overlay.width / 2;
        const centerY = overlay.height / 2;
        const radiusX = 150;
        const radiusY = 200;

        context.clearRect(0, 0, overlay.width, overlay.height);
        context.fillStyle = "rgba(0, 0, 0, 0.5)";
        context.fillRect(0, 0, overlay.width, overlay.height);

        // Draw oval
        context.globalCompositeOperation = 'destination-out';
        context.beginPath();
        context.ellipse(centerX, centerY, radiusX, radiusY, 0, 0, 2 * Math.PI);
        context.fill();
        context.globalCompositeOperation = 'source-over';
    }
}

// Capture photo and send for attendance
function capturePhoto() {
    const video = document.getElementById('video');
    const photoCanvas = document.getElementById('photoCanvas');
    const photoContext = photoCanvas.getContext('2d');
    const name = document.getElementById('studentName').value.trim();
    const rollNumber = document.getElementById('rollNumber').value.trim();

    if (!name || !rollNumber) {
        alert('Please enter your name and roll number before capturing photo!');
        return;
    }

    // Draw the current video frame onto the canvas
    photoContext.drawImage(video, 0, 0, photoCanvas.width, photoCanvas.height);

    // Convert canvas to data URL (base64)
    const photoData = photoCanvas.toDataURL('image/png');

    // Stop video stream (optional)
    if (videoStream) {
        videoStream.getTracks().forEach(track => track.stop());
    }

    // Send photo and student details to server
    fetch('/markAttendance', {
        method: 'POST',
        body: JSON.stringify({ name, rollNumber, photo: photoData }),
        headers: {
            'Content-Type': 'application/json'
        }
    })
        .then(response => response.json())
        .then(data => {
            alert(data.message);
        })
        .catch(err => {
            console.error('Error marking attendance:', err);
            alert('Error marking attendance. Please try again.');
        });
}

fetch('/markAttendance.php', {
    method: 'POST',
    body: JSON.stringify({ name, rollNumber, photo: photoData }),
    headers: {
        'Content-Type': 'application/json'
    }
})
    .then(response => response.json())
    .then(data => {
        alert(data.message);
    })
    .catch(err => {
        console.error('Error marking attendance:', err);
        alert('Error marking attendance. Please try again.');
    });


// Theme Toggle Functionality
function initializeThemeToggle() {
    const themeToggleButton = document.getElementById("themeToggle");
    const themeIcon = document.getElementById("themeIcon");

    // Load saved theme preference
    let isDarkMode = localStorage.getItem("theme") === "dark";

    // Apply saved theme on page load
    document.body.classList.toggle("dark-mode", isDarkMode);
    themeIcon.classList.toggle("bi-moon-fill", isDarkMode);
    themeIcon.classList.toggle("bi-sun-fill", !isDarkMode);

    themeToggleButton.addEventListener("click", () => {
        isDarkMode = !isDarkMode;

        // Toggle the dark mode class
        document.body.classList.toggle("dark-mode", isDarkMode);

        // Update icon
        themeIcon.classList.toggle("bi-sun-fill", !isDarkMode);
        themeIcon.classList.toggle("bi-moon-fill", isDarkMode);

        // Save preference to local storage
        localStorage.setItem("theme", isDarkMode ? "dark" : "light");
    });
}

// DOMContentLoaded event listener to initialize scripts
document.addEventListener("DOMContentLoaded", () => {
    initializeThemeToggle(); // Initialize theme toggle functionality
});