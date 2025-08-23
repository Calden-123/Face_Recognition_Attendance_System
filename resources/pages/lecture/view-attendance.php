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
                    <option value="" selected disabled>Select Unit</option>
                    <!-- Options will be populated dynamically by JavaScript -->
                </select>
            </form>

            <button class="add" onclick="exportTableToExcel('attendaceTable', '<?php echo $unitCode ?>_on_<?php echo date('Y-m-d'); ?>','<?php echo $coursename ?>', '<?php echo $unitname ?>')">Export Attendance As Excel</button>

            <div class="table-container">
                <div class="title">
                    <h2 class="section--title">Attendance Preview</h2>
                </div>
                <div class="table attendance-table" id="attendaceTable">
                    <table>
                        <thead>
                            <tr>
                                <th>Registration No</th>
                                <?php
                                // Fetch distinct dates for the selected course and unit
                                if (isset($courseCode) && isset($unitCode)) {
                                    $distinctDatesQuery = "SELECT DISTINCT dateMarked FROM tblattendance WHERE course = :courseCode AND unit = :unitCode";
                                    $stmtDates = $pdo->prepare($distinctDatesQuery);
                                    $stmtDates->execute([
                                        ':courseCode' => $courseCode,
                                        ':unitCode' => $unitCode,
                                    ]);
                                    $distinctDatesResult = $stmtDates->fetchAll(PDO::FETCH_ASSOC);

                                    // Display each distinct date as a column header
                                    if ($distinctDatesResult) {
                                        foreach ($distinctDatesResult as $dateRow) {
                                            echo "<th>" . $dateRow['dateMarked'] . "</th>";
                                        }
                                    }
                                }
                                ?>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            // Only display data if course and unit are selected
                            if (isset($courseCode) && isset($unitCode)) {
                                // Fetch all unique students for the given course and unit
                                $studentsQuery = "SELECT DISTINCT studentRegistrationNumber FROM tblattendance WHERE course = :courseCode AND unit = :unitCode";
                                $stmtStudents = $pdo->prepare($studentsQuery);
                                $stmtStudents->execute([
                                    ':courseCode' => $courseCode,
                                    ':unitCode' => $unitCode,
                                ]);
                                $studentRows = $stmtStudents->fetchAll(PDO::FETCH_ASSOC);

                                // Display each student's attendance row
                                foreach ($studentRows as $row) {
                                    echo "<tr>";
                                    echo "<td>" . $row['studentRegistrationNumber'] . "</td>";

                                    // Loop through each date and fetch the attendance status for the student
                                    foreach ($distinctDatesResult as $dateRow) {
                                        $date = $dateRow['dateMarked'];

                                        // Fetch attendance for the current student and date
                                        $attendanceQuery = "SELECT attendanceStatus FROM tblattendance 
                                        WHERE studentRegistrationNumber = :studentRegistrationNumber 
                                        AND dateMarked = :date 
                                        AND course = :courseCode 
                                        AND unit = :unitCode";
                                        $stmtAttendance = $pdo->prepare($attendanceQuery);
                                        $stmtAttendance->execute([
                                            ':studentRegistrationNumber' => $row['studentRegistrationNumber'],
                                            ':date' => $date,
                                            ':courseCode' => $courseCode,
                                            ':unitCode' => $unitCode,
                                        ]);
                                        $attendanceResult = $stmtAttendance->fetch(PDO::FETCH_ASSOC);

                                        // Display attendance status or default to "Absent"
                                        if ($attendanceResult) {
                                            echo "<td>" . $attendanceResult['attendanceStatus'] . "</td>";
                                        } else {
                                            echo "<td>Absent</td>";
                                        }
                                    }

                                    echo "</tr>";
                                }
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
            unitSelect.innerHTML = '<option value="" selected disabled>Select Unit</option>';

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
            var courseSelect = document.getElementById("courseSelect");
            var unitSelect = document.getElementById("unitSelect");

            var selectedCourse = courseSelect.value;
            var selectedUnit = unitSelect.value;

            if (selectedCourse && selectedUnit) {
                var url = "download-record";
                url += "?course=" + encodeURIComponent(selectedCourse) + "&unit=" + encodeURIComponent(selectedUnit);
                window.location.href = url;
            }
        }
    </script>

    <?php js_asset(['min/js/filesaver', 'min/js/xlsx', 'active_link']) ?>
</body>
</html>

    function exportTableToExcel(tableId, filename = '', courseCode = '', unitCode = '') {
        var table = document.getElementById(tableId);
        var currentDate = new Date();
        var formattedDate = currentDate.toLocaleDateString(); // Format the date as needed

        var headerContent = '<p style="font-weight:700;"> Attendance for : ' + courseCode + ' Unit name : ' + unitCode + ' On: ' + formattedDate + '</p>';
        var tbody = document.createElement('tbody');
        var additionalRow = tbody.insertRow(0);
        var additionalCell = additionalRow.insertCell(0);
        additionalCell.innerHTML = headerContent;
        table.insertBefore(tbody, table.firstChild);
        var wb = XLSX.utils.table_to_book(table, {
            sheet: "Attendance"
        });
        var wbout = XLSX.write(wb, {
            bookType: 'xlsx',
            bookSST: true,
            type: 'binary'
        });
        var blob = new Blob([s2ab(wbout)], {
            type: 'application/octet-stream'
        });
        if (!filename.toLowerCase().endsWith('.xlsx')) {
            filename += '.xlsx';
        }

        saveAs(blob, filename);
    }

    function s2ab(s) {
        var buf = new ArrayBuffer(s.length);
        var view = new Uint8Array(buf);
        for (var i = 0; i < s.length; i++) view[i] = s.charCodeAt(i) & 0xFF;
        return buf;
    }
</script>

</html>