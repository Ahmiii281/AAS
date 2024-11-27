from flask import Flask, request, jsonify, render_template
import cv2
import numpy as np
import logging

app = Flask(__name__)

# Simulated database
students = {}
attendance_log = []

# Configure logging
logging.basicConfig(level=logging.INFO, format='%(asctime)s - %(levelname)s - %(message)s')

@app.route('/')
def index():
    """Render the home page."""
    return render_template('index.html')

@app.route('/register', methods=['POST'])
def register():
    """Register a new student with facial data."""
    try:
        name = request.form.get('name')
        roll_number = request.form.get('rollNumber')
        image_file = request.files.get('image')

        if not name or not roll_number or not image_file:
            return jsonify({"status": "error", "message": "Missing required fields"}), 400

        # Decode the uploaded image
        image = cv2.imdecode(np.frombuffer(image_file.read(), np.uint8), cv2.IMREAD_COLOR)
        if image is None:
            return jsonify({"status": "error", "message": "Invalid image format"}), 400

        # Store facial data (simulated)
        students[roll_number] = {'name': name, 'face_data': image}
        logging.info(f"Student registered: {name} (Roll No: {roll_number})")
        return jsonify({"status": "success", "message": "Student registered successfully"})
    except Exception as e:
        logging.error(f"Error in registration: {e}")
        return jsonify({"status": "error", "message": "An error occurred during registration"}), 500

@app.route('/markAttendance', methods=['POST'])
def mark_attendance():
    """Mark attendance for a student."""
    try:
        data = request.json
        roll_number = data.get('roll_number')

        if not roll_number:
            return jsonify({"status": "error", "message": "Roll number is required"}), 400

        if roll_number in students:
            attendance_log.append({'name': students[roll_number]['name'], 'status': 'Present'})
            logging.info(f"Attendance marked for Roll No: {roll_number}")
            return jsonify({"status": "success", "message": "Attendance marked"})
        else:
            return jsonify({"status": "error", "message": "Student not found"}), 404
    except Exception as e:
        logging.error(f"Error in marking attendance: {e}")
        return jsonify({"status": "error", "message": "An error occurred while marking attendance"}), 500

@app.route('/getAttendance', methods=['GET'])
def get_attendance():
    """Retrieve the attendance log."""
    try:
        return jsonify({"status": "success", "attendance_log": attendance_log})
    except Exception as e:
        logging.error(f"Error in fetching attendance: {e}")
        return jsonify({"status": "error", "message": "An error occurred while fetching attendance"}), 500

if __name__ == '__main__':
    app.run(debug=True)
