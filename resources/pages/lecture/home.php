<?php


if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $attendanceData = json_decode(file_get_contents("php://input"), true);
    if ($attendanceData) {
        try {
            $sql = "INSERT INTO tblattendance (studentRegistrationNumber, course, unit, attendanceStatus, dateMarked)  
                VALUES (:studentID, :course, :unit, :attendanceStatus, :date)";

            $stmt = $pdo->prepare($sql);

            foreach ($attendanceData as $data) {
                $studentID = $data['studentID'];
                $attendanceStatus = $data['attendanceStatus'];
                $course = $data['course'];
                $unit = $data['unit'];
                $date = date("Y-m-d");

                // Bind parameters and execute for each attendance record
                $stmt->execute([
                    ':studentID' => $studentID,
                    ':course' => $course,
                    ':unit' => $unit,
                    ':attendanceStatus' => $attendanceStatus,
                    ':date' => $date
                ]);
            }

            $_SESSION['message'] = "Attendance recorded successfully for all entries.";
        } catch (PDOException $e) {
            $_SESSION['message'] = "Error inserting attendance data: " . $e->getMessage();
        }
    } else {
        $_SESSION['message'] = "No attendance data received.";
    }
}

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <link href="resources/images/logo/attnlg.png" rel="icon">
    <title>lecture Dashboard</title>
    <link rel="stylesheet" href="resources/assets/css/styles.css">
    <script defer src="resources/assets/javascript/face_logics/face-api.min.js"></script>

    <link href="https://cdnjs.cloudflare.com/ajax/libs/remixicon/4.2.0/remixicon.css" rel="stylesheet">
</head>


<body>

    <?php include 'includes/topbar.php'; ?>
    <section class="main">
        <div class="main--content">
            <div id="messageDiv" class="messageDiv" style="display:none;"> </div>
            <p style="font:80px; font-weight:400; color:blue; text-align:center; padding-top:2px;">Please select course,
                unit, and venue first. Before Launching Facial Recognition</p>
            <form class="lecture-options" id="selectForm">
                <!-- Changed the course dropdown onChange to updateUnits() -->
                <select required name="course" id="courseSelect" onChange="updateUnits()">
                    <option value="" selected>Select Course</option>
                    <option value="BINCT">BACHELOR OF INFORMATION AND
                        COMMUNICATIONS TECHNOLOGY</option>
                    <option value="DIIAD">DIPLOMA IN
                        INFORMATION AND COMMUNICATION
                        TECHNOLOGY IN APPLICATIONS
                        DEVELOPMENTe</option>
                    <option value="DIIBA">Diploma in Information and Communication Technology in Business Analysis
                    </option>
                </select>

                <!-- Changed the unit dropdown to have no onChange. updateTable() will be called after it's populated. -->
                <select required name="unit" id="unitSelect">
                    <option value="" selected disabled>Select Unit</option>
                    <!-- Options will be populated dynamically by JavaScript -->
                </select>


                <select required name="venue" id="venueSelect" onChange="updateTable()">
                    <option value="" selected disabled>Select Venue</option>
                    <option value="Auditorium">Auditorium</option>
                    <option value="Lab1">Computer Lab 1</option>
                    <option value="Lab2">Computer Lab 2</option>
                    <option value="Room101">Room 101</option>
                    <option value="Room102">Room 102</option>
                </select>


            </form>
            <div class="attendance-button">
                <button id="startButton" class="add">Launch Facial Recognition</button>
                <button id="endButton" class="add" style="display:none">End Attendance Process</button>
                <button id="endAttendance" class="add">END Attendance Taking</button>
            </div>

            <div class="video-container" style="display:none;">
                <video id="video" width="600" height="450" autoplay></video>
                <canvas id="overlay"></canvas>
            </div>

            <div class="table-container">

                <div id="studentTableContainer">

                </div>

            </div>

        </div>
    </section>
    <script>
        // Add the JavaScript for the dynamic unit dropdown here
        const courseToUnits = {
            "BINCT": ["CS101", "CS102", "CS103", "CS104", "CS105"],
            "DIIAD": ["CS103", "CS104"],
            "DIIBA": ["CS101", "CS103"]
        };

        const unitNames = {
            "CS101": "Introduction to Computer Science",
            "CS102": "Data Structures",
            "CS103": "Database Systems",
            "CS104": "Web Development",
            "CS105": "Artificial Intelligence"
        };

        function updateUnits() {
            const courseSelect = document.getElementById('courseSelect');
            const selectedCourse = courseSelect.value;
            const unitSelect = document.getElementById('unitSelect');

            // Reset unit dropdown
            unitSelect.innerHTML = '<option value="" selected disabled>Select Unit</option>';

            if (!selectedCourse) return;

            const unitsForCourse = courseToUnits[selectedCourse];

            unitsForCourse.forEach(unitCode => {
                const option = document.createElement('option');
                option.value = unitCode;
                option.textContent = `${unitCode} - ${unitNames[unitCode]}`;
                unitSelect.appendChild(option);
            });
            
            // Call updateTable after populating the units to refresh the table if needed
            updateTable();
        }
    </script>

    <?php js_asset(["active_link", 'face_logics/script']) ?>

</body>

</html>