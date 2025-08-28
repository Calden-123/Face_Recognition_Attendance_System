<?php

$courseCode = isset($_GET['course']) ? $_GET['course'] : '';
$unitCode = isset($_GET['unit']) ? $_GET['unit'] : '';

$studentRows = fetchStudentRecordsFromDatabase($courseCode, $unitCode);

$coursename = "";
if (!empty($courseCode)) {
    $coursename_query = "SELECT name FROM tblcourse WHERE courseCode = '$courseCode'";
    $result = fetch($coursename_query);
    foreach ($result as $row) {

        $coursename = $row['name'];
    }
}
$unitname = "";
if (!empty($unitCode)) {
    $unitname_query = "SELECT name FROM tblunit WHERE unitCode = '$unitCode'";
    $result = fetch($unitname_query);
    foreach ($result as $row) {

        $unitname = $row['name'];
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
    <link href="https://cdnjs.cloudflare.com/ajax/libs/remixicon/4.2.0/remixicon.css" rel="stylesheet">
</head>

<body>
    <?php include 'includes/topbar.php'; ?>
    <section class="main">
        <div class="main--content">
            <form class="lecture-options" id="selectForm">
                <!-- Changed the course dropdown onChange to updateUnits() -->
                <select required name="course" id="courseSelect" onChange="updateUnits()">
                    <option value="" selected>Select Course</option>
                    <option value="BINCT">BACHELOR OF INFORMATION AND
                        COMMUNICATIONS TECHNOLOGY</option>
                    <option value="DIIAD">DIPLOMA IN
                        INFORMATION AND COMMUNICATION
                        TECHNOLOGY IN APPLICATIONS
                        DEVELOPMENT</option>
                    <option value="DIIBA">Diploma in Information and Communication Technology in Business Analysis
                    </option>
                </select>

                <!-- Changed the unit dropdown to have no initial options. They will be populated dynamically -->
                <select required name="unit" id="unitSelect" onChange="updateTable()">
                    <option value="" selected disabled>Select Module</option>
                    <!-- Options will be populated dynamically by JavaScript -->
                </select>
            </form>

            <div class="table-container">
                <div class="title">
                    <h2 class="section--title">Students List</h2>
                </div>
                <div class="table attendance-table" id="attendaceTable">
                    <table>
                        <thead>
                            <tr>
                                <th>Registration No</th>
                                <th>First Name</th>
                                <th>Last Name</th>
                                <th>Email</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php 
                            // Only display data if course is selected
                            if (isset($courseCode)) {
                                $query = "SELECT * FROM tblstudents WHERE courseCode = '$courseCode'";
                                $result = fetch($query);
                                if ($result) {
                                    foreach ($result as $row) {
                                        echo "<tr>";
                                        echo "<td>" . $row['registrationNumber'] . "</td>";
                                        echo "<td>" . $row['firstName'] . "</td>";
                                        echo "<td>" . $row['lastName'] . "</td>";
                                        echo "<td>" . $row['email'] . "</td>";
                                        echo "</tr>";
                                    }
                                } else {
                                    echo "<tr><td colspan='4'>No students found for the selected course</td></tr>";
                                }
                            } else {
                                echo "<tr><td colspan='4'>Please select a course to view students</td></tr>";
                            }
                            ?>
                        </tbody>
                    </table>
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
            unitSelect.innerHTML = '<option value="" selected disabled>Select Module</option>';

            if (!selectedCourse) return;

            const unitsForCourse = courseToUnits[selectedCourse];

            unitsForCourse.forEach(unitCode => {
                const option = document.createElement('option');
                option.value = unitCode;
                option.textContent = `${unitCode} - ${unitNames[unitCode]}`;
                unitSelect.appendChild(option);
            });
        }

        function updateTable() {
            console.log("update noted");
            var courseSelect = document.getElementById("courseSelect");
            var unitSelect = document.getElementById("unitSelect");

            var selectedCourse = courseSelect.value;
            var selectedUnit = unitSelect.value;

            var url = "view-students";
            if (selectedCourse && selectedUnit) {
                url += "?course=" + encodeURIComponent(selectedCourse) + "&unit=" + encodeURIComponent(selectedUnit);
                window.location.href = url;
                console.log(url)
            }
        }
    </script>

    <?php js_asset(["active_link", "min/js/filesaver", "min/js/xlsx"]) ?>
</body>
</html>